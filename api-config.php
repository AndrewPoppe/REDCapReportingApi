<?php

namespace YaleREDCap\REDCapReportingAPI;

/** @var YaleProjectsApi $module */

?>
<h4><i class="fas fa-cloud"></i> REDCap Reporting API</h4>
<div>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Active</a>
        </li>
        <?php if ($module->hasValidToken($module->framework->getUser()->getUsername())) { ?>
        <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
        </li>
        <?php } ?>
    </ul>
</div>
<style>
    .nav-tabs {
        margin-bottom: 1rem;
        border-bottom: 1px solid #ddd;
    }
    .nav-tabs .nav-link {
        border: 1px solid #ddd;
    }
    .nav-tabs .nav-link.active {
        background-color: #f8f9fa;
        border-color: #ddd #ddd #fff;
    }
</style>