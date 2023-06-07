<?php

interface ObjectInterface {

    public static function getTableName();
}

$tableExists = [];

abstract class ObjectYPT implements ObjectInterface {

    protected $properties = [];
    protected $fieldsName = [];
    protected $id;
    protected $created;

    public function __construct($id = "") {
        if (!empty($id)) {
            // get data from id
            $this->load($id);
        }
    }

    public static function getSearchFieldsNames() {
        return [];
    }

    public function load($id) {
        $row = self::getFromDb($id);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public static function getNowFromDB() {
        global $global;
        $sql = "SELECT NOW() as my_date_field";
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function setGlobalTimeZone() {
        global $advancedCustom, $timezoneOriginal;
        if (!isset($timezoneOriginal)) {
            $timezoneOriginal = date_default_timezone_get();
        }
        if (!empty($_COOKIE['timezone']) && $_COOKIE['timezone'] !== 'undefined') {
            $timezone = $_COOKIE['timezone'];
        } else {
            $timeZOnesOptions = object_to_array($advancedCustom->timeZone->type);
            $timezone = $timeZOnesOptions[$advancedCustom->timeZone->value];
        }
        if (empty($timezone) || $timezone == 'undefined') {
            return false;
        }
        date_default_timezone_set($timezone);
    }

    static function getFromDb($id, $refreshCache = false) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  id = ? LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "i", [$id], $refreshCache);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getAll() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function getAllActive() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function getTotal() {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    public static function getSqlFromPost($keyPrefix = "", $searchTableAlias = '') {
        global $global;
        $sql = self::getSqlSearchFromPost($searchTableAlias);

        if (empty($_POST['sort']) && !empty($_GET['order'][0]['dir'])) {
            $index = intval($_GET['order'][0]['column']);
            $_GET['columns'][$index]['data'];
            $_POST['sort'][$_GET['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }

        // add a security here
        if (!empty($_POST['sort'])) {
            foreach ($_POST['sort'] as $key => $value) {
                $_POST['sort'][xss_esc($key)] = xss_esc($value);
            }
        }

        if (!empty($_POST['sort'])) {
            $orderBy = [];
            foreach ($_POST['sort'] as $key => $value) {
                $key = ($key);
                //$value = ($value);
                $direction = "ASC";
                if (strtoupper($value) === "DESC") {
                    $direction = "DESC";
                }
                $key = preg_replace("/[^A-Za-z0-9._ ]/", '', $key);
                $key = trim($key);
                if (strpos($key, '.') === false) {
                    $key = "`{$key}`";
                }
                $orderBy[] = " {$keyPrefix}{$key} {$value} ";
            }
            $sql .= " ORDER BY " . implode(",", $orderBy);
        }

        $sql .= self::getSqlLimit();
        return $sql;
    }

    public static function getSqlLimit() {
        global $global;
        $sql = '';

        if (empty($_POST['rowCount']) && !empty($_GET['length'])) {
            $_POST['rowCount'] = intval($_GET['length']);
        }

        if (empty($_POST['current']) && !empty($_GET['start'])) {
            $_POST['current'] = ($_GET['start'] / $_GET['length']) + 1;
        } elseif (empty($_POST['current']) && isset($_GET['start'])) {
            $_POST['current'] = 1;
        }

        $_POST['current'] = getCurrentPage();
        $_POST['rowCount'] = getRowCount();

        if (!empty($_POST['rowCount']) && !empty($_POST['current']) && $_POST['rowCount'] > 0) {
            $_POST['rowCount'] = intval($_POST['rowCount']);
            $_POST['current'] = intval($_POST['current']);
            $current = ($_POST['current'] - 1) * $_POST['rowCount'];
            $current = $current < 0 ? 0 : $current;
            $sql .= " LIMIT $current, {$_POST['rowCount']} ";
        } else {
            $_POST['current'] = 0;
            $_POST['rowCount'] = 0;
            $sql .= " LIMIT 1000 ";
        }
        return $sql;
    }

    public static function getSqlDateFilter($searchTableAlias = '') {
        $sql = '';
        $created_year = intval(@$_REQUEST['created_year']);
        $created_month = intval(@$_REQUEST['created_month']);
        $modified_year = intval(@$_REQUEST['modified_year']);
        $modified_month = intval(@$_REQUEST['modified_month']);
        if (!empty($searchTableAlias)) {
            $searchTableAlias = "`$searchTableAlias`.";
        }
        if (!empty($created_year)) {
            $sql .= " AND YEAR({$searchTableAlias}created) = $created_year ";
        }
        if (!empty($created_month)) {
            $sql .= " AND MONTH({$searchTableAlias}created) = $created_month ";
        }
        if (!empty($modified_year)) {
            $sql .= " AND YEAR({$searchTableAlias}modified) = $modified_year ";
        }
        if (!empty($modified_month)) {
            $sql .= " AND MONTH({$searchTableAlias}modified) = $modified_month ";
        }

        return $sql;
    }

    public static function getSqlSearchFromPost($searchTableAlias = '') {
        $sql = self::getSqlDateFilter($searchTableAlias);
        if (!empty($_POST['searchPhrase'])) {
            $_GET['q'] = $_POST['searchPhrase'];
        } elseif (!empty($_GET['search']['value'])) {
            $_GET['q'] = $_GET['search']['value'];
        }
        if (!empty($_GET['q'])) {
            global $global;
            $search = strtolower(xss_esc($_GET['q']));

            $like = [];
            $searchFields = static::getSearchFieldsNames();
            foreach ($searchFields as $value) {
                if (!str_contains($value, '.') && !str_contains($value, '`')) {
                    $value = "`{$value}`";
                }
                $like[] = " {$value} LIKE '%{$search}%' ";
                // for accent insensitive
                $like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) LIKE '%{$search}%' ";
            }
            if (!empty($like)) {
                $sql .= " AND (" . implode(" OR ", $like) . ")";
            } else {
                $sql .= " AND 1=1 ";
            }
        }

        return $sql;
    }

    public function save() {
        if (!$this->tableExists()) {
            _error_log("Save error, table " . static::getTableName() . " does not exists", AVideoLog::$ERROR);
            return false;
        }
        if (!self::ignoreTableSecurityCheck() && isUntrustedRequest("SAVE " . static::getTableName())) {
            return false;
        }
        global $global;
        $fieldsName = $this->getAllFields();
        if (empty($fieldsName)) {
            _error_log("Save error, table " . static::getTableName() . " MySQL Error", AVideoLog::$ERROR);
            return false;
        }
        $formats = '';
        $values = [];
        if (!empty($this->id)) {
            $sql = "UPDATE " . static::getTableName() . " SET ";
            $fields = [];
            foreach ($fieldsName as $value) {
                //$escapedValue = $global['mysqli']->real_escape_string($this->$value);
                if (strtolower($value) == 'created') {
                    //var_dump($this->created);exit;
                    if (
                            !empty($this->created) && (
                            User::isAdmin() ||
                            isCommandLineInterface() ||
                            (class_exists('API') && API::isAPISecretValid())
                            )) {
                        $this->created = preg_replace('/[^0-9: \/-]/', '', $this->created);
                        //_error_log("created changed in table=".static::getTableName()." id={$this->id} created={$this->created}");
                        $formats .= 's';
                        $values[] = $this->created;
                        $fields[] = " `{$value}` = ? ";
                    }
                } elseif (strtolower($value) == 'modified') {
                    $fields[] = " {$value} = now() ";
                } elseif (strtolower($value) == 'timezone') {
                    if (empty($this->$value)) {
                        $this->$value = date_default_timezone_get();
                    }
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " `{$value}` = ? ";
                } elseif (!isset($this->$value) || strtolower($this->$value) == 'null') {
                    $fields[] = " `{$value}` = NULL ";
                } else {
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " `{$value}` = ? ";
                }
            }
            $sql .= implode(", ", $fields);
            $formats .= 'i';
            $values[] = $this->id;
            $sql .= " WHERE id = ?";
        } else {
            $sql = "INSERT INTO " . static::getTableName() . " ( ";
            $sql .= "`" . implode("`,`", $fieldsName) . "` )";
            $fields = [];
            foreach ($fieldsName as $value) {
                if (is_string($value) && (strtolower($value) == 'created' || strtolower($value) == 'modified')) {
                    if (strtolower($value) == 'created') {
                        if (empty($this->created) || (!User::isAdmin() && !isCommandLineInterface())) {
                            $fields[] = " now() ";
                        } else {
                            $this->created = preg_replace('/[^0-9: \/-]/', '', $this->created);
                            $formats .= 's';
                            $values[] = $this->created;
                            $fields[] = " ? ";
                        }
                    } else {
                        $fields[] = " now() ";
                    }
                } elseif (is_string($value) && strtolower($value) == 'timezone') {
                    if (empty($this->$value)) {
                        $this->$value = date_default_timezone_get();
                    }
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " ? ";
                } elseif (!isset($this->$value) || (is_string($this->$value) && strtolower($this->$value) == 'null')) {
                    $fields[] = " NULL ";
                } elseif (is_string($this->$value) || is_numeric($this->$value)) {
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " ? ";
                } else {
                    $fields[] = " NULL ";
                }
            }
            $sql .= " VALUES (" . implode(", ", $fields) . ")";
        }
        //var_dump(static::getTableName(), $sql, $values);
        //if(static::getTableName() == 'videos'){ echo $sql;var_dump($values); var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));}//return false;
        //echo $sql;var_dump($this, $values);exit;
        $insert_row = sqlDAL::writeSql($sql, $formats, $values);

        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */
        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
            } else {
                $id = $this->id;
            }
            return $id;
        } else {
            _error_log("ObjectYPT::Error on save 1: " . $sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error . ' ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$ERROR);
            return false;
        }
    }

    private function getAllFields() {
        global $global, $mysqlDatabase;
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = '" . static::getTableName() . "'";
        $res = sqlDAL::readSql($sql, "s", [$mysqlDatabase]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $rows[] = $row["COLUMN_NAME"];
            }
        }
        return $rows;
    }

    public function delete() {
        global $global;
        if (!empty($this->id)) {

            if (!self::ignoreTableSecurityCheck() && isUntrustedRequest("DELETE " . static::getTableName())) {
                return false;
            }
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", [$this->id]);
        }
        _error_log("Id for table " . static::getTableName() . " not defined for deletion " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$ERROR);
        return false;
    }

    static function ignoreTableSecurityCheck() {

        $ignoreArray = [
            'vast_campaigns_logs',
            'videos', 'CachesInDB',
            'plugins',
            'users_login_history',
            'live_transmitions_history',
            'logincontrol_history',
            'wallet',
            'audit',
            'wallet_log',
            'live_restreams_logs',
            'clone_SitesAllowed'
        ];
        return in_array(static::getTableName(), $ignoreArray);
    }

    public static function shouldUseDatabase($content) {
        global $advancedCustom, $global;
        if (!empty($global['doNotUseCacheDatabase'])) {
            return false;
        }
        $maxLen = 60000;

        if (empty($advancedCustom)) {
            $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
        }

        if (empty($advancedCustom->doNotSaveCacheOnFilesystem) && AVideoPlugin::isEnabledByName('Cache') && self::isTableInstalled('CachesInDB')) {
            $json = _json_encode($content);
            if (empty($json)) {
                return false;
            }
            $len = strlen($json);
            if ($len > $maxLen / 2) {
                return false;
            }
            if (class_exists('CachesInDB')) {
                $content = CachesInDB::encodeContent($json);
            } else {
                $content = base64_encode($json);
            }

            $len = strlen($content);
            if (!empty($len) && $len < $maxLen) {
                return $content;
            } elseif (!empty($len)) {
                //_error_log('Object::setCache '.$len);
            }
        }

        return false;
    }

    public static function setCacheGlobal($name, $value, $addSubDirs = true) {
        return self::setCache($name, $value, $addSubDirs, true);
    }

    public static function setCache($name, $value, $addSubDirs = true, $ignoreMetadata = false) {
        if (!self::isToSaveInASubDir($name) && $content = self::shouldUseDatabase($value)) {
            $saved = Cache::_setCache($name, $content);
            if (!empty($saved)) {
                return $saved;
            }
        }

        $content = _json_encode($value);
        if (empty($content)) {
            $content = $value;
        }

        if (empty($content)) {
            return false;
        }

        $cachefile = self::getCacheFileName($name, true, $addSubDirs, $ignoreMetadata);
        make_path($cachefile);
        //_error_log("YPTObject::setCache log error [{$name}] $cachefile filemtime = ".filemtime($cachefile));
        $bytes = @file_put_contents($cachefile, $content);
        self::setSessionCache($name, $value);
        return ['bytes' => $bytes, 'cachefile' => $cachefile, 'type' => 'file'];
    }

    public static function cleanCacheName($name) {
        //return sha1($name);
        $parts = explode(DIRECTORY_SEPARATOR, $name);

        $lastPart = sha1(array_pop($parts));
        $parts[] = $lastPart;
        $name = implode(DIRECTORY_SEPARATOR, $parts);
        return $name;
        /*
          $name = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $name);
          $name = preg_replace('/[!#$&\'()*+,:;=?@[\\]% -]+/', '_', trim(strtolower(cleanString($name))));
          $name = preg_replace('/\/{2,}/', '/', trim(strtolower(cleanString($name))));
          if (function_exists('mb_ereg_replace')) {
          $name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).\\/\\\])", '', $name);
          // Remove any runs of periods (thanks falstro!)
          $name = mb_ereg_replace("([\.]{2,})", '', $name);
          }
          return preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
         * */
    }

    public static function getCacheGlobal($name, $lifetime = 60, $ignoreSessionCache = false, $addSubDirs = true) {
        return self::getCache($name, $lifetime, $ignoreSessionCache, $addSubDirs, true);
    }

    /**
     *
     * @param string $name
     * @param int $lifetime, if is = 0 it is unlimited
     * @return object|string
     */
    public static function getCache($name, $lifetime = 60, $ignoreSessionCache = false, $addSubDirs = true, $ignoreMetadata = false) {
        global $global;
        if (!empty($global['ignoreAllCache'])) {
            return null;
        }
        self::setLastUsedCacheMode("No cache detected $name, $lifetime, " . intval($ignoreSessionCache));
        if (isCommandLineInterface()) {
            return null;
        }
        if (isBot()) {
            $lifetime = 0;
        }
        global $getCachesProcessed, $_getCache;

        if (empty($_getCache)) {
            $_getCache = [];
        }

        if (empty($getCachesProcessed)) {
            $getCachesProcessed = [];
        }
        //if($name=='getVideosURL_V2video_220721204450_v21b7'){var_dump($name);exit;}
        $cachefile = self::getCacheFileName($name, false, $addSubDirs, $ignoreMetadata);
        //if($name=='getVideosURL_V2video_220721204450_v21b7'){var_dump($cachefile);exit;}//exit;
        self::setLastUsedCacheFile($cachefile);
        //_error_log("getCache: cachefile [$name] ".$cachefile);
        if (!empty($_getCache[$name])) {
            //_error_log('getCache: '.__LINE__);
            self::setLastUsedCacheMode("Global Variable \$_getCache[$name]");
            return $_getCache[$name];
        }

        if (empty($getCachesProcessed[$name])) {
            $getCachesProcessed[$name] = 0;
        }
        $getCachesProcessed[$name]++;

        if (!empty($_GET['lifetime'])) {
            $lifetime = intval($_GET['lifetime']);
        }

        if (empty($ignoreSessionCache)) {
            $session = self::getSessionCache($name, $lifetime);
            if (!empty($session)) {
                self::setLastUsedCacheMode("Session cache \$_SESSION['user']['sessionCache'][$name]");
                $_getCache[$name] = $session;
                //_error_log('getCache: '.__LINE__);
                return $session;
            }
        }

        if (class_exists('Cache')) {
            $cache = Cache::getCache($name, $lifetime, $ignoreMetadata);
            if (!empty($cache)) {
                self::setLastUsedCacheMode("Cache::getCache($name, $lifetime, $ignoreMetadata)");
                return $cache;
            }
        }

        /*
          if (preg_match('/firstpage/i', $cachefile)) {
          echo var_dump($cachefile) . PHP_EOL;
          $trace = debug_backtrace();
          $backtrace_lite = array();
          foreach ($trace as $call) {
          echo $call['function'] . "    " . $call['file'] . "    line " . $call['line'] . PHP_EOL;
          }exit;
          }
          /**
         */
        if (file_exists($cachefile) && (empty($lifetime) || time() - $lifetime <= filemtime($cachefile))) {
            //if(preg_match('/getStats/', $cachefile)){echo $cachefile,'<br>';}
            self::setLastUsedCacheMode("Local File $cachefile");
            $c = @url_get_contents($cachefile);
            $json = _json_decode($c);

            if (empty($json) && !is_object($json) && !is_array($json)) {
                $json = $c;
            }

            self::setSessionCache($name, $json);
            $_getCache[$name] = $json;
            //_error_log('getCache: '.__LINE__);
            return $json;
        } elseif (file_exists($cachefile) && !empty($lifetime)) {
            self::deleteCache($name);
            @unlink($cachefile);
        }
        //var_dump(file_exists($cachefile), $cachefile);
        //if(preg_match('/getChannelsWithMoreViews30/i', $name)){var_dump($name, $cachefile, file_exists($cachefile) , $lifetime, time() - $lifetime, filemtime($cachefile));exit;}
        //_error_log("YPTObject::getCache log error [{$name}] $cachefile filemtime = ".filemtime($cachefile));
        return null;
    }

    private static function setLastUsedCacheMode($mode) {
        global $_lastCacheMode;
        $_lastCacheMode = $mode;
    }

    private static function setLastUsedCacheFile($cachefile) {
        global $_lastCacheFile;
        $_lastCacheFile = $cachefile;
    }

    public static function getLastUsedCacheInfo() {
        global $_lastCacheFile, $_lastCacheMode;
        return ['file' => $_lastCacheFile, 'mode' => $_lastCacheMode];
    }

    public static function deleteCache($name, $addSubDirs = true) {
        if (empty($name)) {
            return false;
        }
        if (!class_exists('Cache')) {
            AVideoPlugin::loadPlugin('Cache');
        }

        if (class_exists('Cache')) {
            Cache::deleteCache($name);
        }

        global $__getAVideoCache;
        unset($__getAVideoCache);
        //_error_log('deleteCache: '.json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $cachefile = self::getCacheFileName($name, false, $addSubDirs);
        @unlink($cachefile);
        self::deleteSessionCache($name);
        ObjectYPT::deleteCacheFromPattern($name);
    }

    public static function deleteCachePattern($pattern) {
        global $__getAVideoCache;
        unset($__getAVideoCache);
        $tmpDir = self::getCacheDir();
        $array = _glob($tmpDir, $pattern);
        _error_log('deleteCachePattern: ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        foreach ($array as $value) {
            _error_log("Object::deleteCachePattern file [{$value}]");
            @unlink($value);
        }
        _session_start();
        foreach ($_SESSION['user']['sessionCache'] as $key => $value) {
            if (preg_match($pattern, $key)) {
                _error_log("Object::deleteCachePattern session [{$key}]");
                $_SESSION['user']['sessionCache'][$key] = null;
                unset($_SESSION['user']['sessionCache'][$key]);
            }
        }
    }

    public static function deleteALLCache() {
        if (!class_exists('Cache')) {
            AVideoPlugin::loadPluginIfEnabled('Cache');
        }
        if (class_exists('Cache')) {
            Cache::deleteAllCache();
        }
        self::deleteAllSessionCache();
        $lockFile = getVideosDir() . '.deleteALLCache.lock';
        if (file_exists($lockFile) && filectime($lockFile) > strtotime('-5 minutes')) {
            _error_log('clearCache is in progress ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
            return false;
        }
        $start = microtime(true);
        _error_log('deleteALLCache starts ');
        global $__getAVideoCache;
        unset($__getAVideoCache);
        //_error_log('deleteALLCache: '.json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $tmpDir = self::getCacheDir('', false);

        $newtmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . uniqid();
        _error_log("deleteALLCache rename($tmpDir, $newtmpDir) ");
        @rename($tmpDir, $newtmpDir);
        if (is_dir($tmpDir)) {
            _error_log('deleteALLCache 1 rmdir ' . $tmpDir);
            @rrmdir($tmpDir);
        } elseif (preg_match('/videos.cache/', $newtmpDir)) {
            // only delete if it is on the videos dir. otherwise it is on the /tmp dit and the system will delete it
            _error_log('deleteALLCache 2 rmdir ' . $newtmpDir);
            rrmdirCommandLine($newtmpDir, true);
        }
        self::setLastDeleteALLCacheTime();
        @unlink($lockFile);
        $end = microtime(true) - $start;
        _error_log("deleteALLCache end in {$end} seconds");
        return true;
    }

    private static function isToSaveInASubDir($filename) {
        return str_starts_with($filename, '/') || str_ends_with($filename, '/');
    }

    public static function getCacheDir($filename = '', $createDir = true, $addSubDirs = true, $ignoreMetadata = false) {
        global $_getCacheDir, $global;

        if (!isset($_getCacheDir)) {
            $_getCacheDir = [];
        }

        if (!empty($_getCacheDir[$filename])) {
            return $_getCacheDir[$filename];
        }

        $tmpDir = getTmpDir();
        $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $tmpDir .= "YPTObjectCache" . DIRECTORY_SEPARATOR;
        if (self::isToSaveInASubDir($filename)) {
            $addSubDirs = false;
            $filename = trim($filename, '/');
        }
        $filename = self::cleanCacheName($filename);
        if (!empty($filename)) {
            $tmpDir .= $filename . DIRECTORY_SEPARATOR;
            if ($addSubDirs) {
                $domain = getDomain();
                // make sure you separete http and https cache
                $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                $tmpDir .= "{$protocol}_{$domain}" . DIRECTORY_SEPARATOR;
                if (class_exists("User_Location")) {
                    $loc = User_Location::getThisUserLocation();
                    if (!empty($loc) && !empty($loc['country_code']) && $loc['country_code'] !== '-') {
                        $tmpDir .= $loc['country_code'] . DIRECTORY_SEPARATOR;
                    }
                }
                if (empty($ignoreMetadata)) {

                    if (User::isLogged()) {
                        if (User::isAdmin()) {
                            $tmpDir .= 'admin_' . md5("admin" . $global['salt']) . DIRECTORY_SEPARATOR;
                        } else {
                            $tmpDir .= 'user_' . md5("user" . $global['salt']) . DIRECTORY_SEPARATOR;
                        }
                    } else {
                        $tmpDir .= 'notlogged_' . md5("notlogged" . $global['salt']) . DIRECTORY_SEPARATOR;
                    }
                }
            }
        }
        $tmpDir = fixPath($tmpDir);
        if ($createDir) {
            make_path($tmpDir);
        }
        if (!file_exists($tmpDir . "index.html") && is_writable($tmpDir)) { // to avoid search into the directory
            _file_put_contents($tmpDir . "index.html", time());
        }

        $_getCacheDir[$filename] = $tmpDir;
        return $tmpDir;
    }

    public static function getCacheFileName($name, $createDir = true, $addSubDirs = true, $ignoreMetadata = false) {
        global $global;
        $tmpDir = self::getCacheDir($name, $createDir, $addSubDirs, $ignoreMetadata);
        $uniqueHash = sha1($name . $global['salt']); // add salt for security reasons 
        return $tmpDir . $uniqueHash . '.cache';
    }

    public static function deleteCacheFromPattern($name) {
        if (empty($name)) {
            return false;
        }
        $tmpDir = getTmpDir();
        //_error_log('deleteCacheFromPattern: '.json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $name = self::cleanCacheName($name);
        $ignoreLocationDirectoryName = (strpos($name, DIRECTORY_SEPARATOR) !== false);
        $filePattern = $tmpDir . DIRECTORY_SEPARATOR . $name;
        foreach (glob("{$filePattern}*") as $filename) {
            unlink($filename);
        }
        self::deleteSessionCache($name);
    }

    /**
     * Make sure you start the session before any output
     * @param string $name
     * @param string $value
     */
    public static function setSessionCache($name, $value) {
        $name = self::cleanCacheName($name);
        _session_start();
        $_SESSION['user']['sessionCache'][$name]['value'] = json_encode($value);
        $_SESSION['user']['sessionCache'][$name]['time'] = time();
        if (empty($_SESSION['user']['sessionCache']['time'])) {
            $_SESSION['user']['sessionCache']['time'] = time();
        }
    }

    /**
     *
     * @param string $name
     * @param string $lifetime, if is = 0 it is unlimited
     * @return string
     */
    public static function getSessionCache($name, $lifetime = 60) {
        $name = self::cleanCacheName($name);
        if (!empty($_GET['lifetime'])) {
            $lifetime = intval($_GET['lifetime']);
        }
        if (!empty($_SESSION['user']['sessionCache'][$name])) {
            if ((empty($lifetime) || time() - $lifetime <= $_SESSION['user']['sessionCache'][$name]['time'])) {
                $c = $_SESSION['user']['sessionCache'][$name]['value'];
                self::setLastUsedCacheMode("Session cache \$_SESSION['user']['sessionCache'][$name]");
                $json = _json_decode($c);
                if (is_string($json) && strtolower($json) === 'false') {
                    $json = false;
                }
                return $json;
            }
            _session_start();
            unset($_SESSION['user']['sessionCache'][$name]);
        }
        return null;
    }

    public static function clearSessionCache() {
        unset($_SESSION['user']['sessionCache']);
    }

    private static function getLastDeleteALLCacheTimeFile() {
        $tmpDir = getTmpDir();
        $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . "/";
        $tmpDir .= "lastDeleteALLCacheTime.cache";
        return $tmpDir;
    }

    public static function setLastDeleteALLCacheTime() {
        $file = self::getLastDeleteALLCacheTimeFile();
        //_error_log("ObjectYPT::setLastDeleteALLCacheTime {$file}");
        return @file_put_contents($file, time());
    }

    public static function getLastDeleteALLCacheTime() {
        global $getLastDeleteALLCacheTime;
        if (empty($getLastDeleteALLCacheTime)) {
            $getLastDeleteALLCacheTime = (int) @file_get_contents(self::getLastDeleteALLCacheTimeFile(), time());
        }
        return $getLastDeleteALLCacheTime;
    }

    public static function checkSessionCacheBasedOnLastDeleteALLCacheTime() {
        /*
          var_dump(
          $session_var['time'],
          self::getLastDeleteALLCacheTime(),
          humanTiming($session_var['time']),
          humanTiming(self::getLastDeleteALLCacheTime()),
          $session_var['time'] <= self::getLastDeleteALLCacheTime());
         *
         */
        if (empty($_SESSION['user']['sessionCache']['time']) || $_SESSION['user']['sessionCache']['time'] <= self::getLastDeleteALLCacheTime()) {
            self::deleteAllSessionCache();
            return false;
        }
        return true;
    }

    public static function deleteSessionCache($name) {
        $name = self::cleanCacheName($name);
        _session_start();
        $_SESSION['user']['sessionCache'][$name] = null;
        unset($_SESSION['user']['sessionCache'][$name]);
    }

    public static function deleteAllSessionCache() {
        _session_start();
        unset($_SESSION['user']['sessionCache']);
        return empty($_SESSION['user']['sessionCache']);
    }

    public function tableExists() {
        return self::isTableInstalled();
    }

    public static function isTableInstalled($tableName = "") {
        global $global, $tableExists;
        if (empty($tableName)) {
            $tableName = static::getTableName();
        }
        if (empty($tableName)) {
            return false;
        }
        if (!isset($tableExists[$tableName])) {
            $sql = "SHOW TABLES LIKE '" . $tableName . "'";
            //_error_log("isTableInstalled: ({$sql})");
            $res = sqlDAL::readSql($sql);
            $result = sqlDal::num_rows($res);
            sqlDAL::close($res);
            $tableExists[$tableName] = !empty($result);
        }
        return $tableExists[$tableName];
    }

    public static function clientTimezoneToDatabaseTimezone($clientDate) {
        if (!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $clientDate)) {
            return $clientDate;
        }

        global $timezoneOriginal;
        $currentTimezone = date_default_timezone_get();
        $time = strtotime($clientDate);
        date_default_timezone_set($timezoneOriginal);

        $dbDate = date('Y-m-d H:i:s', $time);

        date_default_timezone_set($currentTimezone);
        return $dbDate;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
    }

    public static function __set_state($state) {
        $obj = new self();
        $obj->properties = $state['properties'];
        return $obj;
    }

}

//abstract class Object extends ObjectYPT{};
