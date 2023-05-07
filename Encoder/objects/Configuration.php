<?php

if (!class_exists('Configuration')) {

    if (!class_exists('ObjectYPT')) {
        require_once 'Object.php';
    }

    class Configuration extends ObjectYPT {

        protected $allowedStreamersURL, $defaultPriority, $version, $autodelete, $resolutions;

        static function getSearchFieldsNames() {
            return array('allowedStreamersURL');
        }

        static function getTableName() {
            global $global;
            return $global['tablesPrefix'] . 'configurations_encoder';
        }

        function __construct() {
            global $global;
            try {
                $this->load(1);
            } catch (Exception $exc) {
            }

            if (empty($this->version)) {
                $this->loadOld();
            }
        }

        function getAllowedStreamersURL() {
            return $this->allowedStreamersURL;
        }

        function getDefaultPriority() {
            return $this->defaultPriority;
        }

        function setAllowedStreamersURL($allowedStreamersURL) {
            $this->allowedStreamersURL = $allowedStreamersURL;
        }

        function setDefaultPriority($defaultPriority) {
            $this->defaultPriority = $defaultPriority;
        }

        function getSelectedResolutions() {
            $resolutions = Format::sanitizeResolutions(json_decode($this->resolutions));
            if (empty($resolutions)) {
                $resolutions = Format::getAvailableResolutions();
            }
            return $resolutions;
        }

        function setSelectedResolutions($resolutions) {
            $resolutions = Format::sanitizeResolutions($resolutions);
            if (!empty($resolutions)) {
                $this->resolutions = json_encode($resolutions);
            }
        }

        function getVersion() {
            return $this->version;
        }

        function setVersion($version) {
            $this->version = $version;
        }

        function getAutodelete() {
            return $this->autodelete;
        }

        function setAutodelete($autodelete) {
            $this->autodelete = (empty($autodelete) || strtolower($autodelete) === 'false') ? 0 : 1;
        }

        function currentVersionLowerThen($version) {
            return version_compare($version, $this->getVersion()) > 0;
        }

        function currentVersionGreaterThen($version) {
            return version_compare($version, $this->getVersion()) < 0;
        }

        function currentVersionEqual($version) {
            return version_compare($version, $this->getVersion()) == 0;
        }

        static function rewriteConfigFile($configurationVersion = 2) {
            global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase;
            $content = "<?php
\$global['configurationVersion'] = {$configurationVersion};
\$global['tablesPrefix'] = '{$global['tablesPrefix']}';
\$global['webSiteRootURL'] = '{$global['webSiteRootURL']}';
\$global['systemRootPath'] = '{$global['systemRootPath']}';
\$global['webSiteRootPath'] = '" . (@$global['webSiteRootPath']) . "';

\$global['disableConfigurations'] = " . intval($global['disableConfigurations']) . ";
\$global['disableBulkEncode'] = " . intval($global['disableBulkEncode']) . ";
\$global['disableWebM'] = " . intval($global['disableWebM']) . ";
\$global['hideUserGroups'] = " . intval($global['hideUserGroups']) . ";
\$global['concurrent'] = " . intval($global['concurrent']) . ";

\$mysqlHost = '{$mysqlHost}';
\$mysqlUser = '{$mysqlUser}';
\$mysqlPass = '{$mysqlPass}';
\$mysqlDatabase = '{$mysqlDatabase}';

\$global['allowed'] = array('" . implode("', '", $global['allowed']) . "');
/**
 * Do NOT change from here
 */
if (empty(\$global['webSiteRootPath'])){
    preg_match('/https?:\/\/[^\/]+(.*)/i', \$global['webSiteRootURL'], \$matches);
    if (!empty(\$matches[1])){
        \$global['webSiteRootPath'] = \$matches[1];
    }
}
if (empty(\$global['webSiteRootPath'])){
    die('Please configure your webSiteRootPath');
}

require_once \$global['systemRootPath'] . 'objects/include_config.php';
";

            $fp = fopen($global['systemRootPath'] . "videos/configuration.php", "wb");
            fwrite($fp, $content);
            fclose($fp);
        }

        static function getOldConfig() {
            global $global;
            $sql = "SELECT * FROM configurations WHERE  id = 1 LIMIT 1";
            $global['lastQuery'] = $sql;
            $res = $global['mysqli']->query($sql);
            return $res ? $res->fetch_assoc() : false;
        }

        protected function loadOld() {
            $user = self::getOldConfig();
            if (empty($user)) {
                return false;
            }
            foreach ($user as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }
    }
}
