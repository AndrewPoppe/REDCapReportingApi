<?php

namespace YaleREDCap\REDCapReportingAPI;

/** @var YaleProjectsApi $module */

try {
    // $token_unsafe = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
    $token_unsafe = $module->getBearerToken();
    $token = trim($module->framework->sanitizeAPIToken($token_unsafe));

    $report = trim(filter_input(INPUT_GET, 'report', FILTER_SANITIZE_SPECIAL_CHARS));
    $query = trim(filter_input(INPUT_GET, 'query', FILTER_SANITIZE_SPECIAL_CHARS));
    $result = $module->handleApi($token, ['report' => $report, 'query' => $query]);    
    
    if (isset($result['error'])) {
        http_response_code($result['errorCode']);
        header('Content-Type: application/json');
        echo json_encode(['error' => $result['error']]);
        exit;
    }

    // Success
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}