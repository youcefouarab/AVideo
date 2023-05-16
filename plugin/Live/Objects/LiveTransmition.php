<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class LiveTransmition extends ObjectYPT {

    protected $properties = [];
    protected $id;
    protected $title;
    protected $public;
    protected $saveTransmition;
    protected $users_id;
    protected $categories_id;
    protected $key;
    protected $description;
    protected $showOnTV;
    protected $password;

    public static function getSearchFieldsNames() {
        return ['title'];
    }

    public static function getTableName() {
        return 'live_transmitions';
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getPublic() {
        return $this->public;
    }

    public function getSaveTransmition() {
        return $this->saveTransmition;
    }

    public function getUsers_id() {
        return $this->users_id;
    }

    public function getCategories_id() {
        return $this->categories_id;
    }

    /**
     * 
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTitle($title) {
        global $global;
        //$title = ($title);
        $this->title = xss_esc($title);
    }

    public function setPublic($public) {
        $this->public = intval($public);
    }

    public function setSaveTransmition($saveTransmition) {
        $this->saveTransmition = $saveTransmition;
    }

    public function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }

    public function setCategories_id($categories_id) {
        $this->categories_id = $categories_id;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function setDescription($description) {
        global $global;
        //$description = ($description);
        $this->description = xss_esc($description);
    }

    public function loadByUser($user_id) {
        $user = self::getFromDbByUser($user_id);
        if (empty($user)) {
            return false;
        }
        foreach ($user as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public function loadByKey($uuid) {
        $row = self::getFromKey($uuid);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public static function getFromDbByUser($user_id, $refreshCache = false) {
        global $global;
        if (!self::isTableInstalled(static::getTableName())) {
            _error_log("Save error, table " . static::getTableName() . " does not exists", AVideoLog::$ERROR);
            return false;
        }
        $user_id = intval($user_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  users_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$user_id], $refreshCache);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($data)) {
            $data['live_servers_id'] = Live::getLiveServersIdRequest();
            $liveStreamObject = new LiveStreamObject($data['key'], $data['live_servers_id']);
            $data['key_with_index'] = $liveStreamObject->getKeyWithIndex(true);
            $data['live_index'] = $liveStreamObject->getIndex();

            $user = $data;
        } else {
            $user = false;
        }
        return $user;
    }

    public static function createTransmitionIfNeed($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $row = static::getFromDbByUser($user_id);
        if ($row && !empty($row['key'])) {
            $row['just_created'] = false;
        } else {
            $l = new LiveTransmition(0);
            $l->setTitle("I am Live");
            $l->setDescription("");
            $l->setKey(uniqid());
            $l->setCategories_id(1);
            $l->setUsers_id($user_id);
            $l->save();
            $row = static::getFromDbByUser($user_id);
            $row['just_created'] = true;
        }
        return $row;
    }

    public static function resetTransmitionKey($user_id) {
        $row = static::getFromDbByUser($user_id);

        $l = new LiveTransmition($row['id']);
        $newKey = uniqid();
        $l->setKey($newKey);
        if ($l->save()) {
            return $newKey;
        } else {
            return false;
        }
    }

    public static function getFromRequest() {
        if (!empty($_REQUEST['live_transmitions_id'])) {
            return LiveTransmition::getFromDb($_REQUEST['live_transmitions_id']);
        } else if (!empty($_REQUEST['live_schedule'])) {
            return LiveTransmition::getFromDbBySchedule($_REQUEST['live_schedule']);
        } elseif (!empty($_REQUEST['u'])) {
            return LiveTransmition::getFromDbByUserName($_REQUEST['u']);
        } elseif (!empty($_REQUEST['c'])) {
            return LiveTransmition::getFromDbByChannelName($_REQUEST['c']);
        }
        return false;
    }

    public static function getFromDbByUserName($userName) {
        global $global;
        _mysql_connect();
        $sql = "SELECT * FROM users WHERE user = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$userName]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $user = $data;
            if (empty($user)) {
                return false;
            }
            return static::getFromDbByUser($user['id']);
        } else {
            return false;
        }
    }

    public static function getFromDbByChannelName($channelName) {
        global $global;
        _mysql_connect();
        $sql = "SELECT * FROM users WHERE channelName = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$channelName]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $user = $data;
            if (empty($user)) {
                return false;
            }
            return static::getFromDbByUser($user['id']);
        } else {
            return false;
        }
    }

    public static function getFromDbBySchedule($live_schedule_id) {
        global $global;
        $live_schedule_id = intval($live_schedule_id);
        $sql = "SELECT lt.*, ls.* FROM live_schedule ls "
                . " LEFT JOIN " . static::getTableName() . " lt ON lt.users_id = ls.users_id "
                . " WHERE ls.id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$live_schedule_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $user = $data;
            $user['live_schedule'] = $live_schedule_id;
        } else {
            $user = false;
        }
        return $user;
    }

    public static function keyExists($key, $checkSchedule = true) {
        global $global;
        if (!is_string($key)) {
            return false;
        }
        if (Live::isAdaptiveTransmition($key)) {
            return false;
        }
        $key = Live::cleanUpKey($key);
        $sql = "SELECT u.*, lt.*, lt.password as live_password FROM " . static::getTableName() . " lt "
                . " LEFT JOIN users u ON u.id = users_id AND u.status='a' "
                . " WHERE  `key` = '$key' LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
            if(!empty($row)) {

                $row['live_schedule_id'] = 0;
                $row['scheduled'] = 0;
                $p = $row['live_password'];
                $row = cleanUpRowFromDatabase($row);
                $row['live_password'] = $p;

                if (empty($row['users_id'])) {
                    $row['users_id'] = 0;
                }
                if (!isset($row['live_servers_id'])) {
                    $row['live_servers_id'] = Live::getLiveServersIdRequest();
                }
            }
        } else {
            $row = false;
        }

        if ($checkSchedule && empty($row)) {
            $row = Live_schedule::keyExists($key);
            if (!empty($row)) {
                $row['scheduled'] = 1;
                $row['live_schedule_id'] = $row['id'];
            }
        }

        return $row;
    }

    public function save() {
        if (empty($this->users_id)) {
            return false;
        }
        $row = self::getFromDbByUser($this->users_id, true);
        if (!empty($row)) {
            $this->id = $row['id'];
        }

        $this->public = intval($this->public);
        $this->saveTransmition = intval($this->saveTransmition);
        $this->showOnTV = intval($this->showOnTV);
        if (empty($this->password)) {
            $this->password = '';
        }
        $id = parent::save();
        Category::clearCacheCount();
        deleteStatsNotifications(true);

        $socketObj = sendSocketMessageToAll(['stats' => getStatsNotifications(false, false)], "socketLiveONCallback");

        return $id;
    }

    public function deleteGroupsTrasmition() {
        if (empty($this->id)) {
            return false;
        }
        global $global;
        $sql = "DELETE FROM live_transmitions_has_users_groups WHERE live_transmitions_id = ?";
        return sqlDAL::writeSql($sql, "i", [$this->id]);
    }

    public function insertGroup($users_groups_id) {
        global $global;
        $sql = "INSERT INTO live_transmitions_has_users_groups (live_transmitions_id, users_groups_id) VALUES (?,?)";
        return sqlDAL::writeSql($sql, "ii", [$this->id, $users_groups_id]);
    }

    public function isAPrivateLive() {
        return !empty($this->getGroups());
    }

    public function getGroups() {
        $rows = [];
        if (empty($this->id)) {
            return $rows;
        }
        global $global;
        $sql = "SELECT * FROM live_transmitions_has_users_groups WHERE live_transmitions_id = ?";
        $res = sqlDAL::readSql($sql, "i", [$this->id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row["users_groups_id"];
            }
        }
        return $rows;
    }

    public function userCanSeeTransmition() {
        global $global;
        require_once $global['systemRootPath'] . 'objects/userGroups.php';
        require_once $global['systemRootPath'] . 'objects/user.php';
        if (User::isAdmin()) {
            return true;
        }
        /*
          $password = $this->getPassword();
          if(!empty($password) && !Live::passwordIsGood($this->getKey())){
          return false;
          }
         *
         */

        $transmitionGroups = $this->getGroups();
        if (!empty($transmitionGroups)) {
            if (empty($this->id)) {
                return false;
            }
            if (!User::isLogged()) {
                return false;
            }
            $userGroups = UserGroups::getUserGroups(User::getId());
            if (empty($userGroups)) {
                return false;
            }
            foreach ($userGroups as $ugvalue) {
                foreach ($transmitionGroups as $tgvalue) {
                    if ($ugvalue['id'] == $tgvalue) {
                        return true;
                    }
                }
            }
            return false;
        } else {
            return true;
        }
    }

    public static function getFromKey($key, $checkSchedule = true) {
        return self::keyExists($key, $checkSchedule);
    }

    public static function keyNameFix($key) {
        $key = str_replace('/', '', $key);
        if (!empty($_REQUEST['live_index']) && !preg_match("/.*-([0-9a-zA-Z]+)/", $key)) {
            if (!empty($_REQUEST['live_index']) && $_REQUEST['live_index'] !== 'false') {
                $key .= "-{$_REQUEST['live_index']}";
            }
        }
        if (!empty($_REQUEST['playlists_id_live']) && !preg_match("/.*_([0-9]+)/", $key)) {
            $key .= "_{$_REQUEST['playlists_id_live']}";
        }
        return $key;
    }

    public function getShowOnTV() {
        return $this->showOnTV;
    }

    public function setShowOnTV($showOnTV) {
        $this->showOnTV = $showOnTV;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password): void {
        $this->password = trim($password);
    }

    public static function canSaveTransmition($users_id) {
        $lt = self::getFromDbByUser($users_id);
        return !empty($lt['saveTransmition']);
    }

    static function getUsers_idOrCompanyFromKey($key) {

        $row = self::getFromKey($key);
        if (!empty($row['users_id_company'])) {
            return $row['users_id_company'];
        }

        return $row['users_id'];
    }

}
