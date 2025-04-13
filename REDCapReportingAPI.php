<?php

namespace YaleREDCap\REDCapReportingAPI;

/**
 * @property \ExternalModules\Framework $framework
 * @see Framework
 */

 class REDCapReportingAPI extends \ExternalModules\AbstractExternalModule
 {
    public function hasValidToken(string $username) : bool {
        return true;
    }
 }