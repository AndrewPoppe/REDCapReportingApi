<?php

namespace YaleREDCap\REDCapReportingAPI;

/** @var YaleProjectsApi $module */

$username = $module->framework->getUser()->getUserName();
$hasToken = $module->hasValidToken($username);
$truncatedToken = $module->getTruncatedToken($username);
$module->framework->initializeJavascriptModuleObject();

?>
<h4><i class="fas fa-cloud"></i> REDCap Reporting API</h4>
<div class="container">
    <div class="row">
        <div class="col">
            <p>
                The REDCap Reporting API is a simple API that allows you to access and manipulate data in your REDCap server.
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
                    <div class="row button-row">
                        <div class="col">
                            <button class="btn btn-warning" id="generate-token" type="button">
                                <i class="fas fa-arrows-rotate"></i> Regenerate API Token
                            </button>
                        </div>
                        <div class="col ms-auto">
                            <button class="btn btn-danger" id="delete-token" type="button">
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
                        <span><strong>Store this token someplace secure now, because you will not be able to retrieve it again.</strong></span>
                    </div>
                    <div class="row button-row">
                        <div class="col">
                            <button class="btn btn-primary" id="generate-token" type="button">
                                <i class="fas fa-key"></i> Generate API Token
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div id= "api-documentation" class="card mt-3 bg-light" style="<?= $hasToken ? '' : 'display: none;' ?>">
            <div class="card-body">
                <h5 class="card-title">API Documentation</h5>
                <div>
                    <span>
                        To call the API, send a <code>GET</code> to the following URL with your 
                        API token set as the query parameter <code>token</code> and the report you 
                        want to access set as the query parameter <code>report</code>.
                    </span>
                    <br>
                    <ul>
                        <li>Base url
                            <ul>
                                <li><span class="api_url"><code><?=$module->getApiUrl()?></code></span></li>
                            </ul>
                        </li>
                        <li><code>token</code> parameter
                            <ul>
                                <li><span>This is your API token</span></li>
                                <li>Example: <code>token=12345ABCDE67890FGHIJKL</code></li>
                            </ul>
                        </li>
                        <li><code>report</code> parameter
                            <ul>
                                <li>This is the report whose contents you want to access</li>
                                <li>Example: <code>report=project_housekeeping</code></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="accordion" id="apiEndpoints">
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
                                <div class="m-2 p-2">
                                    <span class="api_url"><code><?=$module->getApiUrl() . "&token="?></code><code class="api-url-token"><?=$truncatedToken?>&report=project_housekeeping</code></span>
                                </div>
                                <h4>Report contents</h4>
                                <table class="table table-striped table-sm align-middle table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Field Name</th>
                                            <th>Field Type</th>
                                            <th>Description</th>
                                            <th>Values</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">status</th>
                                            <td>string</td>
                                            <td>The status of the project, formatted as a string</td>
                                            <td>Development, Production, Analysis/Cleanup, Completed</td>
                                            <td>Development</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">online_offline</th>
                                            <td>integer</td>
                                            <td>The online/offline status of the project</td>
                                            <td>0 = Offline, 1 = Online</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_name</th>
                                            <td>string</td>
                                            <td>The name of the project</td>
                                            <td>N/A</td>
                                            <td>My Project</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_created_by</th>
                                            <td>string</td>
                                            <td>The username of the creator of the project</td>
                                            <td>N/A</td>
                                            <td>jdoe</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_phostid</th>
                                            <td>string</td>
                                            <td>The project host ID</td>
                                            <td><?=SERVER_NAME?></td>
                                            <td><?=SERVER_NAME?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_created_on</th>
                                            <td>string</td>
                                            <td>The date the project was created</td>
                                            <td>N/A</td>
                                            <td>1970-01-01 12:00:00</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_irb_number</th>
                                            <td>string</td>
                                            <td>The IRB number of the project</td>
                                            <td>N/A</td>
                                            <td>123456</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">project_users</th>
                                            <td>string</td>
                                            <td>The users associated with the project, formatted as a semicolon-delimited string</td>
                                            <td>N/A</td>
                                            <td>jdoe;jsmith</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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