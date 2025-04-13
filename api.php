<?php

namespace YaleREDCap\REDCapReportingAPI;

/** @var YaleProjectsApi $module */

try {
    $token_unsafe = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
    $token = trim($module->framework->sanitizeAPIToken($token_unsafe));
    $result = $module->handleApi($token);
    
    if (isset($result['error'])) {
        http_response_code($result['errorCode']);
        echo json_encode(['error' => $result['error']]);
        exit;
    }

    // Success
    http_response_code(200);
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}