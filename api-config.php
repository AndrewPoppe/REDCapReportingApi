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
                        API token set as the query parameter <code>token</code>:
                    </span>
                    <br>
                    <div class="m-2 p-2">
                        <code><?=$module->getApiUrl() . "&token="?></code><code id="api-url-token"><?=$truncatedToken?></code>
                    </div>
                </div>
            </div>
        </div>
</div>
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
                $('#api-url-token').text(response.token);
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