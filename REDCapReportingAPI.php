<?php

namespace YaleREDCap\REDCapReportingAPI;

/**
 * @property \ExternalModules\Framework $framework
 * @see Framework
 */

 class REDCapReportingAPI extends \ExternalModules\AbstractExternalModule
 {

    public function redcap_module_configuration_settings($project_id, $settings) {
        try {
            $users = $this->getAllUsers();
            $customQueries = $this->getAllCustomQueries();

            $settings = array_map(function($setting) use ($users, $customQueries) {
                if (isset($setting['key']) && $setting['key'] === 'user') {
                    $setting['choices'] = $users;
                }
                if (isset($setting['key']) && $setting['key'] === 'database_query_tool_queries') {
                    $setting['choices'] = $customQueries;
                }
                return $setting;
            }, $settings);
        } catch (\Throwable $e) {
            $this->log("configuration settings error", ['error' => $e->getMessage()]);
        } finally {
            return $settings;
        }
    }

    public function redcap_module_link_check_display($project_id, $link) {
        $username = $this->framework->getUser()->getUserName();
        $userAllowed = $this->isUserAllowed($username);
        $apiEnabled = $this->isApiEnabled();
        if (!$userAllowed || !$apiEnabled) {
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
                $this->log("set token", ['token' => $truncatedToken, 'token_holder' => $user_id]);
                return [
                    'token' => $token,
                    'truncated_token' => $truncatedToken
                ];
            } elseif ($action === 'delete-token') {
                $this->setToken($user_id, '');
                $this->log("delete token", ['token_holder' => $user_id]);
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
        $sql = "SELECT username, CONCAT(user_firstname, ' ', user_lastname, ' (', username, ')') name FROM redcap_user_information WHERE super_user = 1 ORDER BY user_lastname, user_firstname, username";
        $result = $this->framework->query($sql, []);
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'value' => $row['username'],
                'name' => $row['name'],
            ];
        }
        return $this->framework->escape($users);
    }

    public function getAllCustomQueries() {
        try {
            // $sql = "SELECT qid, title FROM redcap_custom_queries";
            $sql = "SELECT q.qid, q.title, q.query, f.name folder FROM redcap_custom_queries q
                    LEFT JOIN redcap_custom_queries_folders_items i 
                    ON i.qid = q.qid
                    LEFT JOIN redcap_custom_queries_folders f
                    ON f.folder_id = i.folder_id
                    ORDER BY position,q.qid";
            $result = $this->framework->query($sql, []);
            $queries = [];
            $index = 1;
            while ($row = $result->fetch_assoc()) {
                $queries[] = [
                    'value' => $row['qid'],
                    'name' => $row['title'] . ' (qid: ' . $row['qid'] . ')',
                    'title' => $row['title'],
                    'folder' => $row['folder'] ?? '',
                ];
            }
            return $this->framework->escape($queries);
        } catch (\Throwable $e) {
            $this->log("getCustomQueries error", ['error' => $e->getMessage()]);
            return [];
        }
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

    public function isQueryAllowed($qid) : bool {
        $queries = $this->framework->getSystemSetting('database_query_tool_queries') ?? [];
        return in_array($qid, $queries, true);
    }

    public function getAllowedQueries() : array {
        $queries = $this->getAllCustomQueries();
        if (empty($queries)) {
            return [];
        }
        return array_filter($queries, function($query) {
            return $this->isQueryAllowed($query['value']);
        });
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

    public function areDatabaseQueryToolQueriesAllowed() : bool {
        return $this->framework->getSystemSetting('database_query_tool_queries_enabled') ?? false;
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
        
        return $truncatedToken;
    }

    public function getApiUrl() : string {
        $url = $this->framework->getUrl('api.php', true, true);
        return $url;
    }

    public function handleApi($token, $request) {
        $error = null;
        $user = $this->validateUser($token);
        $requestedQuery = trim($request['query'] ?? '');
        $requestedReport = trim($request['report'] ?? '');

        if (!$this->isApiEnabled()) {
            $error = ['error' => 'API is disabled', 'errorCode' => 503];
        } else if (!$user) {
            $error = ['error' => 'Invalid token', 'errorCode' => 403];
        } else if (empty($request)) {
            $error = ['error' => 'No report or query specified', 'errorCode' => 400];
        } else if ($requestedReport !== '' && $requestedQuery !== '') {
            $error = ['error' => 'Cannot specify both report and query', 'errorCode' => 400];
        } else if (!$this->areDatabaseQueryToolQueriesAllowed() && $requestedQuery !== ''){
            $error = ['error' => 'Database query tool queries are not allowed', 'errorCode' => 403];
        } else if ($requestedQuery !== '' && !$this->isQueryAllowed($requestedQuery)) {
            $error = ['error' => 'Query is not allowed', 'errorCode' => 403];
        }

        if ($error) {
            $this->log("Reporting API error", ['error' => $error['error'], 'user' => $user, 'request' => json_encode($request)]);
            return $error;
        }

        if ($requestedReport !== '') {
            $result = $this->getReport($requestedReport);
        } else if ($requestedQuery !== '' && $this->isQueryAllowed($requestedQuery)) {
            $result = $this->getQuery($requestedQuery);
        } else {
            $result = ['error' => 'Invalid request', 'errorCode' => 400];
        }

        if (isset($result['error'])) {
            $this->log("Reporting API error", ['error' => $result['error'], 'user' => $user, 'request' => json_encode($request)]);
        } else {
            $this->log("Reporting API success", ['user' => $user, 'request' => json_encode($request)]);
        }
        return $result;
    }

    public function validateUser($token) : string|bool {
        $tokens = $this->framework->getSystemSetting('token') ?? [];

        foreach ($tokens as $user => $candidateToken) {
            if (password_verify($token, $candidateToken) && $this->isUserAllowed($user)) {
                return $user;
            }
        }
        return false;
    }

    private function getReport($reportName) {
        if ($reportName === 'project_housekeeping') {
            return $this->getProjectsReport();
        } else {
            return ['error' => 'Invalid report name', 'errorCode' => 400];
        }
    }

    private function getProjectsReport() {
        $sql = "SELECT 
                    p.project_id,
                    p.app_title 'project_name',
                    p.online_offline 'project_online',
                    CASE
                        WHEN completed_time IS NOT NULL THEN 'Completed'
                        WHEN status = 0 THEN 'Development'
                        WHEN status = 1 THEN 'Production'
                        WHEN status = 2 THEN 'Analysis/Cleanup'
                        ELSE 'Unknown'
                    END 'project_status',
                    p.creation_time 'project_created_on',
                    u.username 'project_created_by',
                    p.project_irb_number,
                    p.surveys_enabled 'surveys',
                    COALESCE(e.active, 0) * p.surveys_enabled 'econsent',
                    GROUP_CONCAT(DISTINCT(r.username) SEPARATOR ';') 'project_users',
                    GROUP_CONCAT(DISTINCT(r3.username) SEPARATOR ';') 'project_design',
                    GROUP_CONCAT(DISTINCT(r2.username) SEPARATOR ';') 'project_userrights',
                    (
                        SELECT `value` FROM redcap_config WHERE field_name = 'redcap_base_url'
                    ) 'project_phostid',
                    IF (p.twilio_enabled = 1 AND p.mosio_api_key IS NOT NULL, 1, 0) mosio,
                    IF (p.twilio_enabled = 1 AND p.twilio_account_sid IS NOT NULL, 1, 0) twilio,
                    IF (p.realtime_webservice_enabled AND p.realtime_webservice_type = 'FHIR', 1, 0) cdis,
                    IF (p.mycap_enabled, 1, 0) mycap,
                    IF (mad.mobile, 1, 0) mobile,
                    IF (mlm.active_languages > 1, 1, 0) mlm,
                    IF (api.api = 1, 1, 0) api,
                    IF (em.em = 1, 1, 0) em,
                    rcs.record_count records
                FROM redcap_projects p
                LEFT JOIN redcap_user_information u
                ON p.created_by = u.ui_id
                LEFT JOIN redcap_user_rights r
                ON p.project_id = r.project_id
                LEFT JOIN (
                    SELECT projects.project_id,
                        rights.username,
                        COALESCE(roles.user_rights, rights.user_rights) user_rights
                    FROM redcap_projects projects
                    LEFT JOIN redcap_user_rights rights
                    ON projects.project_id = rights.project_id
                    LEFT JOIN redcap_user_roles roles
                    ON rights.role_id = roles.role_id
                ) AS r2
                ON p.project_id = r2.project_id
                LEFT JOIN (
                    SELECT projects2.project_id,
                        rights2.username,
                        COALESCE(roles2.design, rights2.design) design
                    FROM redcap_projects projects2
                    LEFT JOIN redcap_user_rights rights2
                    ON projects2.project_id = rights2.project_id
                    LEFT JOIN redcap_user_roles roles2
                    ON rights2.role_id = roles2.role_id
                ) AS r3
                ON p.project_id = r3.project_id
                LEFT JOIN (
                    SELECT 
                        project_id, 
                        IF(SUM(active) > 0, 1, 0) 'active'
                    FROM redcap_econsent 
                    GROUP BY project_id
                ) AS e
                ON p.project_id = e.project_id
                LEFT JOIN (
                    SELECT project_id, 1 mobile 
                    FROM redcap_mobile_app_devices
                    WHERE revoked <> 1
                    GROUP BY project_id
                ) AS mad
                ON p.project_id = mad.project_id
                LEFT JOIN (
                    SELECT project_id, 
                    COUNT(value) active_languages 
                    FROM redcap_multilanguage_config
                    WHERE name = 'active'
                    AND project_id IS NOT NULL
                    AND project_id NOT IN (
                        SELECT project_id 
                        FROM redcap_multilanguage_config 
                        WHERE name = 'disabled' 
                        AND value = 1
                    )
                    GROUP BY project_id
                ) AS mlm
                ON p.project_id = mlm.project_id
                LEFT JOIN (
                    SELECT project_id, 
                    1 api 
                    FROM redcap_user_rights
                    WHERE api_token IS NOT NULL
                    GROUP BY project_id
                ) AS api
                ON p.project_id = api.project_id
                LEFT JOIN (
                    SELECT project_id, 1 em 
                    FROM redcap_external_module_settings
                    WHERE `key` = 'enabled'
                    AND `value` = 'true'
                    AND external_module_id IN (
                        SELECT external_module_id 
                        FROM redcap_external_module_settings 
                        WHERE `key` = 'version'
                    )
                    AND project_id IS NOT NULL
                    GROUP BY project_id
                ) em
                ON p.project_id = em.project_id
                LEFT JOIN redcap_record_counts rcs
                ON p.project_id = rcs.project_id
                WHERE r2.user_rights = 1
                AND r3.design = 1
                GROUP BY p.project_id";
        try {
            $result = $this->framework->query($sql, []);
            $projects = [];
            while ($row = $result->fetch_assoc()) {
                $projects[] = $row;
            }
            return $projects;
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage(), 'errorCode' => 500];
        }
    }

    private static function getProjectStatus($project) {
        if (!empty($project['completed_time'])) {
            return 'Completed';
        }
        $status = $project['status'];
        if ($status == 0) {
            return 'Development';
        } elseif ($status == 1) {
            return 'Production';
        } elseif ($status == 2) {
            return 'Analysis/Cleanup';
        } else {
            return 'Unknown';
        }
    }

    private function getQuery($queryNumber) {
        try {
            $queryNumber = (int)$queryNumber;
            if ($queryNumber < 1) {
                return ['error' => 'Invalid query number', 'errorCode' => 400];
            }
            $query = $this->getQueryByNumber($queryNumber);
            if (empty($query)) {
                return ['error' => 'Invalid query number', 'errorCode' => 400];
            }
            $cleanedQuery = $this->cleanQuery($query);
            if (empty($cleanedQuery)) {
                return ['error' => 'Invalid query', 'errorCode' => 400];
            }
            $result = $this->framework->query($cleanedQuery, []);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return ['data' => $data, 'query' => $query];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage(), 'errorCode' => 500];
        }
    }

    private function cleanQuery($sql, $options = []) {
        if (!isset($options['noop'])) {
            $sqlClean = trim($sql);
            if (empty($sqlClean)) {
                return '';
            }
            // Remove all lines that start with a comment
            $sqlClean = preg_replace('/^\s*--.*$/m', '', $sqlClean);
            // Remove all lines that start with a hash
            $sqlClean = preg_replace('/^\s*#.*$/m', '', $sqlClean);
            // Remove all lines that start with a forward slash and asterisk
            $sqlClean = preg_replace('/^\s*\/\*.*?\*\//s', '', $sqlClean);
            // Remove all lines that start with an asterisk
            $sqlClean = preg_replace('/^\s*\*.*$/m', '', $sqlClean);
            if (stripos($sqlClean, 'SELECT ') !== 0) {
                $this->log("cleanQuery error", ['error' => 'SQL query does not start with SELECT']);
                return '';
            }
        }
        return $sqlClean;
    }

    private function getQueryByNumber($queryNumber) {
        try {
            $sql = "SELECT query FROM redcap_custom_queries WHERE qid = ?";
            if (empty($sql)) {
                $this->log("getQueryByNumber error", ['error' => 'SQL query is empty']);
                return null;
            }
            $result = $this->framework->query($sql, [$queryNumber]);
            if (empty($result)) {
                return null;
            }
            $row = $result->fetch_assoc();
            if (empty($row)) {
                return null;
            }
            return $row['query'];
        } catch (\Throwable $e) {
            $this->log("getQueryByNumber error", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /** 
     * Get header Authorization
     * */
    private function getAuthorizationHeader(){
        $headers = '';
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        $this->log("getAuthorizationHeader", ['headers' => $headers]);
        return $headers;
    }

    /**
     * Get access token from header
     * */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s+(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

}
