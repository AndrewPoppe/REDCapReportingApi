<?php

namespace YaleREDCap\REDCapReportingAPI;

/**
 * @property \ExternalModules\Framework $framework
 * @see Framework
 */

 class REDCapReportingAPI extends \ExternalModules\AbstractExternalModule
 {

    public function redcap_module_configuration_settings($project_id, $settings) {
        $users = $this->getAllUsers();

        $settings = array_map(function($setting) use ($users) {
            if (isset($setting['key']) && $setting['key'] === 'user') {
                $setting['choices'] = $users;
            }
            return $setting;
        }, $settings);

        return $settings;
    }

    public function redcap_module_link_check_display($project_id, $link) {
        $username = $this->framework->getUser()->getUserName();
        $userAllowed = $this->isUserAllowed($username);
        if (!$userAllowed) {
            return null;
        }
        return $link;
    }

    public function redcap_module_ajax($action, $payload, $project_id, $record, $instrument, $event_id, $repeat_instance, $survey_hash, $response_id, $survey_queue_hash, $page, $page_full, $user_id, $group_id) {
        try {
            if (!$this->isUserAllowed($user_id)) {
                return null;
            }

            if ($action === 'generate-token') {
                $token = $this->generateAPIToken();
                $truncatedToken = $this->setToken($user_id, $token);
                $this->log("set token", ['token' => $truncatedToken, 'username' => $user_id]);
                return [
                    'token' => $token,
                    'truncated_token' => $truncatedToken
                ];
            } elseif ($action === 'delete-token') {
                $this->setToken($user_id, '');
                $this->log("delete token", ['username' => $user_id]);
                return;
            } else {
                return null;
            }
        } catch (\Throwable $e) {
            $this->log("ajax error", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getAllUsers() {
        $users = [];
        $sql = "SELECT username, CONCAT(user_firstname, ' ', user_lastname, ' (', username, ')') name FROM redcap_user_information ORDER BY user_lastname, user_firstname, username";
        $result = $this->framework->query($sql, []);
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'value' => $row['username'],
                'name' => $row['name'],
            ];
        }
        return $users;
    }

    
    public function getAllowedUser(string $username) {
        if (empty($username) || !$this->isUserAllowed($username)) {
            return null;
        }

        $tokens = $this->framework->getSystemSetting('token') ?? [];
        $truncatedTokens = $this->framework->getSystemSetting('truncated_token') ?? [];

        return [
            'user' => $username,
            'token' => $tokens[$username],
            'truncated_token' => $truncatedTokens[$username]
        ];
    }

    public function isUserAllowed(string $username) : bool {
        $users = $this->framework->getSystemSetting('user') ?? [];
        return in_array($username, $users, true);
    }

    public function hasValidToken(string $username) : bool {
        $user = $this->getAllowedUser($username);
        if (empty($user)) {
            return false;
        }
        return !empty($user['token']);
    }

    public function isApiEnabled() : bool {
        return $this->framework->getSystemSetting('api_enabled') ?? false;
    }

    public function getTruncatedToken(string $username) : string {
        $user = $this->getAllowedUser($username);
        if (empty($user)) {
            return '...';
        }
        return $user['truncated_token'] ?? '...';
    }

    private function generateAPIToken() : string {
        return strtoupper(bin2hex(random_bytes(16)));
    }
    public function setToken(string $username, string $token) : string {

        if (empty($username) || !$this->isUserAllowed($username)) {
            return '';
        }

        if (empty($token)) {
            $truncatedToken = $hashedToken = '';
        } else {
            $truncatedToken = substr($token, 0, 4) . '...';
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
        }

        $tokens = $this->framework->getSystemSetting('token') ?? [];
        $truncatedTokens = $this->framework->getSystemSetting('truncated_token') ?? [];
        $tokens[$username] = $hashedToken;
        $truncatedTokens[$username] = $truncatedToken;
        $this->framework->setSystemSetting('token', $tokens);
        $this->framework->setSystemSetting('truncated_token', $truncatedTokens);
        $this->log("set token", ['token' => $truncatedToken, 'username' => $username]);
        
        return $truncatedToken;
    }

    public function getApiUrl() : string {
        $url = $this->framework->getUrl('api.php', true, true);
        return $url;
    }

    public function handleApi($token) {
        if (!$this->isApiEnabled()) {
            return ['error' => 'API is disabled', 'errorCode' => 503];
        }
        if (!$this->isValidToken($token)) {
            return ['error' => 'Invalid token', 'errorCode' => 403];
        }
        return ['result'=> 'success'];
    }

    public function isValidToken($token) : bool {
        $tokens = $this->framework->getSystemSetting('token') ?? [];

        foreach ($tokens as $user => $candidateToken) {
            if (password_verify($token, $candidateToken) && $this->isUserAllowed($user)) {
                return true;
            }
        }
        return false;
    }
 }