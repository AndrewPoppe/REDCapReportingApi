<?php

namespace YaleREDCap\REDCapReportingAPI;

/** @var YaleProjectsApi $module */

$username = $module->framework->getUser()->getUserName();
$hasToken = $module->hasValidToken($username);
$truncatedToken = $module->getTruncatedToken($username);
$customQueriesActive = $module->areDatabaseQueryToolQueriesAllowed();
$allowedQueries = $module->getAllowedQueries();
$module->framework->initializeJavascriptModuleObject();

?>
<h4><i class="fas fa-cloud"></i> REDCap Reporting API</h4>
<div class="container">
    <div class="row">
        <div class="col">
            <p>
                The REDCap Reporting API provides a simple API that allows a user to access data in your REDCap server.
                It is designed to be easy to use and integrate with other applications.
            </p>
        </div>
    </div>
    <div class="row">
        <?php if ($hasToken) { ?>
            <div class="card bg-primary-subtle">
                <div class="card-body">
                    <h5 class="card-title">API Token</h5>
                    <p>
                        The API token is a unique identifier that allows you to access the API. 
                        It is important to keep this token secure and not share it with anyone.
                    </p>
                    <div class="m-2 p-2" id="api-token-container" style="display: none;">
                        <span>Your API Token: </span>
                        <code id="api-token"></code>
                        <br>
                        <span><strong>Store this token someplace secure now, because you will not be able to retrieve it again.</strong></span>
                    </div>
                    <div class="m-2 p-2" id="truncated-token-container">
                        <span>Your API Token: </span>
                        <code id="truncated-token"><?= $truncatedToken ?></code>
                    </div>
                    <div class="row button-row justify-content-end">
                        <div class="col-auto">
                            <button class="btn btn-sm btn-warning" id="generate-token" type="button">
                                <i class="fas fa-arrows-rotate"></i> Regenerate API Token
                            </button>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-danger" id="delete-token" type="button">
                                <i class="fas fa-trash"></i> Delete API Token
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div id="no-token-card" class="card bg-danger-subtle">
                <div class="card-body">
                    <h5 id="no-token-title" class="card-title">You do not have an active API token</h5>
                    <p>
                        The API token is a unique identifier that allows you to access the API. 
                        It is important to keep this token secure and not share it with anyone.
                    </p>
                    <div class="m-2 p-2" id="api-token-container" style="display: none;">
                        <span>Your API Token: </span>
                        <code id="api-token"></code>
                        <br>
                        <span class="text-dangerrc"><strong>Store this token someplace secure now, because you will not be able to retrieve it again.</strong></span>
                    </div>
                    <div class="row button-row justify-content-center">
                        <div class="col-auto">
                            <button class="btn btn-primary" id="generate-token" type="button">
                                <i class="fas fa-key"></i> Generate API Token
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div id="api-documentation" class="card mt-3 bg-light" style="<?= $hasToken ? '' : 'display: none;' ?>">
            <div class="card-body">
                <h5 class="card-title">API Documentation</h5>
                <div>
                    <span>
                        To call the API, send a <code>GET</code> to the following URL with an authorization header 
                        containing your <code>Bearer</code> token and the report/query you want to access set as the 
                        query parameter <code>report</code> or <code>query</code> respectively. 
                    </span>
                    <br>
                    <ul>
                        <li>Base url
                            <ul>
                                <li><span class="api_url"><code><?=$module->getApiUrl()?></code></span></li>
                            </ul>
                        </li>
                        <li><code>report</code> parameter
                            <ul>
                                <li>This is the report whose contents you want to access</li>
                                <li>Example: <code>report=project_housekeeping</code></li>
                            </ul>
                        </li>
                        <li><code>query</code> parameter
                            <ul>
                                <li>This is the query ID whose contents you want to access</li>
                                <li>Example: <code>query=123</code></li>
                            </ul>
                        </li>
                        <li>Authorization header
                            <ul>
                                <li><span>This will contain your API token as a <code>Bearer</code> token</span></li>
                                <li>Example: <code>Authorization: Bearer 12345ABCDE67890FGHIJKL</code></li>
                            </ul>
                        </li>
                    </ul>
                    <p><span><em>Note: You can only specify one of the following parameters:</em></span></p>
                    <ul>
                        <li><code>report</code> - to access a specific report</li>
                        <li><code>query</code> - to access a specific query</li>
                    </ul>
                    <p>
                        The response will be a JSON object containing the requested report or query data. The details of
                        the response will depend on whether you are accessing a report or query. See details below.
                    </p>
                </div>
            </div>
        </div>
        <div class="card mt-3 bg-light" id="apiReportDocumentation" style="<?= $hasToken ? '' : 'display: none;' ?>">
            <div class="card-body">
                <h5 class="card-title">API Built-In Report Documentation</h5>
                <p><span>
                    The following built-in reports are available via the API. 
                    They are designed to provide useful information about your REDCap server and projects.
                </span></p>
                <p><strong>The return value will be a JSON object containing the requested report data.</strong></p>
                <h6><strong>Available Reports:</strong></h6>
                <div class="accordion" id="apiReports">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="api_housekeeping_heading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#api_project_housekeeping" aria-expanded="true" aria-controls="collapseOne">
                                Project Housekeeping
                            </button>
                        </h2>
                        <div id="api_project_housekeeping" class="accordion-collapse collapse show" aria-labelledby="api_housekeeping_heading">
                            <div class="accordion-body">
                                <p>
                                    The Project Housekeeping report provides a list of all projects in the REDCap server, along with their status and other information.
                                </p>
                                <p>
                                    The API token is required to access this report. 
                                    The report is returned as a JSON object.
                                </p>
                                <h4 class="mt-2">Info</h4>
                                    <ul>
                                        <li><strong>Query report value</strong>: <code>project_housekeeping</code></li>
                                        <li><strong>Return Type</strong>: <code>JSON</code></li>
                                        <li><strong>Report URL</strong>: <span class="api_url"><code><?=$module->getApiUrl() . "&report=<mark>project_housekeeping</mark>"?></code></span></li>
                                    </ul>
                                    
                                
                                <h4>Report contents</h4>
                                <table class="table table-striped table-sm align-middle table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Field Name</th>
                                            <th>Description</th>
                                            <th>Values</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">project_id</th>
                                            <td>The project ID, formatted as a string</td>
                                            <td>N/A</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">status</th>
                                            <td>The status of the project, formatted as a string</td>
                                            <td>Development, Production, Analysis/Cleanup, Completed</td>
                                            <td>Development</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">online_offline</th>
                                            <td>The online/offline status of the project</td>
                                            <td>0 = Offline, 1 = Online</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_name</th>
                                            <td>The name of the project</td>
                                            <td>N/A</td>
                                            <td>My Project</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_created_by</th>
                                            <td>The username of the creator of the project</td>
                                            <td>N/A</td>
                                            <td>jdoe</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_phostid</th>
                                            <td>The project host ID</td>
                                            <td><?=SERVER_NAME?></td>
                                            <td><?=SERVER_NAME?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_created_on</th>
                                            <td>The date the project was created</td>
                                            <td>N/A</td>
                                            <td>1970-01-01 12:00:00</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_irb_number</th>
                                            <td>The IRB number of the project</td>
                                            <td>N/A</td>
                                            <td>123456</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_users</th>
                                            <td>The users associated with the project, formatted as a semicolon-delimited string</td>
                                            <td>N/A</td>
                                            <td>jdoe;jsmith</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_design</th>
                                            <td>The users with design rights associated with the project, formatted as a semicolon-delimited string</td>
                                            <td>N/A</td>
                                            <td>jdoe;jsmith</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_userrights</th>
                                            <td>The users with user rights permissions associated with the project, formatted as a semicolon-delimited string</td>
                                            <td>N/A</td>
                                            <td>jdoe;jsmith</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">econsent</th>
                                            <td>Whether the project has any eConsent surveys enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">records</th>
                                            <td>The number of records in the project</td>
                                            <td>N/A</td>
                                            <td>100</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">surveys</th>
                                            <td>Whether the project has any surveys enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">mosio</th>
                                            <td>Whether the project has any Mosio integrations enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">cdis</th>
                                            <td>Whether the project has any CDIS integrations enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">twilio</th>
                                            <td>Whether the project has any Twilio integrations enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">mycap</th>
                                            <td>Whether the project has any MyCAP integrations enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">mobileapp</th>
                                            <td>Whether the project has any Mobile App integrations enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">mlm</th>
                                            <td>Whether the project has any MLM languages enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">api</th>
                                            <td>Whether the project has any API tokens enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">em</th>
                                            <td>Whether the project has any External Modules enabled</td>
                                            <td>0 = No, 1 = Yes</td>
                                            <td>0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3 bg-light" style="<?= ($hasToken && $customQueriesActive) ? '' : 'display: none;' ?>">
            <div class="card-body">
                <h5 class="card-title">API Custom Query Documentation</h5>
                <p>
                    The Custom Query API allows you to access queries saved in the Database Query Tool via this API.
                </p>
                <p>
                    <span><strong>The return value will be a JSON object with the following structure:</strong></span>
                    <ul>
                        <li><code>data</code> - An array of objects with the query results</li>
                        <li><code>query</code> - The SQL query text</li>
                    </ul>
                </p>
                <h4>Available Custom Queries</h4>
                <table class="table table-striped table-sm align-middle text-center table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Folder</th>
                            <th>Query Name</th>
                            <th>Query ID (qid) - use this in your API calls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            if (empty($allowedQueries)) { ?>
                            <tr>
                                <td colspan="3" class="text-center">No custom queries available</td>
                            </tr>
                        <?php } else {
                        foreach ($module->getAllowedQueries() as $query) { ?>
                            <tr>
                                <td><?= $module->framework->escape($query['folder']) ?></td>
                                <td><?= $module->framework->escape($query['title']) ?></td>
                                <td><?= $module->framework->escape($query['value']) ?></td>
                            </tr>
                        <?php }} ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>
<style>
    .api_url {
        user-select: all;
        font-size: 1.2em;
        font-family: monospace;
    }
</style>
<script>
    $(function() {
        const module = <?=$module->getJavascriptModuleObjectName()?>;

        $('#generate-token').on('click', function() {
            module.ajax('generate-token').then(function(response) {
                $('#truncated-token-container').hide();
                $('#api-token').text(response.token);
                $('#api-token-container').show();
                $('.button-row').hide();
                $('#api-documentation').show();
                $('.api-url-token').text(response.token);
                $('#no-token-card').toggleClass('bg-danger-subtle bg-success-subtle');
                $('#no-token-title').text('API Token Generated');
            }).catch(function(err) {
                console.error(err);
            });
        });
        $('#delete-token').on('click', function() {
            module.ajax('delete-token').then(function(response) {
                document.location.reload();
            }).catch(function(err) {
                console.error(err);
            });
        });
    })
</script>