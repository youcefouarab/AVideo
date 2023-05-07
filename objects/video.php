<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';
global $global, $config, $videosPaths;

if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/userGroups.php';
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/include_config.php';
require_once $global['systemRootPath'] . 'objects/video_statistic.php';
require_once $global['systemRootPath'] . 'objects/sites.php';
require_once $global['systemRootPath'] . 'objects/Object.php';

if (!class_exists('Video')) {

    class Video extends ObjectYPT
    {

        protected $properties = [];
        protected $id;
        protected $title;
        protected $clean_title;
        protected $filename;
        protected $description;
        protected $views_count;
        protected $order;
        protected $status;
        protected $duration;
        protected $users_id;
        protected $categories_id;
        protected $old_categories_id;
        protected $type;
        protected $rotation;
        protected $zoom;
        protected $videoDownloadedLink;
        protected $videoLink;
        protected $next_videos_id;
        protected $isSuggested;
        public static $types = ['webm', 'mp4', 'mp3', 'ogg', 'pdf', 'jpg', 'jpeg', 'gif', 'png', 'webp', 'zip'];
        protected $videoGroups;
        protected $trailer1;
        protected $trailer2;
        protected $trailer3;
        protected $rate;
        protected $can_download;
        protected $can_share;
        protected $only_for_paid;
        protected $rrating;
        protected $externalOptions;
        protected $sites_id;
        protected $serie_playlists_id;
        protected $video_password;
        protected $encoderURL;
        protected $filepath;
        protected $filesize;
        protected $live_transmitions_history_id;
        protected $total_seconds_watching;
        protected $duration_in_seconds;
        protected $likes;
        protected $dislikes;
        protected $users_id_company;
        protected $created;
        protected $epg_link;
        protected $publish_datetime;
        protected $notification_datetime;
        public static $statusDesc = [
            'a' => 'Active',
            'k' => 'Active and Encoding',
            'i' => 'Inactive',
            'h' => 'Scheduled Release Date',
            'e' => 'Encoding',
            'x' => 'Encoding Error',
            'd' => 'Downloading',
            't' => 'Transferring',
            'u' => 'Unlisted',
            's' => 'Unlisted but Searchable',
            'r' => 'Recording',
            'f' => 'FansOnly',
            'b' => 'Broken Missing files'
        ];
        public static $statusIcons = [
            'a' => '<i class=\'fas fa-eye\'></i>',
            'k' => '<i class=\'fas fa-cog\'></i>',
            'i' => '<i class=\'fas fa-eye-slash\'></i>',
            'h' => '<i class=\'fas fa-clock\'></i>',
            'e' => '<i class=\'fas fa-cog\'></i>',
            'x' => '<i class=\'fas fa-exclamation-triangle\'></i>',
            'd' => '<i class=\'fas fa-download\'></i>',
            't' => '<i class=\'fas fa-sync\'></i>',
            'u' => '<i class=\'fas fa-eye\' style=\'color: #BBB;\'></i>',
            's' => '<i class=\'fas fa-search\' style=\'color: #BBB;\'></i>',
            'r' => '<i class=\'fas fa-circle\'></i>',
            'f' => '<i class=\'fas fa-star\'></i>',
            'b' => '<i class=\'fas fa-times\'></i>'
        ];
        public static $statusActive = 'a';
        public static $statusActiveAndEncoding = 'k';
        public static $statusInactive = 'i';
        public static $statusScheduledReleaseDate = 'h';
        public static $statusEncoding = 'e';
        public static $statusEncodingError = 'x';
        public static $statusDownloading = 'd';
        public static $statusTranfering = 't';
        public static $statusUnlisted = 'u';
        public static $statusUnlistedButSearchable = 's';
        public static $statusRecording = 'r';
        public static $statusFansOnly = 'f';
        public static $statusBrokenMissingFiles = 'b';
        public static $rratingOptions = ['', 'g', 'pg', 'pg-13', 'r', 'nc-17', 'ma'];
        public static $rratingOptionsText = ['g' => 'General Audience', 'pg' => 'Parental Guidance Suggested', 'pg-13' => 'Parental Strongly Cautioned', 'r' => 'Restricted', 'nc-17' => 'No One 17 and Under Admitted', 'ma' => 'Mature Audience'];
        //ver 3.4
        protected $youtubeId;
        public static $typeOptions = ['audio', 'video', 'embed', 'linkVideo', 'linkAudio', 'torrent', 'pdf', 'image', 'gallery', 'article', 'serie', 'image', 'zip', 'notfound', 'blockedUser'];
        public static $searchFieldsNames = ['v.title', 'v.description', 'c.name', 'c.description', 'v.id', 'v.filename'];
        public static $searchFieldsNamesLabels = ['Video Title', 'Video Description', 'Channel Name', 'Channel Description', 'Video ID', 'Video Filename'];
        public static $iframeAllowAttributes = 'allow="fullscreen;autoplay;camera *;microphone *;" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen"';

        public function __construct($title = "", $filename = "", $id = 0, $refreshCache = false)
        {
            global $global;
            $this->rotation = 0;
            $this->zoom = 1;
            if (!empty($id)) {
                $this->load($id, $refreshCache);
            }
            if (!empty($title)) {
                $this->setTitle($title);
            }
            if (!empty($filename)) {
                $this->filename = safeString($filename, true);
            }
        }

        public function getPublish_datetime()
        {
            return $this->publish_datetime;
        }

        public function getNotification_datetime()
        {
            return $this->notification_datetime;
        }

        public function setPublish_datetime($publish_datetime): void
        {
            $this->publish_datetime = $publish_datetime;
        }

        public function setNotification_datetime($notification_datetime): void
        {
            $this->notification_datetime = $notification_datetime;
        }

        public function getCreated()
        {
            return $this->created;
        }

        public function setCreated($created): void
        {
            $this->created = $created;
        }

        function getUsers_id_company(): int
        {
            return intval($this->users_id_company);
        }

        function setUsers_id_company($users_id_company): void
        {
            $this->users_id_company = intval($users_id_company);
        }

        public function addView($currentTime = 0)
        {
            if (isBot()) {
                //_error_log("addView isBot");
                return false;
            }
            global $global;
            if (empty($this->id)) {
                //_error_log("addView empty(\$this->id))");
                return false;
            }

            $lastStatistic = VideoStatistic::getLastStatistics($this->id, User::getId());
            if (!empty($lastStatistic)) {
                //_error_log("addView !empty(\$lastStatistic) ");
                return false;
            }

            $sql = "UPDATE videos SET views_count = views_count+1, modified = now() WHERE id = ?";

            $insert_row = sqlDAL::writeSql($sql, "i", [$this->id]);

            if ($insert_row) {
                $obj = new stdClass();
                $obj->videos_statistics_id = VideoStatistic::create($this->id, $currentTime);
                $obj->videos_id = $this->id;
                $this->views_count++;
                AVideoPlugin::addView($this->id, $this->views_count);
                return $obj;
            }
            //die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            return false;
        }

        public function addSecondsWatching($seconds_watching)
        {
            global $global;

            $seconds_watching = intval($seconds_watching);

            if (empty($seconds_watching)) {
                //_error_log("addSecondsWatching: seconds_watching is empty");
                return false;
            }

            if (empty($this->id)) {
                //_error_log("addSecondsWatching: ID is empty ");
                return false;
            }

            $newTotal = intval($this->total_seconds_watching) + $seconds_watching;

            $sql = "UPDATE videos SET total_seconds_watching = ?, modified = now() WHERE id = ?";
            //_error_log("addSecondsWatching: " . $sql . "={$this->id}");
            try {
                return sqlDAL::writeSql($sql, "ii", [intval($newTotal), intval($this->id)]);
            } catch (Exception $exc) {

                _error_log("UPDATE videos SET total_seconds_watching = ?, modified = now() WHERE id = ? " . json_encode([intval($newTotal), intval($this->id)]));
            }
        }

        public function updateViewsCount($total)
        {
            global $global;
            if (empty($this->id)) {
                return false;
            }
            $total = intval($total);
            if ($total < 0) {
                return false;
            }
            $sql = "UPDATE videos SET views_count = {$total}, modified = now() WHERE id = ?";

            $insert_row = sqlDAL::writeSql($sql, "i", [$this->id]);

            if ($insert_row) {
                return $insert_row;
            }
            //die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            return false;
        }

        public function addViewPercent($percent = 25)
        {
            if (isBot()) {
                return false;
            }
            global $global;
            if (empty($this->id)) {
                return false;
            }
            $sql = "UPDATE videos SET views_count_{$percent} = IFNULL(views_count_{$percent}, 0)+1, modified = now() WHERE id = ?";

            $insert_row = sqlDAL::writeSql($sql, "i", [$this->id]);

            if ($insert_row) {
                return true;
            }
            //die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            return false;
        }

        // allow users to count a view again in case it is refreshed
        public static function unsetAddView($videos_id)
        {
            // allow users to count a view again in case it is refreshed
            if (!empty($_SESSION['addViewCount'][$videos_id]['time']) && $_SESSION['addViewCount'][$videos_id]['time'] <= time()) {
                _session_start();
                unset($_SESSION['addViewCount'][$videos_id]);
            }
        }

        public function load($id, $refreshCache = false)
        {
            $video = self::getVideoLight($id, $refreshCache);
            if (empty($video)) {
                return false;
            }
            foreach ($video as $key => $value) {
                @$this->$key = $value;
                //$this->properties[$key] = $value;
            }
        }

        public function getLive_transmitions_history_id()
        {
            return $this->live_transmitions_history_id;
        }

        public function setLive_transmitions_history_id($live_transmitions_history_id)
        {
            AVideoPlugin::onVideoSetLive_transmitions_history_id($this->id, $this->live_transmitions_history_id, intval($live_transmitions_history_id));
            $this->live_transmitions_history_id = intval($live_transmitions_history_id);
        }

        public function getEncoderURL()
        {
            return $this->encoderURL;
        }

        public function getFilepath()
        {
            return $this->filepath;
        }

        public function getFilesize()
        {
            return intval($this->filesize);
        }

        public function setEncoderURL($encoderURL)
        {
            if (filter_var($encoderURL, FILTER_VALIDATE_URL) !== false) {
                AVideoPlugin::onVideoSetEncoderURL($this->id, $this->encoderURL, $encoderURL);
                $this->encoderURL = $encoderURL;
            }
        }

        public function setFilepath($filepath)
        {
            AVideoPlugin::onVideoSetFilepath($this->id, $this->filepath, $filepath);
            $this->filepath = $filepath;
        }

        public function setFilesize($filesize)
        {
            AVideoPlugin::onVideoSetFilesize($this->id, $this->filesize, $filesize);
            $this->filesize = intval($filesize);
        }

        public function setUsers_id($users_id)
        {
            AVideoPlugin::onVideoSetUsers_id($this->id, $this->users_id, $users_id);
            $this->users_id = $users_id;
        }

        public function getSites_id()
        {
            return $this->sites_id;
        }

        public function setSites_id($sites_id)
        {
            AVideoPlugin::onVideoSetSites_id($this->id, $this->sites_id, $sites_id);
            $this->sites_id = $sites_id;
        }

        public function getVideo_password()
        {
            if (empty($this->video_password)) {
                return '';
            }
            return trim($this->video_password);
        }

        public function setVideo_password($video_password)
        {
            AVideoPlugin::onVideoSetVideo_password($this->id, $this->video_password, $video_password);
            $this->video_password = trim($video_password);
        }

        public function save($updateVideoGroups = false, $allowOfflineUser = false)
        {
            global $advancedCustom;
            global $global;
            if (!User::isLogged() && !$allowOfflineUser) {
                _error_log('Video::save permission denied to save');
                return false;
            }
            if (empty($this->title)) {
                $this->title = uniqid();
            }

            $this->clean_title = _substr(safeString($this->clean_title), 0, 187);

            if (empty($this->clean_title)) {
                $this->setClean_title($this->title);
            }
            $this->clean_title = self::fixCleanTitle($this->clean_title, 1, $this->id);

            if (empty($this->status) || empty(self::$statusDesc[$this->status])) {
                $this->status = Video::$statusEncoding;
            }

            if (empty($this->type) || !in_array($this->type, self::$typeOptions)) {
                $this->type = 'video';
            } else if (!empty($this->id) && $this->type == 'linkVideo') {
                // chek if it has no media
                $types = Video::getVideoTypeFromId($this->id);
                if (!empty($types)) {
                    if ($types->mp4 || $types->webm || $types->m3u8) {
                        $this->type = 'video';
                    } else
                    if ($types->pdf) {
                        $this->type = 'pdf';
                    } else
                    if ($types->mp3) {
                        $this->type = 'audio';
                    }
                }
            }
            //var_dump($this->id, $this->type);exit;
            if (empty($this->isSuggested)) {
                $this->isSuggested = 0;
            } else {
                $this->isSuggested = 1;
            }

            $this->views_count = intval($this->views_count);
            $this->order = intval($this->order);

            if (empty($this->categories_id)) {
                $p = AVideoPlugin::loadPluginIfEnabled("PredefinedCategory");
                $category = Category::getCategoryDefault();
                $categories_id = $category['id'];
                if (empty($categories_id)) {
                    $categories_id = 'NULL';
                }
                if ($p) {
                    $this->categories_id = $p->getCategoryId();
                } else {
                    $this->categories_id = $categories_id;
                }
                if (empty($this->categories_id)) {
                    $this->categories_id = $categories_id;
                }
            }
            // check if category exists
            $cat = new Category($this->categories_id);
            if (empty($cat->getName())) {
                $catDefault = Category::getCategoryDefault();
                $this->categories_id = $catDefault['id'];
            }
            //$this->setTitle((trim($this->title)));
            $this->title = ((safeString($this->title)));
            $this->description = (($this->description));

            if (empty($this->users_id)) {
                $this->users_id = User::getId();
            }

            $this->next_videos_id = intval($this->next_videos_id);
            if (empty($this->next_videos_id)) {
                $this->next_videos_id = 'NULL';
            }

            $this->sites_id = intval($this->sites_id);
            if (empty($this->sites_id)) {
                $this->sites_id = 'NULL';
            }

            $this->serie_playlists_id = intval($this->serie_playlists_id);
            if (empty($this->serie_playlists_id)) {
                $this->serie_playlists_id = 'NULL';
            }

            if (empty($this->filename)) {
                $prefix = $this->type;
                if (empty($prefix)) {
                    $prefix = 'v';
                }
                $paths = self::getNewVideoFilename($prefix);
                $this->filename = $paths['filename'];
            }

            $this->can_download = intval($this->can_download);
            $this->can_share = intval($this->can_share);
            $this->only_for_paid = intval($this->only_for_paid);
            $this->filesize = intval($this->filesize);

            $this->rate = floatval($this->rate);

            if (!filter_var($this->videoLink, FILTER_VALIDATE_URL)) {
                $this->videoLink = '';
                if ($this->type == 'embed') {
                    $this->type = 'video';
                }
            }

            if (empty($this->live_transmitions_history_id)) {
                $this->live_transmitions_history_id = 'NULL';
            }
            if (empty($this->publish_datetime)) {
                $this->publish_datetime = 'NULL';
            }
            if (empty($this->notification_datetime)) {
                $this->notification_datetime = 'NULL';
            }

            $this->duration = self::getCleanDuration($this->duration);

            if (empty($this->duration_in_seconds)) {
                $this->duration_in_seconds = durationToSeconds($this->duration);
            }

            if (!empty($this->id)) {
                if (!$this->userCanManageVideo() && !$allowOfflineUser && !Permissions::canModerateVideos()) {
                    forbiddenPage('Permission denied');
                }

                $insert_row = parent::save();
                if ($insert_row) {
                    AVideoPlugin::onUpdateVideo($insert_row);
                    //_error_log('onUpdateVideo $insert_row = ' . $insert_row);
                } else {
                    _error_log('onUpdateVideo error $saved is empty');
                }
            } else {
                $insert_row = parent::save();
                if (!empty($insert_row)) {
                    AVideoPlugin::onNewVideo($insert_row);
                    _error_log('onNewVideo $insert_row = ' . $insert_row);
                } else {
                    _error_log('onNewVideo error $insert_row is empty');
                }
            }
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            //var_dump($this->title, $insert_row);exit;
            if ($insert_row) {
                _error_log("Video::save ({$this->title}) Saved id = {$insert_row} {$this->duration} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
                Category::clearCacheCount();
                if (empty($this->id)) {
                    $this->id = $insert_row;

                    // check if needs to add the video in a user group
                    $p = AVideoPlugin::loadPluginIfEnabled("PredefinedCategory");
                    if ($p) {
                        $updateVideoGroups = true;
                        $this->videoGroups = $p->getUserGroupsArray();
                    }
                } else {
                    $id = $this->id;
                    if (empty($id)) {
                        $this->id = $id = $insert_row;
                    }
                }

                ObjectYPT::deleteCache("getItemprop{$this->id}");
                ObjectYPT::deleteCache("getLdJson{$this->id}");
                if (!class_exists('Cache')) {
                    AVideoPlugin::loadPlugin('Cache');
                }
                Cache::deleteCache("getVideoTags{$this->id}");
                self::deleteTagsAsync($this->id);
                if ($updateVideoGroups) {
                    require_once $global['systemRootPath'] . 'objects/userGroups.php';
                    // update the user groups
                    UserGroups::updateVideoGroups($this->id, $this->videoGroups);
                }

                // I am not sure what is it for
                //Video::autosetCategoryType($id);
                if (!empty($this->old_categories_id)) {
                    //Video::autosetCategoryType($this->old_categories_id);
                }
                self::clearCache($this->id);
                return $this->id;
            }
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            _error_log('Video::save Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            return false;
        }

        public static function updateDurationInSeconds($videos_id, $duration)
        {
            global $config, $global;

            if (!empty($global['ignoreUpdateDurationInSeconds'])) {
                _error_log('video:updateDurationInSeconds ignoreUpdateDurationInSeconds ' . json_encode(debug_backtrace()));
                return false;
            }

            $videos_id = intval($videos_id);
            if ($config->currentVersionLowerThen('11.4')) {
                return false;
            }
            if (empty($videos_id)) {
                return false;
            }
            $duration_in_seconds = durationToSeconds($duration);
            if (empty($duration_in_seconds)) {
                //_error_log("Video::updateDurationInSeconds empty duration {$videos_id}, {$duration}");
                return false;
            }
            _error_log("Video::updateDurationInSeconds update duration {$videos_id}, {$duration}, {$duration_in_seconds}");
            $formats = 'si';
            $values = [$duration_in_seconds, $videos_id];
            $sql = "UPDATE videos SET duration_in_seconds = ? , modified = now() WHERE id = ?";
            $saved = sqlDAL::writeSql($sql, $formats, $values);
            self::clearCache($videos_id);
            return $duration_in_seconds;
        }

        // i would like to simplify the big part of the method above in this method, but won't work as i want.
        public static function internalAutoset($catId, $videoFound, $audioFound)
        {
            global $config;
            if ($config->currentVersionLowerThen('5.01')) {
                return false;
            }
            $sql = "SELECT type,categories_id FROM `videos` WHERE categories_id = ?;";
            $res = sqlDAL::readSql($sql, "i", [$catId]);
            $fullResult2 = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            if ($res !== false) {
                foreach ($fullResult2 as $row) {
                    if ($row['type'] == "audio") {
                        $audioFound = true;
                    } elseif ($row['type'] == "video") {
                        $videoFound = true;
                    }
                }
            }
            if (($videoFound == false) || ($audioFound == false)) {
                $sql = "SELECT parentId,categories_id FROM `categories` WHERE parentId = ?;";
                $res = sqlDAL::readSql($sql, "i", [$catId]);
                $fullResult2 = sqlDAL::fetchAllAssoc($res);
                sqlDAL::close($res);
                if ($res !== false) {
                    foreach ($fullResult2 as $cat) {
                        $sql = "SELECT type,categories_id FROM `videos` WHERE categories_id = ?;";
                        $res = sqlDAL::readSql($sql, "i", [$cat['parentId']]);
                        $fullResult = sqlDAL::fetchAllAssoc($res);
                        sqlDAL::close($res);
                        if ($res !== false) {
                            foreach ($fullResult as $row) {
                                if ($row['type'] == 'audio') {
                                    $audioFound = true;
                                } elseif ($row['type'] == 'video') {
                                    $videoFound = true;
                                }
                            }
                        }
                    }
                }
            }
            return [$videoFound, $audioFound];
        }

        public function setClean_title($clean_title)
        {
            $clean_title = strip_tags($clean_title);
            if (preg_match("/video-automatically-booked/i", $clean_title) && !empty($this->clean_title)) {
                return false;
            }
            $clean_title = cleanURLName($clean_title);
            AVideoPlugin::onVideoSetClean_title($this->id, $this->clean_title, $clean_title);
            $this->clean_title = $clean_title;
        }

        public function setDuration($duration)
        {
            if (!self::isValidDuration($this->duration) || self::isValidDuration($duration)) {
                _error_log("setDuration before {$duration}");
                AVideoPlugin::onVideoSetDuration($this->id, $this->duration, $duration);
                _error_log("setDuration after {$duration}");
                $this->duration = $duration;
            } else {
                _error_log("setDuration error is not a valid {$duration}, old duration = {$this->duration}");
            }
        }

        static function isValidDuration($duration)
        {
            if (empty($duration) || strtolower($duration) == "ee:ee:ee" || $duration == '0:00:00' || $duration == '00:00:00' || $duration == "0:00:00.000000") {
                return false;
            }
            return preg_match('/^[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}/', $duration);
        }

        public function getDuration()
        {
            return $this->duration;
        }

        public function getIsSuggested()
        {
            return $this->isSuggested;
        }

        public function setIsSuggested($isSuggested)
        {
            if (empty($isSuggested) || $isSuggested === "false") {
                $new_isSuggested = 0;
            } else {
                $new_isSuggested = 1;
            }
            AVideoPlugin::onVideoSetIsSuggested($this->id, $this->isSuggested, $new_isSuggested);
            $this->isSuggested = $new_isSuggested;
        }

        public function setStatus($status)
        {
            if (!empty($this->id)) {
                global $global;

                if (empty(Video::$statusDesc[$status])) {
                    _error_log("Video::setStatus({$status}) NOT found ", AVideoLog::$WARNING);
                    return false;
                }
                /**
                 *
                 * @var array $global
                 * @var object $global['mysqli']
                 */
                _error_log("Video::setStatus  " . json_encode($_REQUEST));
                _error_log("Video::setStatus({$status}) " . json_encode(debug_backtrace()));
                $sql = "UPDATE videos SET status = ?, modified = now() WHERE id = ? ";
                $res = sqlDAL::writeSql($sql, 'si', [$status, $this->id]);
                if ($global['mysqli']->errno !== 0) {
                    die('Error on update Status: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                }
                self::clearCache($this->id);
                if ($this->status == Video::$statusActive || $status == Video::$statusActive && ($this->status != $status)) {

                    $doNotNotify = array(
                        Video::$statusInactive,
                        Video::$statusUnlisted,
                        Video::$statusUnlistedButSearchable,
                        Video::$statusFansOnly,
                        Video::$statusBrokenMissingFiles
                    );
                    if (!in_array($this->status, $doNotNotify) && $status == Video::$statusActive) {
                        _error_log("Video::setStatus({$status}) AVideoPlugin::onNewVideo ");
                        AVideoPlugin::onNewVideo($this->id);
                    } else {
                        _error_log("Video::setStatus({$status}) clearCache only ");
                    }
                    clearCache(true);
                } else {
                    _error_log("Video::setStatus({$status}) [{$this->status}] ");
                }
            }
            AVideoPlugin::onVideoSetStatus($this->id, $this->status, $status);
            $this->status = $status;
            return $status;
        }

        public function isScheduledForRelease()
        {
            $datetime = $this->getPublish_datetime();
            if (empty($datetime)) {
                return false;
            }
            return strtotime($datetime) > time();
        }

        public function setAutoStatus($default = 'a')
        {
            global $advancedCustom;
            if (empty($advancedCustom)) {
                $advancedCustom = AVideoPlugin::getDataObject('CustomizeAdvanced');
            }
            if (!empty($_POST['fail'])) {
                return $this->setStatus(Video::$statusEncodingError);
            } else {
                if ($this->isScheduledForRelease()) {
                    return $this->setStatus(Video::$statusScheduledReleaseDate);
                } else 
                if (!empty($_REQUEST['overrideStatus'])) {
                    return $this->setStatus($_REQUEST['overrideStatus']);
                } else if (!empty($_REQUEST['releaseDate']) && $_REQUEST['releaseDate'] !== 'now') {
                    return $this->setStatus(Video::$statusScheduledReleaseDate);
                } else { // encoder did not provide a status
                    AVideoPlugin::loadPlugin('Scheduler');
                    $row = Scheduler::isActiveFromVideosId($this->id);
                    if (!empty($row)) { // there is a schedule to activate the video
                        return $this->setStatus(Video::$statusScheduledReleaseDate);
                    } else {
                        if (!empty($_REQUEST['keepEncoding'])) {
                            return $this->setStatus(Video::$statusActiveAndEncoding);
                        } else {
                            if ($this->getTitle() !== "Video automatically booked") {
                                return $this->setStatus($advancedCustom->defaultVideoStatus->value);
                            } else {
                                return $this->setStatus(Video::$statusInactive);
                            }
                        }
                    }
                }
            }

            return $this->setStatus($default);
        }

        public function setType($type, $force = true)
        {
            if ($force || empty($this->type)) {
                AVideoPlugin::onVideoSetType($this->id, $this->type, $type, $force);
                $this->type = $type;
            }
        }

        public function setRotation($rotation)
        {
            $saneRotation = intval($rotation) % 360;
            AVideoPlugin::onVideoSetRotation($this->id, $this->rotation, $saneRotation);

            if (!empty($this->id)) {
                global $global;
                $sql = "UPDATE videos SET rotation = ?, modified = now() WHERE id = ? ";
                $formats = 'si';
                $values = [$saneRotation, $this->id];
                $res = sqlDAL::writeSql($sql, $formats, $values);

                /**
                 *
                 * @var array $global
                 * @var object $global['mysqli']
                 */
                if ($global['mysqli']->errno !== 0) {
                    die('Error on update Rotation: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                }
            }
            $this->rotation = $saneRotation;
        }

        public function getRotation()
        {
            return $this->rotation;
        }

        /**
         *
         * @return int
         */
        public function getUsers_id()
        {
            return $this->users_id;
        }

        public function setZoom($zoom)
        {
            $saneZoom = abs(floatval($zoom));

            if ($saneZoom < 0.1 || $saneZoom > 10) {
                die('Zoom level must be between 0.1 and 10');
            }

            if (!empty($this->id)) {
                global $global;
                $sql = "UPDATE videos SET zoom = ?, modified = now() WHERE id = ? ";
                $formats = 'si';
                $values = [$saneZoom, $this->id];
                $res = sqlDAL::writeSql($sql, $formats, $values);
                /**
                 *
                 * @var array $global
                 * @var object $global['mysqli']
                 */
                if ($global['mysqli']->errno !== 0) {
                    die('Error on update Zoom: (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                }
            }

            AVideoPlugin::onVideoSetZoom($this->id, $this->zoom, $saneZoom);
            $this->zoom = $saneZoom;
        }

        public function getZoom()
        {
            return $this->zoom;
        }

        public static function getUserGroupsCanSeeSQL($tableAlias = '')
        {
            global $global;

            if (Permissions::canModerateVideos()) {
                return "";
            }
            //$categories_id = intval($categories_id);
            if (self::allowFreePlayWithAdsIsEnabled()) {
                $sql = " AND {$tableAlias}only_for_paid = 0 ";
                return $sql;
            } else {
                $sql = " ((SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = {$tableAlias}id ) = 0 AND (SELECT count(id) FROM categories_has_users_groups as cug WHERE cug.categories_id = {$tableAlias}categories_id ) = 0) ";
                if (User::isLogged()) {
                    require_once $global['systemRootPath'] . 'objects/userGroups.php';
                    $userGroups = UserGroups::getUserGroups(User::getId());
                    $groups_id = [];
                    foreach ($userGroups as $value) {
                        $groups_id[] = $value['id'];
                    }
                    if (!empty($groups_id)) {
                        $sql = " (({$sql}) ";
                        $sql .= " OR ((SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = {$tableAlias}id AND users_groups_id IN ('" . implode("','", $groups_id) . "') ) > 0)";
                        $sql .= " OR ((SELECT count(id) FROM categories_has_users_groups as chug WHERE chug.categories_id = {$tableAlias}categories_id AND users_groups_id IN ('" . implode("','", $groups_id) . "') ) > 0)";
                        $sql .= " ) ";
                    }
                }
                return " AND " . $sql;
            }
        }

        public static function allowFreePlayWithAdsIsEnabled()
        {
            $obj = AVideoPlugin::getDataObjectIfEnabled('Subscription');
            if ($obj && $obj->allowFreePlayWithAds) {
                return true;
            }
            $obj = AVideoPlugin::getDataObjectIfEnabled('PayPerView');
            if ($obj && $obj->allowFreePlayWithAds) {
                return true;
            }
            $obj = AVideoPlugin::getDataObjectIfEnabled('FansSubscriptions');
            if ($obj && $obj->allowFreePlayWithAds) {
                return true;
            }
            return false;
        }

        public static function getUserGroups($videos_id)
        {
            return UserGroups::getVideosAndCategoriesUserGroups($videos_id);
        }

        public static function getVideo($id = "", $status = "viewable", $ignoreGroup = false, $random = false, $suggestedOnly = false, $showUnlisted = false, $ignoreTags = false, $activeUsersOnly = true)
        {
            global $global, $config, $advancedCustom, $advancedCustomUser, $lastGetVideoSQL;
            if ($config->currentVersionLowerThen('5')) {
                return false;
            }
            $status = str_replace("'", "", $status);
            if ($status === 'suggested') {
                $suggestedOnly = true;
                $status = '';
            }
            $id = intval($id);
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                }
            }
            _mysql_connect();
            $sql = "SELECT STRAIGHT_JOIN  u.*, u.externalOptions as userExternalOptions, v.*, "
                . " nv.title as next_title,"
                . " nv.clean_title as next_clean_title,"
                . " nv.filename as next_filename,"
                . " nv.id as next_id,"
                . " c.id as category_id,c.iconClass,c.name as category,c.iconClass,  c.clean_name as clean_category,c.description as category_description, v.created as videoCreation "
                //. ", (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes "
                //. ", (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = -1 ) as dislikes "
            ;
            if (User::isLogged()) {
                $sql .= ", (SELECT `like` FROM likes as l where l.videos_id = v.id AND users_id = '" . User::getId() . "' ) as myVote ";
            } else {
                $sql .= ", 0 as myVote ";
            }
            $sql .= " FROM videos as v "
                . "LEFT JOIN categories c ON categories_id = c.id "
                . "LEFT JOIN users u ON v.users_id = u.id "
                . "LEFT JOIN videos nv ON v.next_videos_id = nv.id "
                . " WHERE 1=1 ";
            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }

            if (!empty($id)) {
                $sql .= " AND v.id = '$id' ";
            }
            $sql .= AVideoPlugin::getVideoWhereClause();
            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL('v.');
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video' || $_SESSION['type'] == 'linkVideo') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } elseif ($_SESSION['type'] == 'audio') {
                    $sql .= " AND (v.type = 'audio' OR  v.type = 'linkAudio')";
                } else {
                    $sql .= " AND v.type = '{$_SESSION['type']}' ";
                }
            } else if (!empty($_REQUEST['videoType']) && $_REQUEST['videoType'] == 'audio_and_video') {
                $sql .= " AND (v.type = 'audio' OR v.type = 'video') ";
            } else if (!empty($_REQUEST['videoType']) && $_REQUEST['videoType'] == 'audio_and_video_and_serie') {
                $sql .= " AND (v.type = 'audio' OR v.type = 'video' OR v.type = 'serie') ";
            } else if (!empty($_REQUEST['videoType']) && in_array($_REQUEST['videoType'], self::$typeOptions)) {
                $sql .= " AND v.type = '{$_REQUEST['videoType']}' ";
            }

            if (!empty($videosArrayId) && is_array($videosArrayId)) {
                $sql .= " AND v.id IN ( '" . implode("', '", $videosArrayId) . "') ";
            }
            if ($status == "viewable") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
            } elseif ($status == "viewableNotUnlisted") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }

            if (!empty($_REQUEST['catName'])) {
                $catName = ($_REQUEST['catName']);
                $sql .= " AND (c.clean_name = '{$catName}' ";
                if (empty($_REQUEST['doNotShowCatChilds'])) {
                    $sql .= " OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$catName}' )";
                }
                $sql .= " )";
            }

            if (empty($id) && !empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                if (!empty($user['id'])) {
                    $sql .= " AND (v.users_id = '{$user['id']}' OR  v.users_id_company = '{$user['id']}' ) ";
                }
            }

            if (!empty($_GET['search'])) {
                $_POST['searchPhrase'] = $_GET['search'];
            }

            if (!empty($_POST['searchPhrase'])) {
                $_POST['searchPhrase'] = mb_strtolower(str_replace('&quot;', '"', $_POST['searchPhrase']));
                $searchFieldsNames = self::getSearchFieldsNames();
                if (AVideoPlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name "
                        . "LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost($searchFieldsNames, "OR");
                    $searchFieldsNames = ['v.title'];
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']);
                    $sql .= ")";
                } else {
                    $sql .= ' AND (1=1 ' . BootGrid::getSqlSearchFromPost($searchFieldsNames);
                    $searchFieldsNames = ['v.title'];
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']) . ')';
                }
            }
            if (!$ignoreGroup) {
                $arrayNotIN = AVideoPlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
            }
            if (!empty($_GET['created'])) {
                $_GET['created'] = preg_replace('/[^0-9: -]/', '', $_GET['created']);
                if(is_numeric($_GET['created']) && $_GET['created'] > 0){
                    $_GET['created'] = intval($_GET['created']);
                    $sql .= " AND v.created >= DATE_SUB(CURDATE(), INTERVAL {$_GET['created']} DAY)";
                }else{
                    $sql .= " AND v.created >= '{$_GET['created']}'";
                }
            }

            if (!empty($_REQUEST['minViews'])) {
                $minViews = intval($_REQUEST['minViews']);
                $sql .= " AND v.views_count >= '{$minViews}'";
            }

            // replace random based on this
            $firstClauseLimit = '';
            if (empty($id)) {
                if ($suggestedOnly) {
                    $sql .= " AND v.isSuggested = 1 AND v.status = '" . self::$statusActive . "' ";
                }
                if (empty($random) && !empty($_GET['videoName'])) {
                    $videoName = addcslashes($_GET['videoName'], "'");
                    $sql .= " AND v.clean_title = '{$videoName}' ";
                } elseif (!empty($random)) {
                    $sql .= " AND v.id != {$random} ";
                    //getTotalVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false, $type = '') {
                    $numRows = self::getTotalVideos($status, false, $ignoreGroup, $showUnlisted, $activeUsersOnly, $suggestedOnly);
                    if ($numRows <= 2) {
                        $rand = 0;
                    } else {
                        $rand = rand(0, $numRows - 2);
                    }
                    //$rand = ($rand - 2) < 0 ? 0 : $rand - 2;
                    $firstClauseLimit = "$rand, ";
                    //var_dump($rand, $numRows);
                    //$sql .= " ORDER BY RAND() ";
                } elseif ($suggestedOnly && empty($_GET['videoName']) && empty($_GET['search']) && empty($_GET['searchPhrase'])) {
                    $sql .= " AND v.isSuggested = 1 AND v.status = '" . self::$statusActive . "' ";
                    $numRows = self::getTotalVideos($status, false, $ignoreGroup, $showUnlisted, $activeUsersOnly, $suggestedOnly);
                    if ($numRows <= 2) {
                        $rand = 0;
                    } else {
                        $rand = rand(0, $numRows - 2);
                    }
                    //$rand = ($rand - 2) < 0 ? 0 : $rand - 2;
                    $firstClauseLimit = "$rand, ";
                    //$sql .= " ORDER BY RAND() ";
                } elseif (!empty($_GET['v']) && is_numeric($_GET['v'])) {
                    $vid = intval($_GET['v']);
                    $sql .= " AND v.id = {$vid} ";
                } else {
                    $sql .= " ORDER BY v.Created DESC ";
                }
            }
            if (strpos($sql, 'v.id IN') === false && strpos(mb_strtolower($sql), 'limit') === false) {
                $sql .= " LIMIT {$firstClauseLimit}1";
            }
            $lastGetVideoSQL = $sql;
            //echo $sql, "<br>";//exit;
            $res = sqlDAL::readSql($sql);
            $video = sqlDAL::fetchAssoc($res);
            if (!empty($video['id'])) {
                if (is_null($video['likes'])) {
                    $video['likes'] = self::updateLikesDislikes($video['id'], 'likes');
                }
                if (is_null($video['dislikes'])) {
                    $video['dislikes'] = self::updateLikesDislikes($video['id'], 'dislikes');
                }
            }
            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($video) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                $return = self::getVideo($id, $status, $ignoreGroup, $random, $suggestedOnly, $showUnlisted, $ignoreTags, $activeUsersOnly);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            sqlDAL::close($res);
            if ($res !== false) {
                require_once $global['systemRootPath'] . 'objects/userGroups.php';
                if (!empty($video)) {
                    $video = self::getInfo($video);
                }
            } else {
                $video = false;
            }
            return $video;
        }

        public static function getVideoLikes($videos_id, $refreshCache = false)
        {
            global $global, $_getLikes;

            if (!isset($_getLikes)) {
                $_getLikes = [];
            }

            if (!empty($_getLikes[$videos_id])) {
                return $_getLikes[$videos_id];
            }

            require_once $global['systemRootPath'] . 'objects/like.php';
            $obj = new stdClass();
            $obj->videos_id = $videos_id;
            $obj->likes = 0;
            $obj->dislikes = 0;
            $obj->myVote = Like::getMyVote($videos_id);

            $video = Video::getVideoLight($obj->videos_id, $refreshCache);
            $obj->likes = intval($video['likes']);
            $obj->dislikes = intval($video['dislikes']);
            $_getLikes[$videos_id] = $obj;

            return $obj;
        }

        public static function getVideoLight($id, $refreshCache = false)
        {
            global $global, $config;
            $id = intval($id);
            $sql = "SELECT * FROM videos WHERE id = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, 'i', [$id], $refreshCache);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return $video;
        }

        public static function getTotalVideosSizeFromUser($users_id)
        {
            global $global, $config;
            $users_id = intval($users_id);
            $sql = "SELECT sum(filesize) as total FROM videos WHERE 1=1 ";

            if (!empty($users_id)) {
                $sql .= " AND (users_id = '$users_id' OR users_id_company  = '$users_id' )";
            }

            $res = sqlDAL::readSql($sql, "", []);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return intval($video['total']);
        }

        public static function getTotalVideosFromUser($users_id)
        {
            global $global, $config;
            $users_id = intval($users_id);
            $sql = "SELECT count(*) as total FROM videos WHERE 1=1 ";

            if (!empty($users_id)) {
                $sql .= " AND (users_id = '$users_id' OR users_id_company ='{$users_id}' )";
            }

            $res = sqlDAL::readSql($sql, "", []);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return intval($video['total']);
        }

        public static function getVideoFromFileName($fileName, $ignoreGroup = false, $ignoreTags = false)
        {
            global $global, $_getVideoFromFileName;
            if (empty($fileName)) {
                return false;
            }
            $parts = explode("/", $fileName);
            if (!empty($parts[0])) {
                $fileName = $parts[0];
            }
            $fileName = self::getCleanFilenameFromFile($fileName);

            if (!isset($_getVideoFromFileName)) {
                $_getVideoFromFileName = [];
            }
            $indexName = "{$fileName}_{$ignoreGroup}_{$ignoreTags}";
            if (isset($_getVideoFromFileName[$indexName])) {
                return $_getVideoFromFileName[$indexName];
            }
            $_getVideoFromFileName[$indexName] = false;
            $sql = "SELECT id FROM videos WHERE filename = ? LIMIT 1";

            $res = sqlDAL::readSql($sql, "s", [$fileName]);
            if ($res !== false) {
                $video = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                if (!empty($video['id'])) {
                    $_getVideoFromFileName[$indexName] = self::getVideo($video['id'], "", $ignoreGroup, false, false, true, $ignoreTags);
                }
            }
            return $_getVideoFromFileName[$indexName];
        }

        public static function getVideoFromFileNameLight($fileName)
        {
            global $global;
            $fileName = self::getCleanFilenameFromFile($fileName);
            if (empty($fileName)) {
                return false;
            }
            $sql = "SELECT * FROM videos WHERE filename = ? LIMIT 1";
            //var_dump($sql, $fileName);
            $res = sqlDAL::readSql($sql, "s", [$fileName]);
            if ($res !== false) {
                $video = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                if (!empty($video)) {
                    if (self::forceAudio()) {
                        $video['type'] = 'audio';
                    } else if (self::forceArticle()) {
                        $video['type'] = 'article';
                    }
                }

                return $video;
            }
            return false;
        }

        public static function getVideoFromCleanTitle($clean_title)
        {
            // even increasing the max_allowed_packet it only goes away when close and reopen the connection
            global $global;
            $sql = "SELECT id  FROM videos  WHERE clean_title = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", [$clean_title]);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if (!empty($video) && $res) {
                return self::getVideo($video['id'], "", true, false, false, true);
                //$video['groups'] = UserGroups::getVideosAndCategoriesUserGroups($video['id']);
            } else {
                return false;
            }
        }

        public static function getRelatedMovies($videos_id, $limit = 10)
        {
            global $global;
            $video = self::getVideoLight($videos_id);
            if (empty($video)) {
                return array();
            }
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            $sql = "SELECT * FROM videos v WHERE v.id != {$videos_id} AND v.status='a' AND categories_id = {$video['categories_id']} ";

            $sql .= " UNION ";

            $sql .= "SELECT * FROM videos v WHERE v.id != {$videos_id} AND v.status='a' ";

            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                $sql .= " AND (";
                $sql .= "v.id IN (select videos_id FROM tags_has_videos WHERE tags_id IN "
                    . " (SELECT tags_id FROM tags_has_videos WHERE videos_id = {$videos_id}))";
                $sql .= ")";
            }

            $sql .= AVideoPlugin::getVideoWhereClause();

            $sql .= "ORDER BY RAND() LIMIT {$limit}";


            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);

            sqlDAL::close($res);
            $rows = [];
            if ($res !== false) {
                foreach ($fullData as $row) {
                    $row['images'] = self::getImageFromFilename($row['filename']);
                    if (empty($row['externalOptions'])) {
                        $row['externalOptions'] = json_encode(['videoStartSeconds' => '00:00:00']);
                    }
                    $rows[] = $row;
                }
            } else {
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $rows;
        }

        private static function startTransaction()
        {
            global $global, $_video_startTransaction_started;

            if (!empty($_video_startTransaction_started)) {
                return false;
            }
            $_video_startTransaction_started = 1;
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            $global['mysqli']->begin_transaction();
            return true;
        }

        private static function commitTransaction()
        {
            global $global, $_video_startTransaction_started;

            if (empty($_video_startTransaction_started)) {
                return false;
            }
            $_video_startTransaction_started = 0;
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            $global['mysqli']->commit();
            return true;
        }

        /**
         *
         * @global array $global
         * @param string $status
         * @param string $showOnlyLoggedUserVideos you may pass an user ID to filter results
         * @param string $ignoreGroup
         * @param string $videosArrayId an array with videos to return (for filter only)
         * @return array
         */
        public static function getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = [], $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false, $is_serie = null, $type = '')
        {
            global $global, $config, $advancedCustom, $advancedCustomUser;
            if ($config->currentVersionLowerThen('11.7')) {
                return [];
            }
            $tolerance = 0.5;
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            if (!empty($_POST['sort']['suggested'])) {
                $suggestedOnly = true;
            }
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    TimeLogStart("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})");
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                    //var_dump($videosArrayId);exit;
                    TimeLogEnd("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})", __LINE__, 0.2);
                }
            }
            $status = str_replace("'", "", $status);
            if ($status === 'suggested') {
                $suggestedOnly = true;
                $status = '';
            }
            $sql = "SELECT STRAIGHT_JOIN  u.*, u.externalOptions as userExternalOptions, v.*, c.iconClass, 
                    c.name as category, c.order as category_order, c.clean_name as clean_category,c.description as category_description,"
                . " v.created as videoCreation, v.modified as videoModified "
                //. ", (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = 1 ) as likes "
                //. ", (SELECT count(id) FROM likes as l where l.videos_id = v.id AND `like` = -1 ) as dislikes "
                . " FROM videos as v "
                . " LEFT JOIN categories c ON categories_id = c.id "
                . " LEFT JOIN users u ON v.users_id = u.id "
                . " WHERE 2=2 ";

            $blockedUsers = self::getBlockedUsersIdsArray();
            if (!empty($blockedUsers)) {
                $sql .= " AND v.users_id NOT IN ('" . implode("','", $blockedUsers) . "') ";
            }

            if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
                $uid = intval(User::getId());
                $sql .= " AND (v.users_id = '{$uid}' OR v.users_id_company ='{$uid}')";
            } elseif (!empty($showOnlyLoggedUserVideos)) {
                $uid = intval($showOnlyLoggedUserVideos);
                $sql .= " AND (v.users_id = '{$uid}' OR v.users_id_company ='{$uid}')";
            } elseif (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                if (!empty($user)) {
                    $uid = intval($user['id']);
                    $sql .= " AND (v.users_id = '{$uid}' OR v.users_id_company = '{$uid}')";
                }
            }

            if (isset($_REQUEST['is_serie']) && empty($is_serie)) {
                $is_serie = intval($_REQUEST['is_serie']);
            }

            if (isset($is_serie)) {
                if (empty($is_serie)) {
                    $sql .= " AND v.serie_playlists_id IS NULL ";
                } else {
                    $sql .= " AND v.serie_playlists_id IS NOT NULL ";
                }
            }

            if (!empty($videosArrayId) && is_array($videosArrayId) && (is_numeric($videosArrayId[0]))) {
                $sql .= " AND v.id IN ( '" . implode("', '", $videosArrayId) . "') ";
            }

            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }

            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                TimeLogStart("video::getAllVideos::getAllVideosExcludeVideosIDArray");
                $arrayNotIN = AVideoPlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
                TimeLogEnd("video::getAllVideos::getAllVideosExcludeVideosIDArray", __LINE__, 0.2);
            }
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL('v.');
            }
            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video' || $_SESSION['type'] == 'linkVideo') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } elseif ($_SESSION['type'] == 'videoOnly') {
                    $sql .= " AND (v.type = 'video')";
                } elseif ($_SESSION['type'] == 'audio') {
                    $sql .= " AND (v.type = 'audio' OR  v.type = 'linkAudio')";
                } else {
                    $sql .= " AND v.type = '{$_SESSION['type']}' ";
                }
            }

            if (!empty($type) && $type == 'notAudio') {
                $sql .= " AND v.type != 'audio' ";
            } else if (!empty($type) && $type == 'notArticleOrAudio') {
                $sql .= " AND (v.type != 'article' AND v.type != 'audio') ";
            } else if (!empty($type) && $type == 'notArticle') {
                $sql .= " AND v.type != 'article' ";
            } else if (!empty($type)) {
                $sql .= " AND v.type = '{$type}' ";
            } else if (!empty($_REQUEST['videoType']) && $_REQUEST['videoType'] == 'audio_and_video') {
                $sql .= " AND (v.type = 'audio' OR v.type = 'video') ";
            } else if (!empty($_REQUEST['videoType']) && $_REQUEST['videoType'] == 'audio_and_video_and_serie') {
                $sql .= " AND (v.type = 'audio' OR v.type = 'video' OR v.type = 'serie') ";
            } else if (!empty($_REQUEST['videoType']) && in_array($_REQUEST['videoType'], self::$typeOptions)) {
                $sql .= " AND v.type = '{$_REQUEST['videoType']}' ";
            }

            if ($status == "viewable") {
                if (User::isLogged()) {
                    $sql .= " AND (v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "') OR (v.status='u' AND (v.users_id ='" . User::getId() . "' OR v.users_id_company = '" . User::getId() . "')))";
                } else {
                    $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
                }
            } elseif ($status == "viewableNotUnlisted") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
            } elseif ($status == "publicOnly") {
                $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0";
            } elseif ($status == "privateOnly") {
                $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) > 0";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }

            if (!empty($_REQUEST['catName'])) {
                $catName = ($_REQUEST['catName']);
                $sql .= " AND (c.clean_name = '{$catName}' ";
                if (empty($_REQUEST['doNotShowCatChilds'])) {
                    $sql .= " OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$catName}' )";
                }
                $sql .= " )";
            }

            if (!empty($_GET['search'])) {
                $_POST['searchPhrase'] = $_GET['search'];
            }

            if (!empty($_GET['modified'])) {
                $_GET['modified'] = preg_replace('/[^0-9: -]/', '', $_GET['modified']);
                $sql .= " AND v.modified >= '{$_GET['modified']}'";
            }

            if (!empty($_GET['created'])) {
                $_GET['created'] = preg_replace('/[^0-9: -]/', '', $_GET['created']);
                if(is_numeric($_GET['created']) && $_GET['created'] > 0){
                    $_GET['created'] = intval($_GET['created']);
                    $sql .= " AND v.created >= DATE_SUB(CURDATE(), INTERVAL {$_GET['created']} DAY)";
                }else{
                    $sql .= " AND v.created >= '{$_GET['created']}'";
                }
            }

            if (!empty($_REQUEST['minViews'])) {
                $minViews = intval($_REQUEST['minViews']);
                $sql .= " AND v.views_count >= '{$minViews}'";
            }

            if (!empty($_POST['searchPhrase'])) {
                $_POST['searchPhrase'] = mb_strtolower(str_replace('&quot;', '"', $_POST['searchPhrase']));
                $searchFieldsNames = self::getSearchFieldsNames();
                if (AVideoPlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name "
                        . "LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost($searchFieldsNames, "OR");
                    $searchFieldsNames = ['v.title'];
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']);
                    $sql .= ")";
                } else {
                    $sql .= ' AND (1=1 ' . BootGrid::getSqlSearchFromPost($searchFieldsNames);
                    $searchFieldsNames = ['v.title'];
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']) . ')';
                }
            }

            $sql .= AVideoPlugin::getVideoWhereClause();

            if ($suggestedOnly) {
                $sql .= " AND v.isSuggested = 1 AND v.status = '" . self::$statusActive . "' ";
                $sql .= " ORDER BY RAND() ";
                $sort = @$_POST['sort'];
                unset($_POST['sort']);
                $sql .= BootGrid::getSqlFromPost([], empty($_POST['sort']['likes']) ? "v." : "", "", true);
                if (strpos(mb_strtolower($sql), 'limit') === false) {
                    $sql .= " LIMIT 60 ";
                }
                $_POST['sort'] = $sort;
            } elseif (!isset($_POST['sort']['trending']) && !isset($_GET['sort']['trending'])) {
                if (!empty($_POST['sort']['created']) && !empty($_POST['sort']['likes'])) {
                    $_POST['sort']['v.created'] = $_POST['sort']['created'];
                    unset($_POST['sort']['created']);
                }
                $sql .= BootGrid::getSqlFromPost([], empty($_POST['sort']['likes']) ? "v." : "", "", true);
            } else {
                unset($_POST['sort']['trending'], $_GET['sort']['trending']);
                $rows = [];
                if (!empty($_REQUEST['current']) && $_REQUEST['current'] == 1) {
                    $rows = VideoStatistic::getVideosWithMoreViews($status, $showOnlyLoggedUserVideos, $showUnlisted, $suggestedOnly);
                }
                //var_dump($_REQUEST['current'], $rows); 
                $ids = [];
                foreach ($rows as $row) {
                    $ids[] = $row['id'];
                }

                $daysLimit = getTrendingLimit();

                if (!empty($ids)) {
                    $sql .= " ORDER BY FIND_IN_SET(v.id, '" . implode(",", $ids) . "') DESC, likes DESC ";
                } else {
                    $sql .= " ORDER BY likes DESC ";
                }
                $sql .= ObjectYPT::getSqlLimit();
            }
            if (strpos(mb_strtolower($sql), 'limit') === false) {
                if (!empty($_GET['limitOnceToOne'])) {
                    $sql .= " LIMIT 1";
                    unset($_GET['limitOnceToOne']);
                } else {
                    $_REQUEST['rowCount'] = getRowCount();
                    if (!empty($_REQUEST['rowCount'])) {
                        $sql .= " LIMIT {$_REQUEST['rowCount']}";
                    } else {
                        _error_log("getAllVideos without limit " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
                        if (empty($global['limitForUnlimitedVideos'])) {
                            $global['limitForUnlimitedVideos'] = 100;
                        }
                        if ($global['limitForUnlimitedVideos'] > 0) {
                            $sql .= " LIMIT {$global['limitForUnlimitedVideos']}";
                        }
                    }
                }
            }

            //echo $sql;var_dump($_REQUEST['doNotShowCatChilds']);exit;
            //_error_log("getAllVideos($status, $showOnlyLoggedUserVideos , $ignoreGroup , ". json_encode($videosArrayId).")" . $sql);

            $timeLogName = TimeLogStart("video::getAllVideos");
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            //var_dump($sql, $fullData);exit;
            TimeLogEnd($timeLogName, __LINE__, 0.2);

            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($fullData) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                TimeLogEnd($timeLogName, __LINE__, 1);
                $return = self::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs, $showUnlisted, $activeUsersOnly, $suggestedOnly);
                TimeLogEnd($timeLogName, __LINE__, 1);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            sqlDAL::close($res);
            $videos = [];
            if ($res !== false) {
                //$global['mysqli']->commit();
                require_once 'userGroups.php';
                TimeLogStart("video::getAllVideos foreach");
                // for the cache on the database fast insert

                TimeLogEnd($timeLogName, __LINE__, 0.2);

                $allowedDurationTypes = ['video', 'audio'];

                /**
                 *
                 * @var array $global
                 * @var object $global['mysqli']
                 */
                self::startTransaction();
                foreach ($fullData as $index => $row) {
                    if (is_null($row['likes'])) {
                        self::startTransaction();
                        _error_log("Video::updateLikesDislikes: id={$row['id']}");
                        $row['likes'] = self::updateLikesDislikes($row['id'], 'likes');
                    }
                    if (is_null($row['dislikes'])) {
                        self::startTransaction();
                        _error_log("Video::updateLikesDislikes: id={$row['id']}");
                        $row['dislikes'] = self::updateLikesDislikes($row['id'], 'dislikes');
                    }

                    if (empty($row['duration_in_seconds']) && in_array($row['type'], $allowedDurationTypes)) {
                        self::startTransaction();
                        _error_log("Video::duration_in_seconds: id={$row['id']} {$row['duration']} {$row['type']}");
                        $row['duration_in_seconds'] = self::updateDurationInSeconds($row['id'], $row['duration']);
                        if (empty($row['duration_in_seconds'])) {
                            //_error_log("Video duration_in_seconds not updated: id={$row['id']} type={$row['type']}");
                        }
                    }
                    $tlogName = TimeLogStart("video::getInfo index={$index} id={$row['id']} {$row['type']}");
                    self::startTransaction();
                    $row = self::getInfo($row, $getStatistcs);
                    TimeLogEnd($tlogName, __LINE__, $tolerance / 2);
                    $row['externalOptions'] = _json_decode($row['externalOptions']);
                    if(empty($row['externalOptions']->privacyInfo)){
                        $row['externalOptions']->privacyInfo = self::updatePrivacyInfo($row['id']);
                    }

                    $videos[] = $row;
                }
                TimeLogEnd("video::getAllVideos foreach", __LINE__, $tolerance);
                self::commitTransaction();
                TimeLogEnd("video::getAllVideos foreach", __LINE__, $tolerance);
                $rowCount = getRowCount();
                $tolerance = $rowCount / 100;
                if ($tolerance < 0.2) {
                    $tolerance = 0.2;
                } elseif ($tolerance > 2) {
                    $tolerance = 2;
                }
                TimeLogEnd("video::getAllVideos foreach", __LINE__, $tolerance);
                //$videos = $res->fetch_all(MYSQLI_ASSOC);
            } 
            return $videos;
        }

        static function getInfo($row, $getStatistcs = false)
        {
            if (empty($row)) {
                return array();
            }
            $rowOriginal = $row;
            $TimeLogLimit = 0.2;
            $timeLogName = TimeLogStart("video::getInfo getStatistcs");
            $name = "_getVideoInfo_{$row['id']}";
            $OneHour = 3600;
            $cache = ObjectYPT::getCache($name, $OneHour);
            if (!empty($cache)) {
                $externalOptions = $cache->externalOptions;
                $obj = object_to_array($cache);
                foreach ($row as $key => $value) {
                    $obj[$key] = $value;
                }
                if (!empty($externalOptions)) {
                    if (is_object($externalOptions)) {
                        $obj['externalOptions'] = $externalOptions;
                    } elseif (is_string($externalOptions)) {
                        $obj['externalOptions'] = _json_decode($externalOptions);
                    }
                    $obj['externalOptions'] = json_encode($obj['externalOptions']);
                }
                if (empty($obj['externalOptions'])) {
                    $obj['externalOptions'] = json_encode(['videoStartSeconds' => '00:00:00']);
                }
                if (!empty($obj['userExternalOptions']) && is_string($obj['userExternalOptions'])) {
                    $obj['userExternalOptions'] = User::decodeExternalOption($obj['userExternalOptions']);
                }
                $obj = cleanUpRowFromDatabase($obj);

                if (!self::canEdit($obj['id'])) {
                    if (!empty($rowOriginal['video_password'])) {
                        $obj['video_password'] = '1';
                    } else {
                        $obj['video_password'] = '0';
                    }
                }else{
                    $obj['video_password'] = empty($rowOriginal['video_password'])?'':$rowOriginal['video_password'];
                }
                if (self::forceAudio()) {
                    $obj['type'] = 'audio';
                } else if (self::forceArticle()) {
                    $obj['type'] = 'article';
                }
                return $obj;
            }
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row = cleanUpRowFromDatabase($row);
            if (!self::canEdit($row['id'])) {
                if (!empty($rowOriginal['video_password'])) {
                    $row['video_password'] = '1';
                } else {
                    $row['video_password'] = '0';
                }
            }else{
                $row['video_password'] = empty($rowOriginal['video_password'])?'':$rowOriginal['video_password'];
            }
            $row['externalOptions'] = _json_decode($row['externalOptions']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            if ($getStatistcs) {
                $previewsMonth = date("Y-m-d 00:00:00", strtotime("-30 days"));
                $previewsWeek = date("Y-m-d 00:00:00", strtotime("-7 days"));
                $today = date('Y-m-d 23:59:59');
                $row['statistc_all'] = VideoStatistic::getStatisticTotalViews($row['id']);
                $row['statistc_today'] = VideoStatistic::getStatisticTotalViews($row['id'], false, date('Y-m-d 00:00:00'), $today);
                $row['statistc_week'] = VideoStatistic::getStatisticTotalViews($row['id'], false, $previewsWeek, $today);
                $row['statistc_month'] = VideoStatistic::getStatisticTotalViews($row['id'], false, $previewsMonth, $today);
                $row['statistc_unique_user'] = VideoStatistic::getStatisticTotalViews($row['id'], true);
            }
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $otherInfocachename = "otherInfo{$row['id']}";
            $otherInfo = object_to_array(ObjectYPT::getCache($otherInfocachename, 600));
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            if (empty($otherInfo)) {
                $otherInfo = [];
                $otherInfo['category'] = xss_esc_back(@$row['category']);
                //TimeLogStart("video::otherInfo");
                $otherInfo['groups'] = UserGroups::getVideosAndCategoriesUserGroups($row['id']);
                //TimeLogEnd("video::otherInfo", __LINE__, 0.05);
                $otherInfo['tags'] = self::getTags($row['id']);
                //TimeLogEnd("video::otherInfo", __LINE__, 0.05);
                $cached = ObjectYPT::setCache($otherInfocachename, $otherInfo);
                //TimeLogEnd("video::otherInfo", __LINE__, 0.05);
                //_error_log("video::getInfo cache " . json_encode($cached));
            }
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $otherInfo['title'] = UTF8encode($row['title']);
            $otherInfo['description'] = UTF8encode($row['description']);
            $otherInfo['descriptionHTML'] = self::htmlDescription($otherInfo['description']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            foreach ($otherInfo as $key => $value) {
                $row[$key] = $value;
            }
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['hashId'] = idToHash($row['id']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['link'] = self::getLinkToVideo($row['id'], $row['clean_title']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['embedlink'] = self::getLinkToVideo($row['id'], $row['clean_title'], true);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['progress'] = self::getVideoPogressPercent($row['id']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['isFavorite'] = self::isFavorite($row['id']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['isWatchLater'] = self::isWatchLater($row['id']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['favoriteId'] = self::getFavoriteIdFromUser(User::getId());
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['watchLaterId'] = self::getWatchLaterIdFromUser(User::getId());
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['total_seconds_watching_human'] = seconds2human($row['total_seconds_watching']);
            $row['views_count_short'] = number_format_short($row['views_count']);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            $row['identification'] = User::getNameIdentificationById(!empty($row['users_id_company']) ? $row['users_id_company'] : $row['users_id']);

            if (empty($row['externalOptions'])) {
                $row['externalOptions'] = json_encode(['videoStartSeconds' => '00:00:00']);
            }
            if (!empty($row['userExternalOptions']) && is_string($row['userExternalOptions'])) {
                $row['userExternalOptions'] = User::decodeExternalOption($row['userExternalOptions']);
            }
            //var_dump($row['userExternalOptions']);exit;
            $row = array_merge($row, AVideoPlugin::getAllVideosArray($row['id']));
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
            ObjectYPT::setCache($name, $row);
            TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);

            if (self::forceAudio()) {
                $video['type'] = 'audio';
            } else if (self::forceArticle()) {
                $video['type'] = 'article';
            }
            return $row;
        }

        public static function getMediaSession($videos_id)
        {
            $video = Video::getVideoLight($videos_id);

            $MediaMetadata = new stdClass();
            if (empty($video)) {
                return $MediaMetadata;
            }
            $video = Video::getInfo($video);

            $posters = Video::getMediaSessionPosters($videos_id);
            //var_dump($posters);exit;
            if (empty($posters)) {
                $posters = array();
            }

            $MediaMetadata->title = $video['title'];
            $MediaMetadata->artist = $video['identification'];
            $MediaMetadata->album = $video['category'];
            $MediaMetadata->artwork = [];
            foreach ($posters as $key => $value) {
                $MediaMetadata->artwork[] = ['src' => $value['url'], 'sizes' => "{$key}x{$key}", 'type' => 'image/jpg'];
            }
            return $MediaMetadata;
        }

        public static function htmlDescription($description)
        {
            if (empty($description)) {
                return '';
            }
            if (strip_tags($description) !== $description) {
                return $description;
            } else {
                return nl2br(textToLink(htmlentities($description)));
            }
        }

        public static function isFavorite($videos_id)
        {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::isVideoOnFavorite($videos_id, User::getId());
            }
            return false;
        }

        public static function isSerie($videos_id)
        {
            $v = new Video("", "", $videos_id);
            return !empty($v->getSerie_playlists_id());
        }

        public static function isWatchLater($videos_id)
        {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::isVideoOnWatchLater($videos_id, User::getId());
            }
            return false;
        }

        public static function getFavoriteIdFromUser($users_id)
        {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::getFavoriteIdFromUser($users_id);
            }
            return false;
        }

        public static function getWatchLaterIdFromUser($users_id)
        {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                return PlayList::getWatchLaterIdFromUser($users_id);
            }
            return false;
        }

        public static function updateFilesizeFromFilename($filename)
        {
            $video = Video::getVideoFromFileNameLight($filename);
            if ($video['type'] !== 'video' && $video['type'] !== 'audio') {
                return false;
            }
            return self::updateFilesize($video['id']);
        }

        public static function updateFilesize($videos_id)
        {
            global $config, $global;

            if (!empty($global['ignoreUupdateFilesize'])) {
                return false;
            }
            if ($config->currentVersionLowerThen('8.5')) {
                return false;
            }
            TimeLogStart("Video::updateFilesize {$videos_id}");
            ini_set('max_execution_time', 300); // 5
            set_time_limit(300);
            $video = new Video("", "", $videos_id, true);
            $_type = $video->getType();
            if ($_type !== 'video' && $_type !== 'audio') {
                return false;
            }
            $filename = $video->getFilename();
            if (empty($filename)) {
                //_error_log("updateFilesize: Not updated, this filetype is ".$video->getType());
                return false;
            }
            $filesize = getUsageFromFilename($filename);
            if (empty($filesize)) {
                $obj = AVideoPlugin::getObjectDataIfEnabled("DiskUploadQuota");
                if (!empty($obj->deleteVideosWith0Bytes)) {
                    try {
                        _error_log("updateFilesize: DELETE videos_id=$videos_id filename=$filename filesize=$filesize " . humanFileSize($filesize));
                        return $video->delete();
                    } catch (Exception $exc) {
                        _error_log("updateFilesize: ERROR " . $exc->getTraceAsString());
                        return false;
                    }
                }
            }
            if ($video->getFilesize() == $filesize) {
                _error_log("updateFilesize: No need to update videos_id=$videos_id filename=$filename filesize=$filesize " . humanFileSize($filesize));
                return $filesize;
            }
            $video->setFilesize($filesize);
            TimeLogEnd("Video::updateFilesize {$videos_id}", __LINE__);
            if ($video->save(false, true)) {
                _error_log("updateFilesize: videos_id=$videos_id filename=$filename filesize=$filesize " . humanFileSize($filesize));
                Video::clearCache($videos_id);
                return $filesize;
            } else {
                _error_log("updateFilesize: ERROR videos_id=$videos_id filename=$filename filesize=$filesize " . humanFileSize($filesize));
                return false;
            }
        }

        /**
         * Same as getAllVideos() method but a lighter query
         * @global array $global
         * @global type $config
         * @param string $showOnlyLoggedUserVideos
         * @return array
         */
        public static function getAllVideosLight($status = "viewable", $showOnlyLoggedUserVideos = false, $showUnlisted = false, $suggestedOnly = false, $type = '')
        {
            global $global, $config;
            if ($config->currentVersionLowerThen('5')) {
                return [];
            }
            $status = str_replace("'", "", $status);
            if ($status === 'suggested') {
                $suggestedOnly = true;
                $status = '';
            }
            $sql = "SELECT v.* FROM videos as v ";

            if (!empty($_REQUEST['catName'])) {
                $sql .= " LEFT JOIN categories c ON categories_id = c.id ";
            }

            $sql .= " WHERE 1=1 ";
            $blockedUsers = self::getBlockedUsersIdsArray();
            if (!empty($blockedUsers)) {
                $sql .= " AND v.users_id NOT IN ('" . implode("','", $blockedUsers) . "') ";
            }
            if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
                $sql .= " AND (v.users_id = '" . User::getId() . "' OR v.users_id_company ='" . User::getId() . "')";
            } elseif (!empty($showOnlyLoggedUserVideos)) {
                $sql .= " AND (v.users_id = '{$showOnlyLoggedUserVideos}' OR v.users_id_company = '{$showOnlyLoggedUserVideos}')";
            }
            if ($status == "viewable") {
                if (User::isLogged()) {
                    $sql .= " AND (v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "') OR (v.status='u' AND (v.users_id ='" . User::getId() . "' OR v.users_id_company ='" . User::getId() . "' )))";
                } else {
                    $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
                }
            } elseif ($status == "viewableNotUnlisted") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
            } elseif ($status == "publicOnly") {
                $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }

            if (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                $sql .= " AND (v.users_id = '{$user['id']}' OR v.users_id_company = '{$user['id']}')";
            }
            if (!empty($_REQUEST['catName'])) {
                $catName = ($_REQUEST['catName']);
                $sql .= " AND (c.clean_name = '{$catName}' ";
                if (empty($_REQUEST['doNotShowCatChilds'])) {
                    $sql .= " OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$catName}' )";
                }
                $sql .= " )";
            }
            $sql .= AVideoPlugin::getVideoWhereClause();
            if (!empty($type)) {
                $sql .= " AND v.type = '" . $type . "' ";
            }
            if ($suggestedOnly) {
                $sql .= " AND v.isSuggested = 1 AND v.status = '" . self::$statusActive . "' ";
                $sql .= " ORDER BY RAND() ";
            }
            if (strpos(mb_strtolower($sql), 'limit') === false) {
                if (empty($global['limitForUnlimitedVideos'])) {
                    $global['limitForUnlimitedVideos'] = empty($global['rowCount']) ? 1000 : $global['rowCount'];
                }
                if ($global['limitForUnlimitedVideos'] > 0) {
                    $sql .= " LIMIT {$global['limitForUnlimitedVideos']}";
                }
            }
            //echo $sql;exit;
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);

            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($fullData) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                $return = self::getAllVideosLight($status, $showOnlyLoggedUserVideos, $showUnlisted, $suggestedOnly);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            sqlDAL::close($res);
            $videos = [];
            if ($res !== false) {
                foreach ($fullData as $row) {
                    if (empty($row['duration_in_seconds']) && $row['type'] !== 'article') {
                        $row['duration_in_seconds'] = self::updateDurationInSeconds($row['id'], $row['duration']);
                    }
                    if (empty($row['filesize'])) {
                        if ($row['type'] == 'video' || $row['type'] == 'audio') {
                            $row['filesize'] = Video::updateFilesize($row['id']);
                        }
                    }
                    $videos[] = $row;
                }
                //$videos = $res->fetch_all(MYSQLI_ASSOC);
            } else {
                $videos = [];
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $videos;
        }

        public static function getTotalVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false, $type = '')
        {
            global $global, $config, $advancedCustomUser;
            if ($config->currentVersionLowerThen('11.7')) {
                return false;
            }
            if (!empty($_POST['sort']['suggested'])) {
                $suggestedOnly = true;
            }
            $status = str_replace("'", "", $status);
            if ($status === 'suggested') {
                $suggestedOnly = true;
                $status = '';
            }
            /*
              $cn = '';
              if (!empty($_REQUEST['catName'])) {
              $cn .= ", c.clean_name as cn";
              }
             *
             */
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                    TimeLogStart("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})");
                    $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                    TimeLogEnd("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})", __LINE__);
                }
            }
            /*
              $sql = "SELECT STRAIGHT_JOIN  v.users_id, v.type, v.id, v.title,v.description, c.name as category {$cn} "
              . "FROM videos v "
              . "LEFT JOIN categories c ON categories_id = c.id "
              . " LEFT JOIN users u ON v.users_id = u.id "
              . " WHERE 1=1 ";
             */

            $sql = "SELECT count(v.id) as total "
                . "FROM videos v "
                . "LEFT JOIN categories c ON categories_id = c.id "
                . " LEFT JOIN users u ON v.users_id = u.id "
                . " WHERE 1=1 ";
            $blockedUsers = self::getBlockedUsersIdsArray();
            if (!empty($blockedUsers)) {
                $sql .= " AND v.users_id NOT IN ('" . implode("','", $blockedUsers) . "') ";
            }
            if ($activeUsersOnly) {
                $sql .= " AND u.status = 'a' ";
            }
            $sql .= static::getVideoQueryFileter();
            if (!$ignoreGroup) {
                $sql .= self::getUserGroupsCanSeeSQL('v.');
            }
            if (!empty($videosArrayId) && is_array($videosArrayId) && is_string($videosArrayId[0])) {
                $sql .= " AND v.id IN ( '" . implode("', '", $videosArrayId) . "') ";
            }
            if ($status == "viewable") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus($showUnlisted)) . "')";
            } elseif ($status == "viewableNotUnlisted") {
                $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
            } elseif ($status == "publicOnly") {
                $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0";
            } elseif ($status == "privateOnly") {
                $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) > 0";
            } elseif (!empty($status)) {
                $sql .= " AND v.status = '{$status}'";
            }

            if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
                $sql .= " AND (v.users_id = '" . User::getId() . "' OR v.users_id_company  = '" . User::getId() . "')";
            } elseif (is_int($showOnlyLoggedUserVideos)) {
                $sql .= " AND (v.users_id = '{$showOnlyLoggedUserVideos}' OR v.users_id_company  = '{$showOnlyLoggedUserVideos}')";
            }

            if (isset($_REQUEST['is_serie'])) {
                $is_serie = intval($_REQUEST['is_serie']);
                if (empty($is_serie)) {
                    $sql .= " AND v.serie_playlists_id IS NULL ";
                } else {
                    $sql .= " AND v.serie_playlists_id IS NOT NULL ";
                }
            }

            if (!empty($_GET['created'])) {
                $_GET['created'] = preg_replace('/[^0-9: -]/', '', $_GET['created']);
                if(is_numeric($_GET['created']) && $_GET['created'] > 0){
                    $_GET['created'] = intval($_GET['created']);
                    $sql .= " AND v.created >= DATE_SUB(CURDATE(), INTERVAL {$_GET['created']} DAY)";
                }else{
                    $sql .= " AND v.created >= '{$_GET['created']}'";
                }
            }

            if (!empty($_REQUEST['minViews'])) {
                $minViews = intval($_REQUEST['minViews']);
                $sql .= " AND v.views_count >= '{$minViews}'";
            }

            if (!empty($_REQUEST['catName'])) {
                $catName = ($_REQUEST['catName']);
                $sql .= " AND (c.clean_name = '{$catName}' ";
                if (empty($_REQUEST['doNotShowCatChilds'])) {
                    $sql .= " OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name = '{$catName}' )";
                }
                $sql .= " )";
            }

            if (!empty($_SESSION['type'])) {
                if ($_SESSION['type'] == 'video') {
                    $sql .= " AND (v.type = 'video' OR  v.type = 'embed' OR  v.type = 'linkVideo')";
                } elseif ($_SESSION['type'] == 'audio') {
                    $sql .= " AND (v.type = 'audio' OR  v.type = 'linkAudio')";
                } else {
                    $sql .= " AND v.type = '{$_SESSION['type']}' ";
                }
            }

            if (!empty($type) && $type == 'notAudio') {
                $sql .= " AND v.type != 'audio' ";
            } else if (!empty($type) && $type == 'notArticleOrAudio') {
                $sql .= " AND (v.type != 'article' AND v.type != 'audio') ";
            } else if (!empty($type) && $type == 'notArticle') {
                $sql .= " AND v.type != 'article' ";
            } else if (!empty($type)) {
                $sql .= " AND v.type = '{$type}' ";
            } else if (!empty($_REQUEST['videoType']) && $_REQUEST['videoType'] == 'audio_and_video') {
                $sql .= " AND (v.type = 'audio' OR v.type = 'video') ";
            } else if (!empty($_REQUEST['videoType']) && $_REQUEST['videoType'] == 'audio_and_video_and_serie') {
                $sql .= " AND (v.type = 'audio' OR v.type = 'video' OR v.type = 'serie') ";
            } else if (!empty($_REQUEST['videoType']) && in_array($_REQUEST['videoType'], self::$typeOptions)) {
                $sql .= " AND v.type = '{$_REQUEST['videoType']}' ";
            }

            if (!$ignoreGroup) {
                $arrayNotIN = AVideoPlugin::getAllVideosExcludeVideosIDArray();
                if (!empty($arrayNotIN) && is_array($arrayNotIN)) {
                    $sql .= " AND v.id NOT IN ( '" . implode("', '", $arrayNotIN) . "') ";
                }
            }
            if (!empty($_GET['channelName'])) {
                $user = User::getChannelOwner($_GET['channelName']);
                if (!empty($user)) {
                    $uid = intval($user['id']);
                    $sql .= " AND (v.users_id = '{$uid}' OR v.users_id_company  = '{$uid}')";
                }
            }

            $sql .= AVideoPlugin::getVideoWhereClause();

            if (!empty($_POST['searchPhrase'])) {
                $_POST['searchPhrase'] = mb_strtolower(str_replace('&quot;', '"', $_POST['searchPhrase']));
                $searchFieldsNames = self::getSearchFieldsNames();
                if (AVideoPlugin::isEnabledByName("VideoTags")) {
                    $sql .= " AND (";
                    $sql .= "v.id IN (select videos_id FROM tags_has_videos LEFT JOIN tags as t ON tags_id = t.id AND t.name LIKE '%{$_POST['searchPhrase']}%' WHERE t.id is NOT NULL)";
                    $sql .= BootGrid::getSqlSearchFromPost($searchFieldsNames, "OR");
                    $searchFieldsNames = ['v.title'];
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']);
                    $sql .= ")";
                } else {
                    $sql .= ' AND (1=1 ' . BootGrid::getSqlSearchFromPost($searchFieldsNames);
                    $searchFieldsNames = ['v.title'];
                    $sql .= self::getFullTextSearch($searchFieldsNames, $_POST['searchPhrase']) . ')';
                }
            }

            if ($suggestedOnly) {
                $sql .= " AND v.isSuggested = 1 AND v.status = '" . self::$statusActive . "' ";
            }
            /*
              $res = sqlDAL::readSql($sql);
              $numRows = sqlDal::num_rows($res);
              sqlDAL::close($res);
             *
             */
            global $lastGetTotalVideos;
            $lastGetTotalVideos = $sql;
            $res = sqlDAL::readSql($sql);
            $video = sqlDAL::fetchAssoc($res);
            $numRows = intval($video['total']);
            //var_dump($numRows, $sql);
            // if there is a search, and there is no data and is inside a channel try again without a channel
            if (!empty($_GET['search']) && empty($numRows) && !empty($_GET['channelName'])) {
                $channelName = $_GET['channelName'];
                unset($_GET['channelName']);
                $return = self::getTotalVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $showUnlisted, $activeUsersOnly, $suggestedOnly);
                $_GET['channelName'] = $channelName;
                return $return;
            }

            return $numRows;
        }

        static function getSearchFieldsNames()
        {
            global $advancedCustomUser;
            $searchFieldsNames = self::$searchFieldsNames;
            if ($advancedCustomUser->videosSearchAlsoSearchesOnChannelName) {
                $searchFieldsNames[] = 'u.channelName';
            }
            $newSearchFieldsNames = [];
            if (!empty($_REQUEST['searchFieldsNames'])) {
                if (!is_array($_REQUEST['searchFieldsNames'])) {
                    $_REQUEST['searchFieldsNames'] = [$_REQUEST['searchFieldsNames']];
                }
                foreach ($_REQUEST['searchFieldsNames'] as $value) {
                    if (in_array($value, $searchFieldsNames)) {
                        $newSearchFieldsNames[] = $value;
                    }
                }
            }
            if (empty($newSearchFieldsNames)) {
                $newSearchFieldsNames = $searchFieldsNames;
            }
            return $newSearchFieldsNames;
        }

        public static function getTotalVideosInfo($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = [])
        {
            $obj = new stdClass();
            $obj->likes = 0;
            $obj->disLikes = 0;
            $obj->views_count = 0;
            $obj->total_minutes = 0;

            $videos = static::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId);

            foreach ($videos as $value) {
                $obj->likes += intval($value['likes']);
                $obj->disLikes += intval($value['dislikes']);
                $obj->views_count += intval($value['views_count']);
                $obj->total_minutes += intval(parseDurationToSeconds($value['duration']) / 60);
            }

            return $obj;
        }

        public static function getTotalVideosInfoAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = [], $getStatistcs = false)
        {
            global $global, $advancedCustom;
            $path = getCacheDir() . "getTotalVideosInfo/";
            make_path($path);
            $cacheFileName = "{$path}_{$status}_{$showOnlyLoggedUserVideos}_{$ignoreGroup}_" . implode($videosArrayId) . "_{$getStatistcs}";
            $return = [];
            if (!file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return [];
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getTotalVideosInfo($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = _json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > cacheExpirationTime()) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getTotalVideosInfoAsync.php' "
                    . " '$status' '$showOnlyLoggedUserVideos' '$ignoreGroup', '" . json_encode($videosArrayId) . "', "
                    . " '$getStatistcs', '$cacheFileName'");
                //_error_log("getTotalVideosInfoAsync: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return $return;
        }

        public static function getViewableStatus($showUnlisted = false)
        {
            $viewable = [Video::$statusActive, Video::$statusActiveAndEncoding, Video::$statusFansOnly];
            if ($showUnlisted) {
                $viewable[] = Video::$statusUnlisted;
                $viewable[] = Video::$statusUnlistedButSearchable;
            } else {
                $search = getSearchVar();
                if (!empty($search)) {
                    $viewable[] = Video::$statusUnlistedButSearchable;
                }
            }
            if (User::isAdmin()) {
                $viewable[] = Video::$statusScheduledReleaseDate;
            }
            /*
             * Cannot do that otherwise it will list videos on the list videos menu
              $videos_id = getVideos_id();
              if (!empty($videos_id)) {
              $post = $_POST;
              if (self::isOwner($videos_id) || Permissions::canModerateVideos()) {
              $viewable[] = "u";
              }
              $_POST = $post;
              }
             *
             */
            return $viewable;
        }

        public static function getVideoConversionStatus($filename)
        {
            global $global;
            require_once $global['systemRootPath'] . 'objects/user.php';
            if (!User::isLogged()) {
                die("Only logged users can upload");
            }

            $object = new stdClass();

            foreach (self::$types as $value) {
                $progressFilename = self::getStoragePathFromFileName($filename) . "progress_{$value}.txt";
                $content = @url_get_contents($progressFilename);
                $object->$value = new stdClass();
                if (!empty($content)) {
                    $object->$value = self::parseProgress($content);
                } else {
                }

                if (!empty($object->$value->progress) && !is_numeric($object->$value->progress)) {
                    $video = self::getVideoFromFileName($filename);
                    //var_dump($video, $filename);
                    if (!empty($video)) {
                        $object->$value->progress = self::$statusDesc[$video['status']];
                    }
                }

                $object->$value->filename = $progressFilename;
            }

            return $object;
        }

        private static function parseProgress($content)
        {
            //get duration of source

            $obj = new stdClass();

            $obj->duration = 0;
            $obj->currentTime = 0;
            $obj->progress = 0;
            //var_dump($content);exit;
            preg_match("/Duration: (.*?), start:/", $content, $matches);
            if (!empty($matches[1])) {
                $rawDuration = $matches[1];

                //rawDuration is in 00:00:00.00 format. This converts it to seconds.
                $ar = array_reverse(explode(":", $rawDuration));
                $duration = floatval($ar[0]);
                if (!empty($ar[1])) {
                    $duration += intval($ar[1]) * 60;
                }
                if (!empty($ar[2])) {
                    $duration += intval($ar[2]) * 60 * 60;
                }

                //get the time in the file that is already encoded
                preg_match_all("/time=(.*?) bitrate/", $content, $matches);

                $rawTime = array_pop($matches);

                //this is needed if there is more than one match
                if (is_array($rawTime)) {
                    $rawTime = array_pop($rawTime);
                }

                //rawTime is in 00:00:00.00 format. This converts it to seconds.
                $ar = array_reverse(explode(":", $rawTime));
                $time = floatval($ar[0]);
                if (!empty($ar[1])) {
                    $time += intval($ar[1]) * 60;
                }
                if (!empty($ar[2])) {
                    $time += intval($ar[2]) * 60 * 60;
                }

                if (!empty($duration)) {
                    //calculate the progress
                    $progress = round(($time / $duration) * 100);
                } else {
                    $progress = 'undefined';
                }
                $obj->duration = $duration;
                $obj->currentTime = $time;
                $obj->progress = $progress;
            }
            return $obj;
        }

        public function delete($allowOfflineUser = false)
        {
            if (!$allowOfflineUser && !$this->userCanManageVideo()) {
                return false;
            }

            global $global;
            if (!empty($this->id)) {
                $this->removeNextVideos($this->id);
                $this->removeTrailerReference($this->id);
                $this->removeCampaign($this->id);
                $video = self::getVideoLight($this->id);
                $sql = "DELETE FROM videos WHERE id = ?";
            } else {
                return false;
            }

            $resp = sqlDAL::writeSql($sql, "i", [$this->id]);
            if ($resp == false) {
                //_error_log('Error (delete on video) : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
                return false;
            } else {
                $this->removeVideoFiles();
            }
            return $resp;
        }

        public function removeVideoFiles()
        {
            $filename = $this->getFilename();
            if (empty($filename)) {
                return false;
            }
            try {
                _error_log("removeVideoFiles: [{$filename}] ");
                $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
                $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
                $YPTStorage = AVideoPlugin::loadPluginIfEnabled('YPTStorage');
                $cdn = AVideoPlugin::loadPluginIfEnabled('CDN');
                if (!empty($cdn)) {
                    $cdn_obj = $cdn->getDataObject();
                    if (!empty($cdn_obj->enable_storage) && !empty($this->getSites_id())) {
                        CDNStorage::deleteRemoteDirectoryFromFilename($filename);
                    }
                }
                if (!empty($aws_s3)) {
                    _error_log("removeVideoFiles S3 ");
                    $aws_s3->removeFiles($filename);
                }
                if (!empty($bb_b2)) {
                    _error_log("removeVideoFiles B2 ");
                    $bb_b2->removeFiles($filename);
                }
                if (!empty($ftp)) {
                    _error_log("removeVideoFiles FTP ");
                    $ftp->removeFiles($filename);
                }
                if (!empty($YPTStorage) && !empty($this->getSites_id())) {
                    _error_log("removeVideoFiles YPTStorage Sites_id=" . $this->getSites_id());
                    $YPTStorage->removeFiles($filename, $this->getSites_id());
                }
            } catch (Exception $exc) {
                _error_log("removeVideoFiles: Error on delete files [{$filename}] " . $exc->getTraceAsString());
            }


            $this->removeFiles($filename);
            self::deleteThumbs($filename);
        }

        private function removeNextVideos($videos_id)
        {
            if (!$this->userCanManageVideo()) {
                return false;
            }

            global $global;

            if (!empty($videos_id)) {
                $sql = "UPDATE videos SET next_videos_id = NULL WHERE next_videos_id = ?";
                sqlDAL::writeSql($sql, "s", [$videos_id]);
            } else {
                return false;
            }
            return true;
        }

        private function removeTrailerReference($videos_id)
        {
            if (!$this->userCanManageVideo()) {
                return false;
            }

            global $global;

            if (!empty($videos_id)) {
                $videoURL = self::getLink($videos_id, '', true);
                $sql = "UPDATE videos SET trailer1 = '' WHERE trailer1 = ?";
                sqlDAL::writeSql($sql, "s", [$videoURL]);
                $sql = "UPDATE videos SET trailer2 = '' WHERE trailer2 = ?";
                sqlDAL::writeSql($sql, "s", [$videoURL]);
                $sql = "UPDATE videos SET trailer3 = '' WHERE trailer3 = ?";
                sqlDAL::writeSql($sql, "s", [$videoURL]);
            } else {
                return false;
            }
            return true;
        }

        private function removeCampaign($videos_id)
        {
            if (ObjectYPT::isTableInstalled('vast_campaigns_has_videos')) {
                if (!empty($this->id)) {
                    $sql = "DELETE FROM vast_campaigns_has_videos ";
                    $sql .= " WHERE videos_id = ?";
                    $global['lastQuery'] = $sql;
                    return sqlDAL::writeSql($sql, "i", [$videos_id]);
                }
            }
            return false;
        }

        private function removeFiles($filename)
        {
            if (empty($filename)) {
                return false;
            }
            global $global;
            $file = self::getStoragePath() . "original_{$filename}";
            $this->removeFilePath($file);

            $files = self::getStoragePath() . "{$filename}";
            $this->removeFilePath($files);
        }

        private function removeFilePath($filePath)
        {
            if (empty($filePath)) {
                return false;
            }
            // Streamlined for less coding space.
            $files = glob("{$filePath}*");
            foreach ($files as $file) {
                if (file_exists($file)) {
                    if (is_dir($file)) {
                        self::rrmdir($file);
                    } else {
                        @unlink($file);
                    }
                }
            }
        }

        private static function rrmdir($dir)
        {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object !== '.' && $object !== '..') {
                        if (is_dir($dir . '/' . $object)) {
                            self::rrmdir($dir . '/' . $object);
                        } else {
                            unlink($dir . '/' . $object);
                        }
                    }
                }
                rmdir($dir);
            }
        }

        public function setDescription($description)
        {
            global $global, $advancedCustom;

            if (empty($advancedCustom)) {
                $advancedCustom = AVideoPlugin::getDataObject('CustomizeAdvanced');
            }
            if (empty($advancedCustom->disableHTMLDescription)) {
                $articleObj = AVideoPlugin::getObjectData('Articles');
                if (empty($articleObj->allowAllTags)) {
                    $configPuri = HTMLPurifier_Config::createDefault();
                    $configPuri->set('Cache.SerializerPath', getCacheDir());
                    $purifier = new HTMLPurifier($configPuri);
                    if (empty($articleObj->allowAttributes)) {
                        $configPuri->set('HTML.AllowedAttributes', ['a.href', 'a.target', 'a.title', 'a.title', 'img.src', 'img.width', 'img.height', 'span.style']); // remove all attributes except a.href
                        $configPuri->set('Attr.AllowedFrameTargets', ['_blank']);
                        $configPuri->set('CSS.AllowedProperties', []); // remove all CSS
                    }
                    $configPuri->set('AutoFormat.RemoveEmpty', true); // remove empty elements
                    $pure = $purifier->purify($description);
                } else {
                    $pure = trim($description);
                }

                $parts = explode("<body>", $pure);
                if (!empty($parts[1])) {
                    $parts = explode("</body>", $parts[1]);
                }
                $new_description = $parts[0];
            } else {
                $new_description = strip_tags(br2nl($description));
            }
            AVideoPlugin::onVideoSetDescription($this->id, $this->description, $new_description);
            //$new_description= preg_replace('/[\xE2\x80\xAF\xBA\x96]/', '', $new_description);

            if (function_exists('mb_convert_encoding')) {
                $new_description = mb_convert_encoding($new_description, 'UTF-8', 'UTF-8');
            }

            $this->description = $new_description;
            //var_dump($this->description, $description, $parts);exit;
        }

        public function setCategories_id($categories_id)
        {
            if (!User::isAdmin() && !Category::userCanAddInCategory($categories_id)) {
                $reason = 'unknown';
                if (!empty($categories_id)) {
                    $users_id = User::getId();
                    if (!empty($users_id)) {
                        $cat = new Category($categories_id);
                        if (!empty($cat->getPrivate()) && $users_id == $cat->getUsers_id()) {
                            $reason = 'The category is private and belong to users_id ' . $cat->getUsers_id();
                        }
                    } else {
                        $reason = 'users_id is empty';
                    }
                } else {
                    $reason = 'categories_id is empty';
                }
                _error_log("The users_id {$users_id} cannot add in the categories_id {$categories_id} reason: ");
                return false;
            }

            // to update old cat as well when auto..
            if (!empty($this->categories_id)) {
                $this->old_categories_id = $this->categories_id;
            }
            AVideoPlugin::onVideoSetCategories_id($this->id, $this->categories_id, $categories_id);
            $this->categories_id = $categories_id;
        }

        public static function getCleanDuration($duration = "")
        {
            if (empty($duration)) {
                return "00:00:00";
            } else {
                $durationParts = explode(".", $duration);
            }
            if (empty($durationParts[0])) {
                return "00:00:00";
            } else {
                $duration = $durationParts[0];
                $durationParts = explode(':', $duration);
                if (count($durationParts) == 1) {
                    return '0:00:' . static::addZero($durationParts[0]);
                } elseif (count($durationParts) == 2) {
                    return '0:' . static::addZero($durationParts[0]) . ':' . static::addZero($durationParts[1]);
                }
                return $duration;
            }
        }

        private static function addZero($str)
        {
            if (intval($str) < 10) {
                return '0' . intval($str);
            }
            return $str;
        }

        public static function getItemPropDuration($duration = '')
        {
            $duration = static::getCleanDuration($duration);
            $parts = explode(':', $duration);
            $duration = 'PT' . intval($parts[0]) . 'H' . intval($parts[1]) . 'M' . intval($parts[2]) . 'S';
            if ($duration == "PT0H0M0S") {
                $duration = "PT0H0M1S";
            }
            return $duration;
        }

        public static function getItemDurationSeconds($duration = '')
        {
            if (!self::isValidDuration($duration)) {
                return 0;
            }
            $duration = static::getCleanDuration($duration);
            $parts = explode(':', $duration);
            return intval($parts[0] * 60 * 60) + intval($parts[1] * 60) + intval($parts[2]);
        }

        public static function getDurationFromFile($file)
        {
            global $global;
            // get movie duration HOURS:MM:SS.MICROSECONDS
            if (!file_exists($file)) {
                _error_log('{"status":"error", "msg":"getDurationFromFile ERROR, File (' . $file . ') Not Found"}');
                return "EE:EE:EE";
            }
            // Initialize getID3 engine
            $getID3 = new getID3();
            // Analyze file and store returned data in $ThisFileInfo
            $ThisFileInfo = $getID3->analyze($file);
            return static::getCleanDuration(@$ThisFileInfo['playtime_string']);
        }

        public static function getResolution($file)
        {
            global $videogetResolution, $global;
            if (!isset($videogetResolution)) {
                $videogetResolution = [];
            }
            if (isset($videogetResolution[$file])) {
                return $videogetResolution[$file];
            }
            $videogetResolution[$file] = 0;
            if (
                AVideoPlugin::isEnabledByName("Blackblaze_B2") ||
                AVideoPlugin::isEnabledByName("AWS_S3") ||
                AVideoPlugin::isEnabledByName("FTP_Storage") ||
                AVideoPlugin::isEnabledByName("YPTStorage") || !file_exists($file)
            ) {
                return 0;
            }
            global $global;
            if (preg_match("/.m3u8$/i", $file) && AVideoPlugin::isEnabledByName('VideoHLS') && method_exists(new VideoHLS(), 'getHLSHigestResolutionFromFile')) {
                $videogetResolution[$file] = VideoHLS::getHLSHigestResolutionFromFile($file);
            } else if (empty($global['disableVideoTags'])) {
                $getID3 = new getID3();
                $ThisFileInfo = $getID3->analyze($file);
                $videogetResolution[$file] = intval(@$ThisFileInfo['video']['resolution_y']);
            }
            return $videogetResolution[$file];
        }

        public static function getHLSDurationFromFile($file)
        {
            $plugin = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
            if (empty($plugin)) {
                return 0;
            }
            return VideoHLS::getHLSDurationFromFile($file);
        }

        public function updateHLSDurationIfNeed()
        {
            $plugin = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
            if (empty($plugin)) {
                return false;
            }
            return VideoHLS::updateHLSDurationIfNeed($this);
        }

        public function updateDurationIfNeed($fileExtension = ".mp4")
        {
            global $global;
            $source = self::getSourceFile($this->filename, $fileExtension, true);
            $file = $source['path'];

            if (!empty($this->id) && !self::isValidDuration($this->duration) && file_exists($file)) {
                $this->duration = Video::getDurationFromFile($file);
                _error_log("Duration Updated: " . json_encode($this));

                $sql = "UPDATE videos SET duration = ?, modified = now() WHERE id = ?";
                $res = sqlDAL::writeSql($sql, "si", [$this->duration, $this->id]);
                return $this->id;
            } else {
                $reason = array();
                if (empty($this->id)) {
                    $reason[] = 'empty id';
                }
                if (self::isValidDuration($this->duration)) {
                    $reason[] = 'duration is valid ' . $this->duration;
                }
                if (!file_exists($file)) {
                    $reason[] = 'file not exists ' . $file;
                }
                _error_log("Do not need update duration: " . implode(', ', $reason));
                return false;
            }
        }

        public function getFilename()
        {
            return $this->filename;
        }

        /**
         * return string
         */
        public function getStatus()
        {
            return $this->status;
        }

        public function getId()
        {
            return $this->id;
        }

        public function getVideoDownloadedLink()
        {
            return $this->videoDownloadedLink;
        }

        public function setVideoDownloadedLink($videoDownloadedLink)
        {
            AVideoPlugin::onVideoSetVideoDownloadedLink($this->id, $this->videoDownloadedLink, $videoDownloadedLink);
            $this->videoDownloadedLink = $videoDownloadedLink;
        }

        public function userCanManageVideo()
        {
            global $advancedCustomUser;
            if (Permissions::canAdminVideos()) {
                return true;
            }
            if (empty($this->users_id) || !User::canUpload()) {
                return false;
            }

            // if you're not admin you can only manage your videos
            $users_id = [$this->users_id, $this->users_id_company];
            if ($advancedCustomUser->userCanChangeVideoOwner) {
                $video = new Video("", "", $this->id); // query again to make sure the user is not changing the owner
                $users_id = [$video->getUsers_id(), $video->getUsers_id_company()];
            }
            //var_dump(User::getId(), $users_id, $video, $this);
            if (!in_array(User::getId(), $users_id)) {
                return false;
            }
            return true;
        }

        public function getVideoGroups()
        {
            return $this->videoGroups;
        }

        public function setVideoGroups($userGroups)
        {
            global $_getVideosAndCategoriesUserGroups;
            if (is_array($userGroups)) {
                AVideoPlugin::onVideoSetVideoGroups($this->id, $this->videoGroups, $userGroups);
                $this->videoGroups = $userGroups;
                unset($_getVideosAndCategoriesUserGroups[$this->id]);
            }
        }

        /**
         *
         * @param string $user_id
         * text
         * label Default Primary Success Info Warning Danger
         */
        public static function getTags($video_id, $type = "")
        {
            global $advancedCustom, $videos_getTags;

            if (empty($videos_getTags)) {
                $videos_getTags = [];
            }
            $name = "{$video_id}_{$type}";
            if (!empty($videos_getTags[$name])) {
                return $videos_getTags[$name];
            }

            $videos_getTags[$name] = self::getTags_($video_id, $type);
            return $videos_getTags[$name];
        }

        public static function getTagsHTMLLabelIfEnable($videos_id)
        {
            global $objGallery;
            $return = '<!-- Gallery->showTags not enabled videos_id ' . $videos_id . ' -->';
            if (empty($objGallery)) {
                $objGallery = AVideoPlugin::getObjectData("Gallery");
            }
            if (!empty($objGallery->showTags)) {
                $return = implode('', Video::getTagsHTMLLabelArray($videos_id));
            }
            return $return;
        }

        public static function getTagsHTMLLabelArray($video_id)
        {
            global $_getTagsHTMLLabelArray;

            if (!isset($_getTagsHTMLLabelArray)) {
                $_getTagsHTMLLabelArray = [];
            }

            if (isset($_getTagsHTMLLabelArray[$video_id])) {
                return $_getTagsHTMLLabelArray[$video_id];
            }

            $tags = Video::getTags($video_id);
            $_getTagsHTMLLabelArray[$video_id] = [];
            foreach ($tags as $value2) {
                if (empty($value2->label) || empty($value2->text)) {
                    continue;
                }
                $valid_tags = [__("Paid Content"), __("Group"), __("Plugin"), __("Rating")];
                if (!in_array($value2->label, $valid_tags)) {
                    continue;
                }

                $tooltip = '';
                if (!empty($value2->tooltip)) {
                    $icon = $value2->text;
                    if (!empty($value2->tooltipIcon)) {
                        $icon = $value2->tooltipIcon;
                    }
                    $tooltip = '  data-toggle="tooltip" title="' . htmlentities($icon . ' ' . $value2->tooltip) . '" data-html="true"';
                }
                $class = preg_replace('/[^a-z0-9]/i', '_', $value2->label);
                $_getTagsHTMLLabelArray[$video_id][] = '<span class="label label-' . $value2->type . ' videoLabel' . $class . '" ' . $tooltip . '>' . $value2->text . '</span>';
            }
            return $_getTagsHTMLLabelArray[$video_id];
        }
        static function updatePrivacyInfo($videos_id){
            $v = new Video('', '', $videos_id);
            $privacyInfo = self::_getPrivacyInfo($videos_id);
            return $v->setPrivacyInfo($privacyInfo);
        }
        static function _getPrivacyInfo($videos_id){
            global $advancedCustomUser, $_getPrivacyInfo;
            if(!isset($_getPrivacyInfo)){
                $_getPrivacyInfo = array();
            }else{
                if(!empty($_getPrivacyInfo[$videos_id])){
                    return $_getPrivacyInfo[$videos_id];
                }
            }
            $responseFields = array(
                'fans_only',
                'password_protectd',
                'only_for_paid',
                'pay_per_view',
                'user_groups',
            );
            $response = array('videos_id'=>$videos_id);
            $video = new Video("", "", $videos_id);
            $ppv = AVideoPlugin::getObjectDataIfEnabled("PayPerView");
            $response['fans_only'] = $video->getStatus() === self::$statusFansOnly;
            if($response['fans_only'] && AVideoPlugin::isEnabled("FansSubscriptions")){
                $response['fans_only_info'] = FansSubscriptions::getPlansFromUsersID($video->getUsers_id());
            }
            $response['password_protectd'] = $advancedCustomUser->userCanProtectVideosWithPassword && !empty($video->getVideo_password());
            $response['only_for_paid'] = !empty($video->getOnly_for_paid());
            $response['pay_per_view'] = $ppv && PayPerView::isVideoPayPerView($videos_id);
            if($response['pay_per_view'] && AVideoPlugin::isEnabled("PayPerView")){
                $response['pay_per_view_info'] = PayPerView::getAllPlansFromVideo($videos_id);
            }
            $response['user_groups'] = !Video::isPublic($videos_id);
            if($response['user_groups']){
                $response['user_groups_info'] = Video::getUserGroups($videos_id);
            }
            $response['isPrivate'] = false;
            foreach ($responseFields as $value) {
                if($response[$value]){
                    $response['isPrivate'] = true;
                    break;
                }
            }

            $_getPrivacyInfo[$videos_id] = $response;
            return $response;
        }

        public static function getTags_($video_id, $type = "")
        {
            global $advancedCustom, $advancedCustomUser, $getTags_;
            $tolerance = 0.5;
            if (!isset($getTags_)) {
                $getTags_ = [];
            }
            $index = "{$video_id}_{$type}";
            if (!empty($getTags_[$index])) {
                return $getTags_[$index];
            }

            TimeLogStart("video::getTags_ $video_id, $type");
            if (empty($advancedCustomUser)) {
                $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
            }
            if (empty($advancedCustom)) {
                $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
            }
            $currentPage = getCurrentPage();
            $rowCount = getRowCount();
            $_REQUEST['current'] = 1;
            $_REQUEST['rowCount'] = 1000;

            TimeLogStart("video::getTags_ new Video $video_id, $type");
            $video = new Video("", "", $video_id);
            $tags = [];
            if (empty($type) || $type === "paid") {
                $objTag = new stdClass();
                $objTag->label = __("Paid Content");
                if (!empty($advancedCustom->paidOnlyShowLabels)) {
                    if (!empty($video->getOnly_for_paid())) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-lock"></i>';
                        $objTag->tooltip = $advancedCustom->paidOnlyLabel;
                    } else {
                        /*
                          $objTag->type = "success";
                          $objTag->text = '<i class="fas fa-lock-open"></i>';
                          $objTag->tooltip = $advancedCustom->paidOnlyFreeLabel;
                         */
                    }
                } else {
                    $ppv = AVideoPlugin::getObjectDataIfEnabled("PayPerView");
                    if ($video->getStatus() === self::$statusFansOnly) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-star" ></i>';
                        $objTag->tooltip = __("Fans Only");
                    } elseif ($advancedCustomUser->userCanProtectVideosWithPassword && !empty($video->getVideo_password())) {
                        $objTag->type = "danger";
                        $objTag->text = '<i class="fas fa-lock" ></i>';
                        $objTag->tooltip = __("Password Protected");
                    } elseif (!empty($video->getOnly_for_paid())) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-lock"></i>';
                        $objTag->tooltip = $advancedCustom->paidOnlyLabel;
                    } elseif ($ppv && PayPerView::isVideoPayPerView($video_id)) {
                        if (!empty($ppv->showPPVLabel)) {
                            $objTag->type = "warning";
                            $objTag->text = "PPV";
                            $objTag->tooltip = __("Pay Per View");
                        } else {
                            $objTag->type = "warning";
                            $objTag->text = '<i class="fas fa-lock"></i>';
                            $objTag->tooltip = __("Private");
                        }
                    } elseif (!Video::isPublic($video_id)) {
                        $objTag->type = "warning";
                        $objTag->text = '<i class="fas fa-lock"></i>';
                        $objTag->tooltip = __("Private");
                    } else {
                        /*
                          $objTag->type = "success";
                          $objTag->text = '<i class="fas fa-lock-open"></i>';
                          $objTag->tooltip = $advancedCustom->paidOnlyFreeLabel;
                         */
                    }
                }
                $tags[] = $objTag;
                $objTag = new stdClass();
            }
            TimeLogEnd("video::getTags_ new Video $video_id, $type", __LINE__, $tolerance);

            TimeLogStart("video::getTags_ status $video_id, $type");
            if (empty($type) || $type === "status") {
                $objTag = new stdClass();
                $objTag->label = __("Status");
                /**
                 * @var string $status
                 */
                $status = $video->getStatus();
                $objTag->text = __(Video::$statusDesc[$status]);
                switch ($status) {
                    case Video::$statusActive:
                        $objTag->type = "success";
                        break;
                    case Video::$statusActiveAndEncoding:
                        $objTag->type = "success";
                        break;
                    case Video::$statusInactive:
                        $objTag->type = "warning";
                        break;
                    case Video::$statusEncoding:
                        $objTag->type = "info";
                        break;
                    case Video::$statusDownloading:
                        $objTag->type = "info";
                        break;
                    case Video::$statusUnlisted:
                        $objTag->type = "info";
                        break;
                    case Video::$statusUnlistedButSearchable:
                        $objTag->type = "info";
                        break;
                    case Video::$statusRecording:
                        $objTag->type = "danger isRecording isRecordingIcon";
                        break;
                    default:
                        $objTag->type = "danger";
                        break;
                }
                $objTag->text = $objTag->text;
                $tags[] = $objTag;
                $objTag = new stdClass();
            }
            TimeLogEnd("video::getTags_ status $video_id, $type", __LINE__, $tolerance);

            TimeLogStart("video::getTags_ userGroups $video_id, $type");
            if (empty($type) || $type === "userGroups") {
                $groups = UserGroups::getVideosAndCategoriesUserGroups($video_id);
                $objTag = new stdClass();
                $objTag->label = __("Group");
                if (empty($groups)) {
                    $status = $video->getStatus();
                    if ($status == 'u') {
                        $objTag->type = "info";
                        $objTag->text = '<i class="far fa-eye-slash"></i>';
                        $objTag->tooltip = __("Unlisted");
                        $tags[] = $objTag;
                        $objTag = new stdClass();
                    } else {
                        //$objTag->type = "success";
                        //$objTag->text = __("Public");
                    }
                } else {
                    $groupNames = [];
                    foreach ($groups as $value) {
                        $groupNames[] = $value['group_name'];
                    }
                    $totalUG = count($groupNames);
                    if (!empty($totalUG)) {
                        $objTag = new stdClass();
                        $objTag->label = __("Group");
                        $objTag->type = "info";
                        if ($totalUG > 1) {
                            $objTag->text = '<i class="fas fa-users"></i> ' . ($totalUG);
                        } else {
                            $objTag->text = '<i class="fas fa-users"></i>';
                        }
                        $objTag->tooltip = implode(', ', $groupNames);
                        $tags[] = $objTag;
                        $objTag = new stdClass();
                    }
                }
            }
            TimeLogEnd("video::getTags_ userGroups $video_id, $type", __LINE__, $tolerance);

            TimeLogStart("video::getTags_ category $video_id, $type");
            if (empty($type) || $type === "category") {
                require_once 'category.php';
                $sort = null;
                if (!empty($_POST['sort']['title'])) {
                    $sort = $_POST['sort'];
                    unset($_POST['sort']);
                }
                $category = Category::getCategory($video->getCategories_id());
                if (!empty($sort)) {
                    $_POST['sort'] = $sort;
                }
                $objTag = new stdClass();
                $objTag->label = __("Category");
                if (!empty($category)) {
                    $objTag->type = "default";
                    $objTag->text = $category['name'];
                    $tags[] = $objTag;
                    $objTag = new stdClass();
                }
            }
            TimeLogEnd("video::getTags_ category $video_id, $type", __LINE__, $tolerance);

            TimeLogStart("video::getTags_ source $video_id, $type");
            if (empty($type) || $type === "source") {
                $url = $video->getVideoDownloadedLink();
                if (!empty($url)) {
                    $parse = parse_url($url);
                    $objTag = new stdClass();
                    $objTag->label = __("Source");
                    if (!empty($parse['host'])) {
                        $objTag->type = "danger";
                        $objTag->text = $parse['host'];
                        $tags[] = $objTag;
                        $objTag = new stdClass();
                    } else {
                        $objTag->type = "info";
                        $objTag->text = __("Local File");
                        $tags[] = $objTag;
                        $objTag = new stdClass();
                    }
                }
            }
            TimeLogEnd("video::getTags_ source $video_id, $type", __LINE__, $tolerance);

            if (!empty($video->getRrating())) {
                $rating = $video->getRrating();
                $objTag = new stdClass();
                $objTag->label = __("Rating");
                $objTag->type = "default";
                $objTag->text = strtoupper($rating);
                $objTag->tooltip = __(Video::$rratingOptionsText[$rating]);
                $objTag->tooltipIcon = "[{$objTag->text}]";
                $tags[] = $objTag;
                //var_dump($tags);exit;
            }

            TimeLogStart("video::getTags_ AVideoPlugin::getVideoTags $video_id", __LINE__, $tolerance);
            $array2 = AVideoPlugin::getVideoTags($video_id);
            if (is_array($array2)) {
                $tags = array_merge($tags, $array2);
            }
            TimeLogEnd("video::getTags_ AVideoPlugin::getVideoTags $video_id", __LINE__, $tolerance);
            //var_dump($tags);

            TimeLogEnd("video::getTags_ $video_id, $type", __LINE__, $tolerance * 2);
            $_REQUEST['current'] = $currentPage;
            $_REQUEST['rowCount'] = $rowCount;
            $getTags_[$index] = $tags;
            return $tags;
        }

        public static function deleteTagsAsync($video_id)
        {
            global $global;
            return false;
            if (empty($video_id)) {
                return false;
            }

            $name = "getVideoTags{$video_id}";

            if (!class_exists('Cache')) {
                AVideoPlugin::loadPlugin('Cache');
            }
            Cache::deleteCache($name);
            _session_start();
            unset($_SESSION['getVideoTags'][$video_id]);
            $path = getCacheDir() . "getTagsAsync/";
            if (!is_dir($path)) {
                return false;
            }

            $cacheFileName = "{$path}_{$video_id}_";

            $files = glob("{$cacheFileName}*");
            foreach ($files as $file) {
                unlink($file);
            }
        }

        public static function getTagsAsync($video_id, $type = "video")
        {
            global $global, $advancedCustom;
            $path = getCacheDir() . "getTagsAsync/";
            make_path($path);
            $cacheFileName = "{$path}_{$video_id}_{$type}";

            $return = [];
            if (!file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return [];
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getTags_($video_id, $type);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = _json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > 300) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getTags.php' '$video_id' '$type' '{$cacheFileName}'");
                //_error_log("getTags: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return (array) $return;
        }

        public function getCategories_id()
        {
            return $this->categories_id;
        }

        public function getType()
        {
            return $this->type;
        }

        public static function fixCleanTitle($clean_title, $count, $videoId, $original_title = "")
        {
            global $global;
            $clean_title = safeString($clean_title);
            if (empty($original_title)) {
                $original_title = $clean_title;
            }
            $values = array();
            $sql = "SELECT * FROM videos WHERE clean_title = ? ";
            $formats = "s";
            $values[] = $clean_title;
            if (!empty($videoId)) {
                $sql .= " AND id != ? ";
                $formats .= "i";
                $values[] = $videoId;
            }
            $sql .= " LIMIT 1";
            $res = sqlDAL::readSql($sql,$formats, $values, true);
            $cleanTitleExists = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($cleanTitleExists != false) {
                return self::fixCleanTitle($original_title . "-" . $count, $count + 1, $videoId, $original_title);
            }
            return $clean_title;
        }

        /**
         *
         * @global array $global
         * @param string $videos_id
         * @param string $users_id if is empty will use the logged user
         * @return boolean
         */
        public static function isOwner($videos_id, $users_id = 0, $checkAffiliate = true)
        {
            global $global;
            if (empty($users_id)) {
                $users_id = User::getId();
            }
            if (empty($users_id)) {
                return false;
            }

            $video = self::getVideoLight($videos_id, true);
            if ($video) {
                if ($video['users_id'] == $users_id || ($checkAffiliate && $video['users_id_company'] == $users_id)) {
                    return true;
                }
            }
            return false;
        }

        public static function isOwnerFromCleanTitle($clean_title, $users_id = 0)
        {
            global $global;
            $video = self::getVideoFromCleanTitle($clean_title);
            return self::isOwner($video['id'], $users_id);
        }

        /**
         *
         * @global array $global
         * @param string $videos_id
         * @param string $users_id if is empty will use the logged user
         * @return boolean
         */
        public static function getOwner($videos_id)
        {
            global $global;
            $sql = "SELECT users_id FROM videos WHERE id = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "i", [$videos_id]);
            $videoRow = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res) {
                if ($videoRow != false) {
                    return $videoRow['users_id'];
                }
            } else {
                $videos = false;
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return false;
        }

        /**
         *
         * @param string $videos_id
         * @param string $users_id if is empty will use the logged user
         * @return boolean
         */
        public static function canEdit($videos_id, $users_id = 0)
        {
            if (empty($videos_id)) {
                return false;
            }
            if (empty($users_id)) {
                $users_id = User::getId();
                if (empty($users_id)) {
                    return false;
                }
            }
            $user = new User($users_id);
            if (empty($user)) {
                return false;
            }

            if ($user->getIsAdmin()) {
                return true;
            }

            if (Permissions::canAdminVideos()) {
                return true;
            }

            return self::isOwner($videos_id, $users_id);
        }

        public static function getRandom($excludeVideoId = false, $status = "viewable")
        {
            return static::getVideo("", $status, false, $excludeVideoId);
        }

        public static function getVideoQueryFileter()
        {
            global $global;
            $sql = '';
            if (!empty($_GET['playlist_id'])) {
                require_once $global['systemRootPath'] . 'objects/playlist.php';
                $ids = PlayList::getVideosIdFromPlaylist($_GET['playlist_id']);
                if (!empty($ids)) {
                    $sql .= " AND v.id IN (" . implode(",", $ids) . ") ";
                }
            }
            return $sql;
        }

        public function getTitle()
        {
            return $this->title;
        }

        public function getClean_title()
        {
            return $this->clean_title;
        }

        public function getDescription()
        {
            return $this->description;
        }

        public function getExistingVideoFile()
        {
            $source = self::getHigestResolutionVideoMP4Source($this->getFilename(), true);
            if (empty($source)) {
                _error_log("getExistingVideoFile:: resources are empty " . $this->getFilename());
                return false;
            }
            $size = filesize($source['path']);
            if ($size <= 20) { // it is a dummy file
                $url = $source['url'];
                _error_log("getExistingVideoFile:: dummy file, download it " . json_encode($source));
                $filename = getTmpDir("getExistingVideoFile") . md5($url);
                copyfile_chunked($url, $filename);
                wget($url, $filename);
                return $filename;
            }
            return $source['path'];
        }

        public function getTrailer1()
        {
            return $this->trailer1;
        }

        public function getTrailer2()
        {
            return $this->trailer2;
        }

        public function getTrailer3()
        {
            return $this->trailer3;
        }

        public function getRate()
        {
            return $this->rate;
        }

        public function setTrailer1($trailer1)
        {
            if (filter_var($trailer1, FILTER_VALIDATE_URL)) {
                $new_trailer1 = $trailer1;
            } else {
                $new_trailer1 = '';
            }
            AVideoPlugin::onVideoSetTrailer1($this->id, $this->trailer1, $new_trailer1);
            $this->trailer1 = $new_trailer1;
        }

        public function setTrailer2($trailer2)
        {
            if (filter_var($trailer2, FILTER_VALIDATE_URL)) {
                $new_trailer2 = $trailer2;
            } else {
                $new_trailer2 = '';
            }
            AVideoPlugin::onVideoSetTrailer2($this->id, $this->trailer2, $new_trailer2);
            $this->trailer2 = $new_trailer2;
        }

        public function setTrailer3($trailer3)
        {
            if (filter_var($trailer3, FILTER_VALIDATE_URL)) {
                $new_trailer3 = $trailer3;
            } else {
                $new_trailer3 = '';
            }
            AVideoPlugin::onVideoSetTrailer3($this->id, $this->trailer3, $new_trailer3);
            $this->trailer3 = $new_trailer3;
        }

        public function setRate($rate)
        {
            AVideoPlugin::onVideoSetRate($this->id, $this->rate, floatval($rate));
            $this->rate = floatval($rate);
        }

        public function getYoutubeId()
        {
            return $this->youtubeId;
        }

        public function setYoutubeId($youtubeId)
        {
            AVideoPlugin::onVideoSetYoutubeId($this->id, $this->youtubeId, $youtubeId);
            $this->youtubeId = $youtubeId;
        }

        public function setTitle($title)
        {
            if ($title === "Video automatically booked" && !empty($this->title)) {
                _error_log("Video::setTitle($title) Title not set ");
                return false;
            }
            $originalTitle = $title;
            $title = safeString($title);
            $title = str_replace(['"', "\\"], ["''", ""], $title);
            if (strlen($title) > 190) {
                $title = _substr($title, 0, 187) . '...';
                _error_log("Video::setTitle($originalTitle) Title resized {$title} ");
            }
            AVideoPlugin::onVideoSetTitle($this->id, $originalTitle, $title);
            if (!empty($new_title)) {
                _error_log("Video::setTitle($originalTitle) Title 1 set to [" . json_encode($new_title) . "] ");
                $this->title = $new_title;
            } else {
                _error_log("Video::setTitle($originalTitle) Title 2 set to [" . json_encode($title) . "] ");
                $this->title = $title;
            }
        }

        public function setFilename($filename, $force = false)
        {
            $filename = safeString($filename, true);
            if ($force || empty($this->filename)) {
                AVideoPlugin::onVideoSetFilename($this->id, $this->filename, $filename, $force);
                $this->filename = $filename;
            } else {
                _error_log('setFilename: fail ' . $filename . " {$this->id}");
            }
            return $this->filename;
        }

        public function getNext_videos_id()
        {
            return $this->next_videos_id;
        }

        public function setNext_videos_id($next_videos_id)
        {
            AVideoPlugin::onVideoSetNext_videos_id($this->id, $this->next_videos_id, $next_videos_id);
            $this->next_videos_id = $next_videos_id;
        }

        public function queue($types = [])
        {
            global $config, $global;

            if (!User::canUpload()) {
                return false;
            }

            $obj = new stdClass();
            $obj->error = true;

            $target = $config->getEncoderURL() . "queue";
            $postFields = [
                'user' => User::getUserName(),
                'pass' => User::getUserPass(),
                'fileURI' => $global['webSiteRootURL'] . "videos/original_{$this->getFilename()}",
                'filename' => $this->getFilename(),
                'videos_id' => $this->getId(),
                "notifyURL" => "{$global['webSiteRootURL']}",
            ];

            if (empty($types) && AVideoPlugin::isEnabledByName("VideoHLS")) {
                $postFields['inputAutoHLS'] = 1;
            } elseif (!empty($types)) {
                $postFields = array_merge($postFields, $types);
            }

            _error_log("SEND To QUEUE: ($target) " . json_encode($postFields));

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $target,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ));

            $r = curl_exec($curl);
            $obj->response = $r;

            if ($errno = curl_errno($curl)) {
                $error_message = curl_strerror($errno);
                $obj->msg = "cURL error ({$errno}):\n {$error_message}";
            } else {
                $obj->error = false;
            }

            _error_log("QUEUE CURL: ($target) " . json_encode($obj));
            curl_close($curl);
            Configuration::deleteEncoderURLCache();
            return $obj;
        }


        public function getVideoLink()
        {
            return $this->videoLink;
        }

        public function setVideoLink($videoLink)
        {
            AVideoPlugin::onVideoSetVideoLink($this->id, $this->videoLink, $videoLink);
            $this->videoLink = fixURL($videoLink);
        }

        public function getCan_download()
        {
            return $this->can_download;
        }

        public function getCan_share()
        {
            return $this->can_share;
        }

        public function setCan_download($can_download)
        {
            $new_can_download = (empty($can_download) || $can_download === "false") ? 0 : 1;
            AVideoPlugin::onVideoSetCan_download($this->id, $this->can_download, $new_can_download);
            $this->can_download = $new_can_download;
        }

        public function setCan_share($can_share)
        {
            $new_can_share = (empty($can_share) || $can_share === "false") ? 0 : 1;
            AVideoPlugin::onVideoSetCan_share($this->id, $this->can_share, $new_can_share);
            $this->can_share = $new_can_share;
        }

        public function getOnly_for_paid()
        {
            return $this->only_for_paid;
        }

        public function setOnly_for_paid($only_for_paid)
        {
            $new_only_for_paid = (empty($only_for_paid) || $only_for_paid === "false") ? 0 : 1;
            AVideoPlugin::onVideoSetOnly_for_paid($this->id, $this->only_for_paid, $new_only_for_paid);
            $this->only_for_paid = $new_only_for_paid;
        }

        /**
         *
         * @param string $filename
         * @param string $type
         * @return string .jpg .gif .webp _thumbs.jpg _Low.mp4 _SD.mp4 _HD.mp4
         */
        public static function getSourceFile($filename, $type = ".jpg", $includeS3 = false)
        {
            global $global, $advancedCustom, $videosPaths, $VideoGetSourceFile;
            //if (!isValidFormats($type)) {
            //return array();
            //}

            $timeLog1Limit = 0.2;
            $timeLog1 = "getSourceFile($filename, $type, $includeS3)";
            TimeLogStart($timeLog1);

            //self::_moveSourceFilesToDir($filename);
            $paths = self::getPaths($filename);
            if ($type == '_thumbsSmallV2.jpg' && empty($advancedCustom->usePreloadLowResolutionImages)) {
                return ['path' => $global['systemRootPath'] . 'view/img/loading-gif.png', 'url' => getCDN() . 'view/img/loading-gif.png'];
            }

            TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
            $cacheName = md5($filename . $type . $includeS3);
            if (isset($VideoGetSourceFile[$cacheName]) && is_array($VideoGetSourceFile[$cacheName])) {
                if (!preg_match("/token=/", $VideoGetSourceFile[$cacheName]['url'])) {
                    return $VideoGetSourceFile[$cacheName];
                }
            }

            // check if there is a webp image
            if ($type === '.gif' && (empty($_SERVER['HTTP_USER_AGENT']) || get_browser_name($_SERVER['HTTP_USER_AGENT']) !== 'Safari')) {
                $path = "{$paths['path']}{$filename}.webp";
                if (file_exists($path)) {
                    $type = ".webp";
                }
            }
            TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
            if (empty($videosPaths[$filename][$type][intval($includeS3)])) {
                $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
                $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
                $cdn = AVideoPlugin::loadPluginIfEnabled('CDN');
                $yptStorage = AVideoPlugin::loadPluginIfEnabled('YPTStorage');
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                if (!empty($cdn)) {
                    $cdn_obj = $cdn->getDataObject();
                    if (!empty($cdn_obj->enable_storage)) {
                        $includeS3 = true;
                    }
                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                } elseif (!empty($aws_s3)) {
                    $aws_s3_obj = $aws_s3->getDataObject();
                    if (!empty($aws_s3_obj->useS3DirectLink)) {
                        $includeS3 = true;
                    }
                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                } elseif (!empty($bb_b2)) {
                    $bb_b2_obj = $bb_b2->getDataObject();
                    if (!empty($bb_b2_obj->useDirectLink)) {
                        $includeS3 = true;
                    }
                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                } elseif (!empty($ftp)) {
                    $includeS3 = true;
                }
                $token = '';
                $secure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                if ((preg_match("/.*\\.mp3$/", $type) || preg_match("/.*\\.mp4$/", $type) || preg_match("/.*\\.webm$/", $type) || $type == ".m3u8" || $type == ".pdf" || $type == ".zip")) {
                    $vars = [];
                    if (!empty($secure)) {
                        $vars[] = $secure->getToken($filename);
                    }
                    if (!empty($vars)) {
                        $token = "?" . implode("&", $vars);
                    }
                }

                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);

                $paths = self::getPaths($filename);

                $source = [];
                $source['path'] = $paths['path'] . "{$filename}{$type}";

                if ($type == ".m3u8") {
                    $source['path'] = self::getStoragePath() . "{$filename}/index{$type}";
                }
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                $cleanFileName = self::getCleanFilenameFromFile($filename);
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                $video = Video::getVideoFromFileNameLight($cleanFileName);
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                if (empty($video)) {
                    //_error_log("Video::getSourceFile($filename, $type, $includeS3) ERROR video not found ($cleanFileName)");
                    $VideoGetSourceFile[$cacheName] = false;
                    return false;
                }
                $canUseCDN = canUseCDN($video['id']);
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                $fsize = @filesize($source['path']);
                $isValidType = (preg_match("/.*\\.mp3$/", $type) || preg_match("/.*\\.mp4$/", $type) || preg_match("/.*\\.webm$/", $type) || $type == ".m3u8" || $type == ".pdf" || $type == ".zip");

                if (!empty($video['sites_id'])) {
                    $site = new Sites($video['sites_id']);
                }
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);

                if (!empty($cdn_obj->enable_storage) && $isValidType && $fsize < 20 && !empty($site) && (empty($yptStorage) || $site->getUrl() == 'url/')) {
                    if ($type == ".m3u8") {
                        $f = "{$filename}/index{$type}";
                    } else {
                        $f = "{$paths['relative']}{$filename}{$type}";
                    }
                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                    $source['url'] = CDNStorage::getURL($f) . "{$token}";
                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                    //$source['url'] = addQueryStringParameter($source['url'], 'cache', uniqid());
                    $source['url_noCDN'] = $source['url'];
                } elseif (!empty($yptStorage) && !empty($site) && $isValidType && $fsize < 20) {
                    $siteURL = getCDNOrURL($site->getUrl(), 'CDN_YPTStorage', $video['sites_id']);
                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                    $source['url'] = "{$siteURL}{$paths['relative']}{$filename}{$type}{$token}";
                    $source['url_noCDN'] = $site->getUrl() . "{$paths['relative']}{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = "{$siteURL}videos/{$filename}/index{$type}{$token}";
                        $source['url_noCDN'] = "{$global['webSiteRootURL']}videos/{$filename}/index{$type}{$token}";
                    }
                } elseif (!empty($advancedCustom->videosCDN) && $canUseCDN) {
                    $advancedCustom->videosCDN = addLastSlash($advancedCustom->videosCDN);
                    $source['url'] = "{$advancedCustom->videosCDN}{$paths['relative']}{$filename}{$type}{$token}";
                    $source['url_noCDN'] = "{$global['webSiteRootURL']}{$paths['relative']}{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = "{$advancedCustom->videosCDN}videos/{$filename}/index{$type}{$token}";
                        $source['url_noCDN'] = "{$global['webSiteRootURL']}videos/{$filename}/index{$type}{$token}";
                    }
                } else {
                    $source['url'] = getCDN() . "{$paths['relative']}{$filename}{$type}{$token}";
                    $source['url_noCDN'] = "{$global['webSiteRootURL']}{$paths['relative']}{$filename}{$type}{$token}";
                    if ($type == ".m3u8") {
                        $source['url'] = getCDN() . "videos/{$filename}/index{$type}{$token}";
                        $source['url_noCDN'] = "{$global['webSiteRootURL']}videos/{$filename}/index{$type}{$token}";
                    }
                }
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                /* need it because getDurationFromFile */
                if ($includeS3 && preg_match('/\.(mp4|webm|mp3|ogg|pdf|zip)$/i', $type)) {
                    if (file_exists($source['path']) && filesize($source['path']) < 1024) {
                        if (!empty($cdn_obj->enable_storage)) {
                            $source['url'] = CDNStorage::getURL("{$filename}{$type}");
                            $source['url_noCDN'] = $source['url'];
                            TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                        } elseif (!empty($aws_s3)) {
                            $source = $aws_s3->getAddress("{$filename}{$type}");
                            $source['url_noCDN'] = $source['url'];
                            $source['url'] = replaceCDNIfNeed($source['url'], 'CDN_S3');
                            TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                        } elseif (!empty($bb_b2)) {
                            $source = $bb_b2->getAddress("{$filename}{$type}");
                            $source['url_noCDN'] = $source['url'];
                            $source['url'] = replaceCDNIfNeed($source['url'], 'CDN_B2');
                            TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                        } elseif (!empty($ftp)) {
                            $source = $ftp->getAddress("{$filename}{$type}");
                            $source['url_noCDN'] = $source['url'];
                            $source['url'] = replaceCDNIfNeed($source['url'], 'CDN_FTP');
                            TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                        }
                    }
                }
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                if (!file_exists($source['path']) || ($type !== ".m3u8" && !is_dir($source['path']) && (filesize($source['path']) < 1000 && filesize($source['path']) != 10))) {
                    if (
                        $type !== "_thumbsV2.jpg" &&
                        $type !== "_thumbsSmallV2.jpg" &&
                        $type !== "_portrait_thumbsV2.jpg" &&
                        $type !== "_portrait_thumbsSmallV2.jpg" &&
                        $type !== "_thumbsV2_jpg.webp"
                    ) {
                        $VideoGetSourceFile[$cacheName] = ['path' => false, 'url' => false];
                        //if($type=='.jpg'){echo '----'.PHP_EOL;var_dump($type, $source);echo '----'.PHP_EOL;};
                        //echo PHP_EOL.'---'.PHP_EOL;var_dump($source, $type, !file_exists($source['path']), ($type !== ".m3u8" && !is_dir($source['path']) && (filesize($source['path']) < 1000 && filesize($source['path']) != 10)));echo PHP_EOL.'+++'.PHP_EOL;
                        return $VideoGetSourceFile[$cacheName];
                    }
                }

                $videosPaths[$filename][$type][intval($includeS3)] = $source;
            } else {
                $source = $videosPaths[$filename][$type][intval($includeS3)];
            }
            if (substr($type, -4) === ".jpg" || substr($type, -4) === ".png" || substr($type, -4) === ".gif" || substr($type, -4) === ".webp") {
                $x = uniqid();
                if (file_exists($source['path'])) {
                    $x = filemtime($source['path']) . filectime($source['path']);
                } elseif (!empty($video)) {
                    $x = strtotime($video['modified']);
                }
                $source['url'] = addQueryStringParameter($source['url'], 'cache', $x);
                $source['url_noCDN'] = addQueryStringParameter($source['url_noCDN'], 'cache', $x);
            }

            $source = AVideoPlugin::modifyURL($source);

            //var_dump($type, $source);exit;
            //ObjectYPT::setCache($name, $source);
            $VideoGetSourceFile[$cacheName] = $source;
            return $VideoGetSourceFile[$cacheName];
        }

        private static function _moveSourceFilesToDir($videoFilename)
        {
            $videoFilename = self::getCleanFilenameFromFile($videoFilename);
            if (preg_match('/^(hd|low|sd|(res[0-9]{3,4}))$/', $videoFilename)) {
                return false;
            }
            $paths = self::getPaths($videoFilename);
            $lock = "{$paths['path']}.move_v1.lock";
            if (file_exists($lock)) {
                return true;
            }
            $videosDir = self::getStoragePath();
            make_path($paths['path']);
            $files = _glob($videosDir, '/' . $videoFilename . '[._][a-z0-9_]+/i');
            //var_dump($paths['path'], is_dir($paths['path']), $files);exit;
            foreach ($files as $oldname) {
                if (is_dir($oldname)) {
                    continue;
                }
                $newname = str_replace($videosDir, $paths['path'], $oldname);
                rename($oldname, $newname);
            }
            return file_put_contents($lock, time());
        }

        public static function getPaths($videoFilename, $createDir = false)
        {
            global $global, $__getPaths;
            if (!isset($__getPaths)) {
                $__getPaths = [];
            }
            if (empty($videoFilename)) {
                return array();
            }
            if (!empty($__getPaths[$videoFilename])) {
                return $__getPaths[$videoFilename];
            }
            $cleanVideoFilename = self::getCleanFilenameFromFile($videoFilename);
            //var_dump($videoFilename, $path,$cleanVideoFilename);
            $videosDir = self::getStoragePath();

            $path = addLastSlash("{$videosDir}{$cleanVideoFilename}");

            $path = fixPath($path);
            if ($createDir) {
                make_path(addLastSlash($path));
            }
            $relative = addLastSlash("videos/{$cleanVideoFilename}");
            if (preg_match('/\.vtt$/', $videoFilename)) {
                $url = $global['webSiteRootURL'] . "{$relative}";
            } else {
                $url = getCDN() . "{$relative}";
            }
            $__getPaths[$videoFilename] = ['filename' => $cleanVideoFilename, 'path' => $path, 'url' => $url, 'relative' => $relative];
            return $__getPaths[$videoFilename];
        }

        public static function getPathToFile($videoFilename, $createDir = false)
        {
            $videosDir = self::getStoragePath();
            $videoFilename = str_replace($videosDir, '', $videoFilename);
            $paths = Video::getPaths($videoFilename, $createDir);
            //var_dump($paths);
            if (preg_match('/index(_offline)?.(m3u8|mp4)$/', $videoFilename)) {
                $paths['path'] = rtrim($paths['path'], DIRECTORY_SEPARATOR);
                $paths['path'] = rtrim($paths['path'], '/');
                $videoFilename = str_replace($paths['relative'], '', $videoFilename);
                $videoFilename = str_replace($paths['filename'], '', $videoFilename);
            }
            $newPath = addLastSlash($paths['path']) . "{$videoFilename}";
            //var_dump($newPath);
            return $newPath;
        }

        public static function getURLToFile($videoFilename, $createDir = false)
        {
            $videosDir = self::getStoragePath();
            $videoFilename = str_replace($videosDir, '', $videoFilename);
            $paths = Video::getPaths($videoFilename, $createDir);
            $parts = explode('/', $videoFilename);
            if (!empty($parts[1]) && $parts[1] == 'index.m3u8') {
                $videoFilename = $parts[1];
            }
            return "{$paths['url']}{$videoFilename}";
        }

        public static function getURLToFileIfExists($videoFilename)
        {
            $paths = Video::getPaths($videoFilename);
            if (!file_exists("{$paths['path']}{$videoFilename}")) {
                return false;
            }
            return "{$paths['url']}{$videoFilename}";
        }

        public static function getNewVideoFilename($prefix = '', $time = '')
        {
            $uid = substr(uniqid(), -4);
            if (empty($time)) {
                $time = time();
            }
            $prefix = preg_replace('/[^a-z0-9]/i', '', $prefix);
            if (empty($prefix)) {
                $prefix = 'v';
            }
            $date = date('ymdHis', $time);
            $videoFilename = mb_strtolower("{$prefix}_{$date}_v{$uid}");
            return self::getPaths($videoFilename);
        }

        public static function isNewVideoFilename($filename)
        {
            $filename = self::getCleanFilenameFromFile($filename);
            return preg_match('/_([0-9]{12})_([0-9a-z]{4})$/i', $filename);
        }

        public static function getNewVideoFilenameWithPrefixFromFilename($filename)
        {
            $video = self::getVideoFromFileNameLight($filename);
            if (empty($video)) {
                return self::getNewVideoFilename();
            }
            return self::getNewVideoFilename($video['type']);
        }

        public static function updateDirectoryFilename($directory)
        {
            if (!is_dir($directory)) {
                _error_log('Video::updateDirectoryFilename directory not found ' . "[{$directory}]");
                return false;
            }
            $video = self::getVideoFromFileNameLight($directory);
            if (empty($video)) {
                _error_log('Video::updateDirectoryFilename video not found for directory ' . "[{$directory}]");
                return false;
            }

            if (isAnyStorageEnabled()) {
                $newFilename = self::getPaths($video['filename']);
                $id = $video['id'];
            } else {
                $newFilename = self::getNewVideoFilename($video['type'], strtotime($video['created']));
                $v = new Video('', '', $video['id']);
                $v->setFilename($newFilename['filename'], true);
                $id = $v->save(false, true);
            }

            if ($id) {
                $renamed = rename($directory, $newFilename['path']);
                if (empty($renamed)) { // rename dir fail rollback
                    _error_log('Video::updateDirectoryFilename rename dir fail, we will rollback changes ' . "[olddir={$directory}] [newdir={$newFilename['path']}]");
                    $v = new Video('', '', $video['id']);
                    $v->setFilename($video['filename'], true);
                    $id = $v->save(false, true);
                    return false;
                } else {
                    _error_log('Video::updateDirectoryFilename video folder renamed from ' . "[olddir={$directory}] [newdir={$newFilename['path']}]");
                    self::updateFilesInDirectoryFilename($newFilename['path']);
                }
            }

            return ['videos_id' => $video['id'], 'filename' => $newFilename['filename'], 'oldDir' => $directory, 'newDir' => $newFilename['path']];
        }

        public static function updateFilesInDirectoryFilename($directory)
        {
            if (!is_dir($directory)) {
                _error_log('Video::updateFilesInDirectoryFilename directory not found ' . "[{$directory}]");
                return false;
            }
            $video = self::getVideoFromFileNameLight($directory);
            if (empty($video)) {
                _error_log('Video::updateFilesInDirectoryFilename video not found for directory ' . "[{$directory}]");
                return false;
            }
            $newFilename = $video['filename'];
            $files = glob("{$directory}*.{jpg,png,gif,webp,vtt,srt,mp4,webm,mp3,ogg,notfound}", GLOB_BRACE);
            _error_log('Video::updateFilesInDirectoryFilename total files found ' . count($files));
            foreach ($files as $value) {
                $oldFilename = self::getCleanFilenameFromFile($value);
                $newFilenamePath = str_replace($oldFilename, $newFilename, $value);
                $renamed = rename($value, $newFilenamePath);
                if (empty($renamed)) { // rename dir fail rollback
                    _error_log('Video::updateFilesInDirectoryFilename rename file fail ' . "[olddir={$value}] [newdir={$newFilenamePath}]");
                } else {
                    _error_log('Video::updateFilesInDirectoryFilename video file renamed from ' . "[olddir={$value}] [newdir={$newFilenamePath}]");
                }
            }
        }

        public function getVideoIdHash()
        {
            $obj = new stdClass();
            $obj->videos_id = $this->id;
            return encryptString(json_encode($obj));
        }

        public static function getVideoIdFromHash($hash)
        {
            $string = decryptString($hash);
            if (!empty($string)) {
                $json = json_decode($string);
                if (!empty($json) && !empty($json->videos_id)) {
                    return $json->videos_id;
                }
            }
            return false;
        }

        public static function getCleanFilenameFromFile($filename)
        {
            global $global;
            if (empty($filename)) {
                return "";
            }
            $filename = fixPath($filename);
            $filename = str_replace('index_offline', 'index', $filename);
            $filename = str_replace(getVideosDir(), '', $filename);
            if (preg_match('/videos[\/\\\]([^\/\\\]+)[\/\\\].*index.(m3u8|mp4|mp3)$/', $filename, $matches)) {
                //var_dump($filename, $matches);
                return $matches[1];
            }

            $search = ['_Low', '_SD', '_HD', '_thumbsV2', '_thumbsSmallV2', '_thumbsSprit', '_roku', '_portrait', '_portrait_thumbsV2', '_portrait_thumbsSmallV2', '_thumbsV2_jpg', '_spectrum', '_tvg', '.notfound'];

            if (!empty($global['langs_codes_values_withdot']) && is_array($global['langs_codes_values_withdot'])) {
                $search = array_merge($search, $global['langs_codes_values_withdot']);
            }

            /**
             *
             * @var array $global
             * @var array $global['avideo_resolutions']
             */
            if (!empty($global['avideo_resolutions']) && is_array($global['avideo_resolutions'])) {
                foreach ($global['avideo_resolutions'] as $value) {
                    $search[] = "_{$value}";

                    $search[] = "res{$value}";
                }
            }
            $cleanName = str_replace($search, '', $filename);
            if ($cleanName == $filename || preg_match('/([a-z]+_[0-9]{12}_[a-z0-9]{4})_[0-9]+/', $filename)) {
                $cleanName = preg_replace('/([a-z]+_[0-9]{12}_[a-z0-9]{4,5})_[0-9]+/', '$1', $filename);
            }

            $path_parts = pathinfo($cleanName);
            if (empty($path_parts['extension'])) {
                //_error_log("Video::getCleanFilenameFromFile could not find extension of ".$filename);
                if (!empty($path_parts['filename'])) {
                    return $path_parts['filename'];
                } else {
                    return $filename;
                }
            } elseif (strlen($path_parts['extension']) > 4) {
                return $cleanName;
            } elseif ($path_parts['filename'] == 'index' && $path_parts['extension'] == 'm3u8') {
                $parts = explode(DIRECTORY_SEPARATOR, $cleanName);
                if (!empty($parts[0])) {
                    return $parts[0];
                }
                return $parts[1];
            } else {
                return $path_parts['filename'];
            }
        }

        public static function getSpecificResolution($filename, $desired_resolution)
        {
            $filename = self::getCleanFilenameFromFile($filename);
            $cacheName = "getSpecificResolution($filename)";
            $return = ObjectYPT::getCache($cacheName, 0);
            if (!empty($return)) {
                return object_to_array($return);
            }
            $name0 = "Video:::getSpecificResolution($filename)";
            TimeLogStart($name0);
            $name1 = "Video:::getSpecificResolution::getVideosURL_V2($filename)";
            TimeLogStart($name1);
            $sources = getVideosURL_V2($filename);
            if (!is_array($sources)) {
                _error_log("Video:::getSpecificResolution::getVideosURL_V2($filename) does not return an array " . json_encode($sources));
                return [];
            }
            TimeLogEnd($name1, __LINE__);
            $return = [];
            foreach ($sources as $key => $value) {
                if ($value['type'] === 'video') {
                    $parts = explode("_", $key);
                    $resolution = intval(@$parts[1]);
                    if (empty($resolution)) {
                        $name2 = "Video:::getSpecificResolution::getResolution({$value["path"]})";
                        TimeLogStart($name2);
                        $resolution = self::getResolution($value["path"]);
                        TimeLogEnd($name2, __LINE__);
                    }
                    if (!isset($return['resolution']) || $resolution == $desired_resolution) {
                        $return = $value;
                        $return['resolution'] = $resolution;
                        $return['resolution_text'] = getResolutionText($return['resolution']);
                        $return['resolution_label'] = getResolutionLabel($return['resolution']);
                        $return['resolution_string'] = trim($resolution . "p {$return['resolution_label']}");
                    }
                }
            }
            TimeLogEnd($name0, __LINE__);
            ObjectYPT::setCache($cacheName, $return);
            return $return;
        }

        public static function getHigestResolution($filename)
        {
            global $global;
            $filename = self::getCleanFilenameFromFile($filename);

            $return = [];

            $cacheName = "getHigestResolution($filename)";
            $return = ObjectYPT::getSessionCache($cacheName, 0);
            if (!empty($return)) {
                return object_to_array($return);
            }
            $name0 = "Video:::getHigestResolution($filename)";
            TimeLogStart($name0);
            $name1 = "Video:::getHigestResolution::getVideosURL_V2($filename)";
            TimeLogStart($name1);

            $v = self::getVideoFromFileNameLight($filename);
            if (empty($v)) {
                return [];
            }
            if ($v['type'] !== 'video') {
                return [];
            }
            if ($v['status'] !== self::$statusActive && $v['status'] !== self::$statusUnlisted && $v['status'] !== self::$statusUnlistedButSearchable) {
                return [];
            }
            $video = new Video('', '', $v['id']);
            if (empty($video)) {
                return [];
            }
            $HigestResolution = $video->getVideoHigestResolution();
            if (!empty($HigestResolution)) {
                //_error_log("getHigestResolution($filename) 1 {$HigestResolution} ".$video->getType());
                $resolution = $HigestResolution;
                $return['resolution'] = $resolution;
                $return['resolution_text'] = getResolutionText($return['resolution']);
                $return['resolution_label'] = getResolutionLabel($return['resolution']);
                $return['resolution_string'] = trim($resolution . "p {$return['resolution_label']}");
                return $return;
            } else {
                //_error_log("getHigestResolution($filename) 2 ".$video->getType());
                $validFileExtensions = ['webm', 'mp4', 'm3u8'];
                $sources = getVideosURL_V2($filename);
                if (!is_array($sources)) {
                    //_error_log("Video:::getHigestResolution::getVideosURL_V2($filename) does not return an array " . json_encode($sources));
                    return [];
                }
                TimeLogEnd($name1, __LINE__);
                foreach ($sources as $key => $value) {
                    $ext = pathinfo($value["path"], PATHINFO_EXTENSION);
                    if (!in_array($ext, $validFileExtensions)) {
                        continue;
                    }
                    if ($value['type'] === 'video') {
                        $parts = explode("_", $key);
                        $resolution = intval(@$parts[1]);
                        if (empty($resolution)) {
                            $name2 = "Video:::getHigestResolution::getResolution({$value["path"]})";
                            TimeLogStart($name2);
                            $resolution = self::getResolutionFromFilename($value["path"]); // this is faster
                            //var_dump(2, $filename, $resolution);
                            if (empty($resolution) && empty($global['onlyGetResolutionFromFilename'])) {
                                $resolution = self::getResolution($value["path"]);
                            }
                            TimeLogEnd($name2, __LINE__);
                        }
                        if (!isset($return['resolution']) || $resolution > $return['resolution']) {
                            $return = $value;
                            $return['resolution'] = $resolution;
                            $return['resolution_text'] = getResolutionText($return['resolution']);
                            $return['resolution_label'] = getResolutionLabel($return['resolution']);
                            $return['resolution_string'] = trim($resolution . "p {$return['resolution_label']}");
                        }
                    }
                }
            }
            //_error_log("Video:::getHigestResolution::getVideosURL_V2($filename) 3 FROM database " . $return['resolution'] . ' - ' . $v['path']); //exit;
            //if($filename=='video_210916143432_c426'){var_dump(1, $filename, $return);exit;}
            if (!empty($return)) {
                $video->setVideoHigestResolution($return['resolution']);
            }
            TimeLogEnd($name0, __LINE__);
            ObjectYPT::setSessionCache($cacheName, $return);
            return $return;
        }

        public static function getResolutionFromFilename($filename)
        {
            global $global;
            $resolution = false;
            if (preg_match("/_([0-9]+).(mp4|webm)/i", $filename, $matches)) {
                if (!empty($matches[1])) {
                    $resolution = intval($matches[1]);
                }
                //var_dump(__LINE__);
            } elseif (preg_match('/res([0-9]+)\/index.m3u8/i', $filename, $matches)) {
                if (!empty($matches[1])) {
                    $resolution = intval($matches[1]);
                }
                //var_dump(__LINE__);
            } elseif (preg_match('/_(HD|Low|SD).(mp4|webm)/i', $filename, $matches)) {
                if (!empty($matches[1])) {
                    if ($matches[1] == 'HD') {
                        $resolution = 1080;
                    } elseif ($matches[1] == 'SD') {
                        $resolution = 720;
                    } elseif ($matches[1] == 'Low') {
                        $resolution = 480;
                    }
                }
                //var_dump(__LINE__);
            } elseif (preg_match('/\/(hd|low|sd)\/index.m3u8/', $filename, $matches)) {
                if (!empty($matches[1])) {
                    if ($matches[1] == 'hd') {
                        $resolution = 1080;
                    } elseif ($matches[1] == 'sd') {
                        $resolution = 720;
                    } elseif ($matches[1] == 'low') {
                        $resolution = 480;
                    }
                }
                //var_dump(__LINE__);
            } elseif (preg_match('/video_[0-9_a-z]+\/index.m3u8/i', $filename)) {
                if (class_exists('VideoHLS')) {
                    $resolution = VideoHLS::getHLSHigestResolutionFromFile($filename);
                    //var_dump(5, $filename,$resolution);
                }
                //var_dump(__LINE__);
            }
            //echo PHP_EOL.PHP_EOL;var_dump(__LINE__, preg_match('/video_[0-9_a-z]+\/index.m3u8/i', $filename), $filename, $resolution, $matches);echo PHP_EOL.PHP_EOL;
            //if($filename=='video_210916143432_c426'){var_dump(3, $filename, $resolution, $matches);exit;}
            return $resolution;
        }

        public static function getHigestResolutionVideoMP4Source($filename, $includeS3 = false)
        {
            global $global;
            $types = ['', '_HD', '_SD', '_Low'];
            /**
             *
             * @var array $global
             * @var array $global['avideo_resolutions']
             */
            $resolutions = $global['avideo_resolutions'];
            rsort($resolutions);
            foreach ($resolutions as $value) {
                $types[] = "_{$value}";
            }
            foreach ($types as $value) {
                $source = self::getSourceFile($filename, $value . ".mp4", $includeS3);
                if (!empty($source['url'])) {
                    return $source;
                }
            }
            return false;
        }

        public static function getHigherVideoPathFromID($videos_id)
        {
            global $global;
            if (empty($videos_id)) {
                return false;
            }
            $paths = self::getVideosPathsFromID($videos_id);

            $types = [0, 2160, 1440, 1080, 720, 'HD', 'SD', 'Low', 540, 480, 360, 240];

            if (!empty($paths['mp4'])) {
                foreach ($types as $value) {
                    if (!empty($paths['mp4'][$value])) {
                        if (is_string($paths['mp4'][$value])) {
                            return $paths['mp4'][$value];
                        } else {
                            return $paths['mp4'][$value]["url"];
                        }
                    }
                }
            }
            if (!empty($paths['webm'])) {
                foreach ($types as $value) {
                    if (!empty($paths['webm'][$value])) {
                        if (is_string($paths['webm'][$value])) {
                            return $paths['webm'][$value];
                        } else {
                            return $paths['webm'][$value]["url"];
                        }
                    }
                }
            }
            if (!empty($paths['m3u8'])) {
                if (!empty($paths['m3u8'])) {
                    if (is_string($paths['m3u8']["url"])) {
                        return $paths['m3u8']["url"];
                    } elseif (is_string($paths['m3u8'][$value])) {
                        return $paths['m3u8'][$value];
                    } else {
                        return $paths['m3u8'][$value]["url"];
                    }
                }
            }
            if (!empty($paths['mp3'])) {
                return $paths['mp3'];
            }
            return false;
        }

        public static function getVideosPathsFromID($videos_id)
        {
            if (empty($videos_id)) {
                return false;
            }
            $video = new Video("", "", $videos_id);
            return self::getVideosPaths($video->getFilename(), true);
        }

        public static function getSourceFileURL($filename, $includeS3 = false, $fileType = '')
        {
            $sources = self::getVideosPaths($filename, $includeS3);
            if(empty($fileType) || $fileType == 'audio'){
                if (!empty($sources['mp3'])) {
                    return $sources['mp3'];
                }
            }
            if(empty($fileType) || $fileType == 'video'){
                if (!empty($sources['webm'])) {
                    return end($sources['webm']);
                }
                if (!empty($sources['m3u8']) && !empty($sources['m3u8']['url'])) {
                    return $sources['m3u8']['url'];
                }
                if (!empty($sources['mp4'])) {
                    return end($sources['mp4']);
                }
            }
            return false;
        }

        public static function getVideosPaths($filename, $includeS3 = false, $try = 0)
        {
            global $global, $_getVideosPaths;

            $cacheName = "getVideosPaths_$filename" . ($includeS3 ? 1 : 0);
            $cache = ObjectYPT::getCache($cacheName, 0);
            //var_dump($cacheName, $cache, _json_decode($cache));//exit;
            if (!empty($cache)) {
                return object_to_array(_json_decode($cache));
            }

            $types = ['', '_Low', '_SD', '_HD'];

            /**
             *
             * @var array $global
             * @var array $global['avideo_resolutions']
             */
            foreach ($global['avideo_resolutions'] as $value) {
                $types[] = "_{$value}";
            }

            $videos = [];

            $plugin = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
            if (!empty($plugin)) {
                $videos = VideoHLS::getSourceFile($filename, $includeS3);
            }

            foreach ($types as $value) {
                $source = self::getSourceFile($filename, $value . ".mp4", $includeS3);
                if (!empty($source['url'])) {
                    $videos['mp4'][str_replace("_", "", $value)] = $source['url'];
                }
            }

            foreach ($types as $value) {
                $source = self::getSourceFile($filename, $value . ".webm", $includeS3);
                if (!empty($source['url'])) {
                    $videos['webm'][str_replace("_", "", $value)] = $source['url'];
                }
            }
            $source = self::getSourceFile($filename, ".pdf", $includeS3);
            if (!empty($source['url'])) {
                $videos['pdf'] = $source['url'];
            }
            $source = self::getSourceFile($filename, ".zip", $includeS3);
            if (!empty($source['url'])) {
                $videos['zip'] = $source['url'];
            }
            $source = self::getSourceFile($filename, ".mp3", $includeS3);
            if (!empty($source['url'])) {
                $videos['mp3'] = $source['url'];
            }

            if (empty($videos) && $try === 0) {
                return self::getVideosPathsSearchingDir($filename, $includeS3);
            }
            $c = ObjectYPT::setCache($cacheName, $videos);
            //var_dump($cacheName, $c);exit;
            return $videos;
        }

        public static function getVideosPathsSearchingDir($filename, $includeS3 = false)
        {
            global $global;

            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            $paths = self::getPaths($filename);
            $dir = $paths["path"];
            if (!is_dir($dir)) {
                return array();
            }
            $allowedExtensions = array('mp4');
            $dirHandle = opendir($dir);
            while ($file = readdir($dirHandle)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (in_array($ext, $allowedExtensions)) {
                    $path = "$dir/$file";
                    $resolution = self::getResolutionFromFilename($path);
                    if (!in_array($resolution, $global['avideo_resolutions'])) {
                        _error_log("getVideosPathsGloob($filename) new resolution found $resolution");
                        $global['avideo_resolutions'][] = $resolution;
                        if (!empty($resolution)) {
                            closedir($dirHandle);
                            return self::getVideosPaths($filename, $includeS3, 1);
                        }
                    }
                }
            }
            closedir($dirHandle);
            return array();
        }

        public static function getStoragePath()
        {
            global $global;
            $path = "{$global['systemRootPath']}videos" . DIRECTORY_SEPARATOR;
            return $path;
        }

        public static function getStoragePathFromFileName($filename)
        {
            $cleanFileName = self::getCleanFilenameFromFile($filename);
            $path = self::getStoragePath() . "{$cleanFileName}/";
            make_path($path);
            return $path;
        }

        public static function getStoragePathFromVideosId($videos_id)
        {
            $v = new Video("", "", $videos_id);
            return self::getStoragePathFromFileName($v->getFilename());
        }

        public static function getImageFromFilename($filename, $type = "video", $async = false)
        {
            global $advancedCustom;
            // I dont know why but I had to remove it to avoid ERR_RESPONSE_HEADERS_TOO_BIG
            header_remove('Set-Cookie');
            if (!$async) {
                return self::getImageFromFilename_($filename, $type);
            } else {
                return self::getImageFromFilenameAsync($filename, $type);
            }
        }

        public static function getPoster($videos_id)
        {
            global $_getPoster;
            if (!isset($_getPoster)) {
                $_getPoster = [];
            }
            if (isset($_getPoster[$videos_id])) {
                return $_getPoster[$videos_id];
            }
            $images = self::getImageFromID($videos_id);
            $_getPoster[$videos_id] = false;
            if (!empty($images->poster)) {
                $_getPoster[$videos_id] = $images->poster;
            } elseif (!empty($images->posterPortrait)) {
                $_getPoster[$videos_id] = $images->posterPortrait;
            }
            return $_getPoster[$videos_id];
        }

        public static function getMediaSessionPosters($videos_id)
        {
            global $global;
            $images = self::getImageFromID($videos_id);
            $imagePath = $images->posterLandscapePath;
            if (empty($imagePath) || !file_exists($imagePath)) {
                $imagePath = $images->posterLandscapeThumbs;
            }
            if (empty($imagePath) || !file_exists($imagePath)) {
                $imagePath = $images->poster;
            }
            if (empty($imagePath) || empty(@filesize($imagePath))) {
                if (AVideoPlugin::isEnabledByName('MP4ThumbsAndGif')) {
                    MP4ThumbsAndGif::getImageInDuration($videos_id, 'jpg');
                }
            }

            return getMediaSessionPosters($imagePath);
        }

        public static function getRokuImage($videos_id)
        {
            global $global;
            $images = self::getImageFromID($videos_id);
            $imagePath = $images->posterLandscapePath;
            if (empty($imagePath) || !file_exists($imagePath)) {
                $imagePath = $images->posterLandscapeThumbs;
            }
            if (empty($imagePath) || !file_exists($imagePath)) {
                $imagePath = $images->poster;
            }
            $rokuImage = str_replace(".jpg", "_roku.jpg", $imagePath);
            if (convertImageToRoku($imagePath, $rokuImage)) {
                $relativePath = str_replace($global['systemRootPath'], '', $rokuImage);
                return getURL($relativePath);
            }
            return getURL("view/img/notfound.jpg");
        }

        public static function clearImageCache($filename, $type = "video")
        {
            $cacheFileName = "getImageFromFilename_" . $filename . $type . (get_browser_name() == 'Safari' ? "s" : "");
            return ObjectYPT::deleteCache($cacheFileName);
        }

        public static function getImageFromFilename_($filename, $type = "video")
        {
            if (empty($filename)) {
                return [];
            }
            global $_getImageFromFilename_;
            if (empty($_getImageFromFilename_)) {
                $_getImageFromFilename_ = [];
            }

            $cacheFileName = "getImageFromFilename_" . $filename . $type . (get_browser_name() == 'Safari' ? "s" : "");
            if (!empty($_getImageFromFilename_[$cacheFileName])) {
                $obj = $_getImageFromFilename_[$cacheFileName];
            } else {
                $cache = ObjectYPT::getCache($cacheFileName, 0, false, true, true);
                if (!empty($cache)) {
                    return $cache;
                }
                global $global, $advancedCustom;

                $timeLog1Limit = 0.1;
                $timeLog1 = "getImageFromFilename_($filename, $type)";
                TimeLogStart($timeLog1);
                /*
                  $name = "getImageFromFilename_{$filename}{$type}_";
                  $cached = ObjectYPT::getCache($name, 86400);//one day
                  if (!empty($cached)) {
                  return $cached;
                  }
                 *
                 */
                $obj = new stdClass();
                $gifSource = self::getSourceFile($filename, ".gif");
                $gifPortraitSource = self::getSourceFile($filename, "_portrait.gif");
                $jpegSource = self::getSourceFile($filename, ".jpg");
                $jpegPortraitSource = self::getSourceFile($filename, "_portrait.jpg");
                $jpegPortraitThumbs = self::getSourceFile($filename, "_portrait_thumbsV2.jpg");
                $jpegPortraitThumbsSmall = self::getSourceFile($filename, "_portrait_thumbsSmallV2.jpg");
                $thumbsSource = self::getSourceFile($filename, "_thumbsV2.jpg");
                $thumbsSmallSource = self::getSourceFile($filename, "_thumbsSmallV2.jpg");
                $spectrumSource = self::getSourceFile($filename, "_spectrum.jpg");
                if (empty($jpegSource)) {
                    return [];
                }
                $obj->poster = $jpegSource['url'];
                $obj->posterPortrait = $jpegPortraitSource['url'];
                $obj->posterPortraitPath = $jpegPortraitSource['path'];
                $obj->posterPortraitThumbs = $jpegPortraitThumbs['url'];
                $obj->posterPortraitThumbsSmall = $jpegPortraitThumbsSmall['url'];
                $obj->thumbsGif = $gifSource['url'];
                $obj->gifPortrait = $gifPortraitSource['url'];
                $obj->thumbsJpg = $thumbsSource['url'];
                $obj->thumbsJpgSmall = $thumbsSmallSource['url'];
                $obj->spectrumSource = $spectrumSource['url'];

                $obj->posterLandscape = $jpegSource['url'];
                $obj->posterLandscapePath = $jpegSource['path'];
                $obj->posterLandscapeThumbs = $thumbsSource['url'];
                $obj->posterLandscapeThumbsSmall = $thumbsSmallSource['url'];

                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                if (file_exists($gifSource['path'])) {
                    $obj->thumbsGif = $gifSource['url'];
                }
                if (file_exists($jpegPortraitSource['path'])) {
                    convertImageIfNotExists($jpegPortraitSource['path'], $jpegPortraitThumbs['path'], $advancedCustom->thumbsWidthPortrait, $advancedCustom->thumbsHeightPortrait, true);
                    convertImageIfNotExists($jpegPortraitThumbsSmall['path'], $jpegPortraitThumbsSmall['path'], $advancedCustom->thumbsWidthPortrait / 2, $advancedCustom->thumbsHeightPortrait / 2, true);
                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                } else {
                    if ($type == "article") {
                        $obj->posterPortrait = "" . getCDN() . "view/img/article_portrait.png";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/article_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/article_portrait.png";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/article_portrait.png";
                    } elseif ($type == "pdf") {
                        $obj->posterPortrait = "" . getCDN() . "view/img/pdf_portrait.png";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/pdf_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/pdf_portrait.png";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/pdf_portrait.png";
                    } /* elseif ($type == "image") {
                      $obj->posterPortrait = "".getCDN()."view/img/image_portrait.png";
                      $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/image_portrait.png";
                      $obj->posterPortraitThumbs = "".getCDN()."view/img/image_portrait.png";
                      $obj->posterPortraitThumbsSmall = "".getCDN()."view/img/image_portrait.png";
                      } */ elseif ($type == "zip") {
                        $obj->posterPortrait = "" . getCDN() . "view/img/zip_portrait.png";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/zip_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/zip_portrait.png";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/zip_portrait.png";
                    } else {
                        $obj->posterPortrait = "" . getCDN() . "view/img/notfound_portrait.jpg";
                        $obj->posterPortraitPath = "{$global['systemRootPath']}view/img/notfound_portrait.png";
                        $obj->posterPortraitThumbs = "" . getCDN() . "view/img/notfound_portrait.jpg";
                        $obj->posterPortraitThumbsSmall = "" . getCDN() . "view/img/notfound_portrait.jpg";
                    }
                }

                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                if (file_exists($jpegSource['path'])) {
                    $obj->poster = $jpegSource['url'];
                    $obj->thumbsJpg = $thumbsSource['url'];
                    convertImageIfNotExists($jpegSource['path'], $thumbsSource['path'], $advancedCustom->thumbsWidthLandscape, $advancedCustom->thumbsHeightLandscape, true);
                    convertImageIfNotExists($jpegSource['path'], $thumbsSmallSource['path'], $advancedCustom->thumbsWidthLandscape / 2, $advancedCustom->thumbsHeightLandscape / 2, true);

                    TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                } else {
                    if ($type == "article") {
                        $obj->poster = "" . getCDN() . "view/img/article.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/article.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/article.png";
                    } elseif ($type == "pdf") {
                        $obj->poster = "" . getCDN() . "view/img/pdf.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/pdf.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/pdf.png";
                    } elseif ($type == "image") {
                        $obj->poster = "" . getCDN() . "view/img/image.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/image.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/image.png";
                    } elseif ($type == "zip") {
                        $obj->poster = "" . getCDN() . "view/img/zip.png";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/zip.png";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/zip.png";
                    } elseif (($type !== "audio") && ($type !== "linkAudio")) {
                        if (file_exists($spectrumSource['path'])) {
                            $obj->poster = $spectrumSource['url'];
                            $obj->thumbsJpg = $spectrumSource['url'];
                            $obj->thumbsJpgSmall = $spectrumSource['url'];
                        } else {
                            $obj->poster = "" . getCDN() . "view/img/notfound.jpg";
                            $obj->thumbsJpg = "" . getCDN() . "view/img/notfoundThumbs.jpg";
                            $obj->thumbsJpgSmall = "" . getCDN() . "view/img/notfoundThumbsSmall.jpg";
                        }
                    } else {
                        $obj->poster = "" . getCDN() . "view/img/audio_wave.jpg";
                        $obj->thumbsJpg = "" . getCDN() . "view/img/audio_waveThumbs.jpg";
                        $obj->thumbsJpgSmall = "" . getCDN() . "view/img/audio_waveThumbsSmall.jpg";
                    }
                }

                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
                if (empty($obj->thumbsJpg)) {
                    $obj->thumbsJpg = $obj->poster;
                }
                if (empty($obj->thumbsJpgSmall)) {
                    $obj->thumbsJpgSmall = $obj->poster;
                }
                //ObjectYPT::setCache($name, $obj);
                if (!empty($advancedCustom->disableAnimatedGif)) {
                    $obj->thumbsGif = false;
                }

                ObjectYPT::setCache($cacheFileName, $obj);
                //$cache = ObjectYPT::getCache($cacheFileName, 0);
                //_error_log("getImageFromFilename_($filename, $type) [$cacheFileName] ".!empty($cache).' ' .json_encode($response));
                $_getImageFromFilename_[$cacheFileName] = $obj;
                TimeLogEnd($timeLog1, __LINE__, $timeLog1Limit);
            }

            return $obj;
        }

        public static function getImageFromFilenameAsync($filename, $type = "video")
        {
            global $global, $advancedCustom;
            $return = [];
            $path = getCacheDir() . "getImageFromFilenameAsync/";
            make_path($path);
            $cacheFileName = "{$path}_{$filename}_{$type}";
            if (!file_exists($cacheFileName)) {
                if (file_exists($cacheFileName . ".lock")) {
                    return [];
                }
                file_put_contents($cacheFileName . ".lock", 1);
                $total = static::getImageFromFilename_($filename, $type);
                file_put_contents($cacheFileName, json_encode($total));
                unlink($cacheFileName . ".lock");
                return $total;
            }
            $return = _json_decode(file_get_contents($cacheFileName));
            if (time() - filemtime($cacheFileName) > cacheExpirationTime()) {
                // file older than 1 min
                $command = ("php '{$global['systemRootPath']}objects/getImageFromFilenameAsync.php' '$filename' '$type' '{$cacheFileName}'");
                //_error_log("getImageFromFilenameAsync: {$command}");
                exec($command . " > /dev/null 2>/dev/null &");
            }
            return $return;
        }

        public static function getImageFromID($videos_id)
        {
            global $global;
            $video = new Video("", "", $videos_id);
            $return = (object) self::getImageFromFilename($video->getFilename());
            if (empty($return->posterLandscapePath)) {
                $path = Video::getPaths($video->getFilename());
                if (!empty($path['path'])) {
                    $return->posterLandscapePath = "{$path['path']}{$path['filename']}.jpg";
                    $return->posterLandscape = "{$path['url']}{$path['filename']}.jpg";
                }
            }
            if (empty($return->posterPortraitPath)) {
                $path = Video::getPaths($video->getFilename());
                if (!empty($path['path'])) {
                    $return->posterPortraitPath = "{$path['path']}{$path['filename']}_portrait.jpg";
                    $return->posterPortrait = "{$path['url']}{$path['filename']}_portrait.jpg";
                }
            }

            if (defaultIsLandscape() && !empty($return->posterLandscape)) {
                $return->default = ['url' => $return->posterLandscape, 'path' => $return->posterLandscapePath];
            } else if (!empty($return->posterPortrait)) {
                $return->default = ['url' => $return->posterPortrait, 'path' => $return->posterPortraitPath];
            } else {
                $return->default = ['url' => getCDN() . "view/img/notfoundThumbs.jpg", 'path' => "{$global['systemRootPath']}view/img/notfoundThumbs.jpg"];
            }

            return $return;
        }

        public function getViews_count()
        {
            return intval($this->views_count);
        }

        public static function get_clean_title($videos_id)
        {
            global $global;

            $sql = "SELECT * FROM videos WHERE id = ? LIMIT 1";

            $res = sqlDAL::readSql($sql, "i", [$videos_id]);
            $videoRow = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);

            if ($res !== false) {
                if ($videoRow !== false) {
                    return $videoRow['clean_title'];
                }
            } else {
                $videos = false;
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return false;
        }

        public static function get_id_from_clean_title($clean_title)
        {
            global $global;

            $sql = "SELECT * FROM videos WHERE clean_title = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "s", [$clean_title]);
            $videoRow = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($res !== false) {
                if ($videoRow !== false) {
                    return $videoRow['id'];
                }
            } else {
                $videos = false;
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return false;
        }

        public function getChannelName()
        {
            return User::_getChannelName($this->getUsers_id());
        }

        public function getChannelLink()
        {
            return User::getChannelLink($this->getUsers_id());
        }

        /**
         *
         * @global array $global
         * @param string $videos_id
         * @param string $clean_title
         * @param string $embed
         * @param string $type URLFriendly or permalink
         * @return String a web link
         */
        public static function getLinkToVideo($videos_id, $clean_title = "", $embed = false, $type = "URLFriendly", $get = [], $ignoreChannelname = false)
        {
            global $global, $advancedCustomUser, $advancedCustom;
            if (!empty($_GET['evideo'])) {
                $v = self::decodeEvideo();
                if (!empty($v['video']['videoLink'])) {
                    if ($embed) {
                        return parseVideos($v['video']['videoLink'], $advancedCustom->embedAutoplay, $advancedCustom->embedLoop, $advancedCustom->embedStartMuted, $advancedCustom->embedShowinfo, $advancedCustom->embedControls->value);
                    } else {
                        return $v['video']['videoLink'];
                    }
                }
            }

            if (!is_object($advancedCustomUser)) {
                $advancedCustomUser = AVideoPlugin::getDataObject('CustomizeUser');
            }
            if (empty($advancedCustom)) {
                $advancedCustom = AVideoPlugin::loadPlugin("CustomizeAdvanced");
            }
            if (empty($videos_id) && !empty($clean_title)) {
                $videos_id = self::get_id_from_clean_title($clean_title);
            }
            $video = new Video("", "", $videos_id);

            if (!$ignoreChannelname && $advancedCustomUser->addChannelNameOnLinks) {
                $get['channelName'] = $video->getChannelName();
            } elseif ($ignoreChannelname) {
                $get['channelName'] = null;
            }

            unset($get['v'], $get['videoName'], $get['videoName'], $get['isMediaPlaySite'], $get['parentsOnly']);
            $get_http = http_build_query($get);
            if (empty($get_http)) {
                $get_http = '';
            } else {
                $get_http = "?{$get_http}";
            }

            if ($type == "URLFriendly") {
                $cat = '';
                if (!empty($_REQUEST['catName'])) {
                    $cat = "cat/{$_REQUEST['catName']}/";
                }

                if (empty($clean_title)) {
                    $clean_title = $video->getClean_title();
                }
                $clean_title = @urlencode($clean_title);
                $subDir = "video";
                $subEmbedDir = "videoEmbed";
                if ($video->getType() == 'article') {
                    $subDir = "article";
                    $subEmbedDir = "articleEmbed";
                }
                if (!empty($advancedCustom->makeVideosIDHarderToGuess)) {
                    $videoHash = idToHash($videos_id);
                    if (!empty($videoHash)) {
                        $encryptedVideos_id = '.' . idToHash($videos_id);
                        $videos_id = $encryptedVideos_id;
                    }
                }
                if ($embed) {
                    if (empty($advancedCustom->useVideoIDOnSEOLinks)) {
                        $url = "{$global['webSiteRootURL']}{$subEmbedDir}/{$clean_title}{$get_http}";
                    } else {
                        $url = "{$global['webSiteRootURL']}{$subEmbedDir}/{$videos_id}/{$clean_title}{$get_http}";
                    }
                    return parseVideos($url, $advancedCustom->embedAutoplay, $advancedCustom->embedLoop, $advancedCustom->embedStartMuted, $advancedCustom->embedShowinfo, $advancedCustom->embedControls->value);
                } else {
                    if (empty($advancedCustom->useVideoIDOnSEOLinks)) {
                        return "{$global['webSiteRootURL']}{$cat}{$subDir}/{$clean_title}{$get_http}";
                    } else {
                        return "{$global['webSiteRootURL']}{$subDir}/{$videos_id}/{$clean_title}{$get_http}";
                    }
                }
            } else {
                if (!empty($advancedCustom->makeVideosIDHarderToGuess)) {
                    $encryptedVideos_id = '.' . idToHash($videos_id);
                    $videos_id = $encryptedVideos_id;
                }
                if ($embed) {
                    $url = "{$global['webSiteRootURL']}vEmbed/{$videos_id}{$get_http}";
                    return parseVideos($url, $advancedCustom->embedAutoplay, $advancedCustom->embedLoop, $advancedCustom->embedStartMuted, $advancedCustom->embedShowinfo, $advancedCustom->embedControls->value);
                } else {
                    return "{$global['webSiteRootURL']}v/{$videos_id}{$get_http}";
                }
            }
        }

        public static function getPermaLink($videos_id, $embed = false, $get = [])
        {
            return self::getLinkToVideo($videos_id, "", $embed, "permalink", $get);
        }

        public static function getURLFriendly($videos_id, $embed = false, $get = [])
        {
            return self::getLinkToVideo($videos_id, "", $embed, "URLFriendly", $get);
        }

        public static function getPermaLinkFromCleanTitle($clean_title, $embed = false, $get = [])
        {
            return self::getLinkToVideo("", $clean_title, $embed, "permalink", $get);
        }

        public static function getURLFriendlyFromCleanTitle($clean_title, $embed = false, $get = [])
        {
            return self::getLinkToVideo("", $clean_title, $embed, "URLFriendly", $get);
        }

        public static function getLink($videos_id, $clean_title, $embed = false, $get = [])
        {
            global $advancedCustom;
            if (!empty($advancedCustom->usePermalinks)) {
                $type = "permalink";
            } else {
                $type = "URLFriendly";
            }

            return self::getLinkToVideo($videos_id, $clean_title, $embed, $type, $get);
        }

        public static function getTotalVideosThumbsUpFromUser($users_id, $startDate, $endDate)
        {
            global $global;

            $sql = "SELECT id from videos  WHERE users_id = ?  ";

            $res = sqlDAL::readSql($sql, "i", [$users_id]);
            $videoRows = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);

            $r = ['thumbsUp' => 0, 'thumbsDown' => 0];

            if ($res !== false) {
                foreach ($videoRows as $row) {
                    $values = [$row['id']];
                    $format = "i";
                    $sql = "SELECT id from likes WHERE videos_id = ? AND `like` = 1  ";
                    if (!empty($startDate)) {
                        $sql .= " AND `created` >= ? ";
                        $format .= "s";
                        $values[] = $startDate;
                    }

                    if (!empty($endDate)) {
                        $sql .= " AND `created` <= ? ";
                        $format .= "s";
                        $values[] = $endDate;
                    }
                    $res = sqlDAL::readSql($sql, $format, $values);
                    $countRow = sqlDAL::num_rows($res);
                    sqlDAL::close($res);
                    $r['thumbsUp'] += $countRow;

                    $format = '';
                    $values = [];
                    $sql = "SELECT id from likes WHERE videos_id = {$row['id']} AND `like` = -1  ";
                    if (!empty($startDate)) {
                        $sql .= " AND `created` >= ? ";
                        $format .= "s";
                        $values[] = $startDate;
                    }

                    if (!empty($endDate)) {
                        $sql .= " AND `created` <= ? ";
                        $format .= "s";
                        $values[] = $endDate;
                    }
                    $res = sqlDAL::readSql($sql, $format, $values);
                    $countRow = sqlDAL::num_rows($res);
                    sqlDAL::close($res);
                    $r['thumbsDown'] += $countRow;
                }
            }

            return $r;
        }

        public static function getTotalVideosThumbsUpFromUserFromVideos($users_id)
        {
            global $global;

            $sql = "SELECT sum(likes) as thumbsUp, sum(dislikes) as thumbsDown from videos WHERE users_id = ?  ";

            $res = sqlDAL::readSql($sql, "i", [$users_id]);
            $videoRows = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);

            $r = ['thumbsUp' => 0, 'thumbsDown' => 0];

            if ($res !== false) {
                foreach ($videoRows as $row) {
                    $r['thumbsUp'] += intval($row['thumbsUp']);
                    $r['thumbsDown'] += intval($row['thumbsDown']);
                }
            }
            return $r;
        }

        public static function deleteThumbs($filename, $doNotDeleteSprit = false, $checkIfIsCorrupted = false)
        {
            if (empty($filename)) {
                return false;
            }
            global $global;

            $filePath = Video::getPathToFile($filename);

            deleteMediaSessionPosters($filePath . '.jpg');

            // Streamlined for less coding space.
            $files = glob("{$filePath}*_thumbs*.jpg");
            $files[] = "{$filePath}_roku.jpg";
            $files[] = "{$filePath}_thumbsV2_jpg.webp";
            $files[] = "{$filePath}_jpg.webp";
            $totalDeleted = 0;
            foreach ($files as $file) {
                if (file_exists($file)) {
                    if ($checkIfIsCorrupted && !isImageCorrupted($file)) {
                        continue;
                    }
                    if ($doNotDeleteSprit && strpos($file, '_thumbsSprit.jpg') !== false) {
                        continue;
                    }
                    if (isCommandLineInterface()) {
                        echo "Deleting {$file} " . humanFileSize(filesize($file)) . PHP_EOL;
                    }
                    @unlink($file);
                    $totalDeleted++;
                }
            }
            if ($totalDeleted) {
                ObjectYPT::deleteCache($filename);
                ObjectYPT::deleteCache($filename . "article");
                ObjectYPT::deleteCache($filename . "pdf");
                ObjectYPT::deleteCache($filename . "video");
                Video::clearImageCache($filename);
                Video::clearImageCache($filename, "article");
                Video::clearImageCache($filename, "pdf");
                Video::clearImageCache($filename, "audio");
                clearVideosURL($filename);
            }
            return $totalDeleted;
        }

        public static function deleteGifAndWebp($filename)
        {
            if (empty($filename)) {
                return false;
            }
            global $global;

            $filePath = Video::getPathToFile($filename);
            @unlink("{$filePath}.gif");
            @unlink("{$filePath}.webp");
            ObjectYPT::deleteCache($filename);
            ObjectYPT::deleteCache($filename . "article");
            ObjectYPT::deleteCache($filename . "pdf");
            ObjectYPT::deleteCache($filename . "video");
            Video::clearImageCache($filename);
            Video::clearImageCache($filename, "article");
            Video::clearImageCache($filename, "pdf");
            Video::clearImageCache($filename, "audio");
            clearVideosURL($filename);
            return true;
        }

        public static function clearCache($videos_id)
        {
            //_error_log("Video:clearCache($videos_id)");
            $video = new Video("", "", $videos_id);
            $filename = $video->getFilename();
            if (empty($filename)) {
                _error_log("Video:clearCache filename not found");
                return false;
            }
            self::deleteThumbs($filename, true);
            ObjectYPT::deleteCache("PlayeSkins_getVideoTags{$videos_id}");
            ObjectYPT::deleteCache("_getVideoInfo_{$videos_id}");
            ObjectYPT::deleteCache("otherInfo{$videos_id}");
            ObjectYPT::deleteCache($filename);
            ObjectYPT::deleteCache("getVideosURL_V2$filename");
            ObjectYPT::deleteCache($filename . "article");
            ObjectYPT::deleteCache($filename . "pdf");
            ObjectYPT::deleteCache($filename . "video");
            ObjectYPT::deleteCache(md5($filename . ".m3u8"));
            ObjectYPT::deleteCache(md5($filename . ".mp4"));
            ObjectYPT::deleteCache(md5($filename . ".m3u81"));
            ObjectYPT::deleteCache(md5($filename . ".mp41"));
            if (!class_exists('Cache')) {
                AVideoPlugin::loadPlugin('Cache');
            }
            Cache::deleteCache("getSourceFile($filename)1");
            Cache::deleteCache("getSourceFile($filename)0");
            Cache::deleteCache("getVideosPaths_($filename)1");
            Cache::deleteCache("getVideosPaths_($filename)0");
            Video::clearImageCache($filename);
            Video::clearImageCache($filename, "article");
            Video::clearImageCache($filename, "pdf");
            Video::clearImageCache($filename, "audio");
            Video::deleteTagsAsync($videos_id);
            clearVideosURL($filename);
            AVideoPlugin::deleteVideoTags($videos_id);
            ObjectYPT::setLastDeleteALLCacheTime();
            return true;
        }

        public static function clearCacheFromFilename($fileName)
        {
            if ($fileName == '.zip') {
                return false;
            }
            //_error_log("Video:clearCacheFromFilename($fileName)");
            $video = self::getVideoFromFileNameLight($fileName);
            if (empty($video['id'])) {
                return false;
            }
            return self::clearCache($video['id']);
        }

        public static function getVideoPogress($videos_id, $users_id = 0)
        {
            if (empty($users_id)) {
                if (!User::isLogged()) {
                    return 0;
                }
                $users_id = User::getId();
            }

            return VideoStatistic::getLastVideoTimeFromVideo($videos_id, $users_id);
        }

        public static function getLastVideoTimePosition($videos_id, $users_id = 0)
        {
            return self::getVideoPogress($videos_id, $users_id);
        }

        public static function getVideoPogressPercent($videos_id, $users_id = 0)
        {
            $lastVideoTime = self::getVideoPogress($videos_id, $users_id);

            if (empty($lastVideoTime)) {
                return ['percent' => 0, 'lastVideoTime' => 0, 'msg' => 'empty LastVideoTime'];
            }

            // start incremental search and save
            $sql = "SELECT duration FROM `videos` WHERE id = ? LIMIT 1";
            $res = sqlDAL::readSql($sql, "i", [$videos_id]);
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);

            if (empty($row) || empty($row['duration'])) {
                return ['percent' => 0, 'lastVideoTime' => 0];
            }

            $duration = parseDurationToSeconds($row['duration']);

            if (empty($duration)) {
                return ['percent' => 0, 'lastVideoTime' => 0, 'msg' => 'empty duration'];
            }

            if ($lastVideoTime > $duration) {
                return ['percent' => 100, 'lastVideoTime' => $lastVideoTime, 'msg' => 'LastVideoTime > duration'];
            }

            //var_dump(__LINE__, $videos_id,  $users_id, $lastVideoTime, ['percent' => ($lastVideoTime / $duration) * 100, 'lastVideoTime' => $lastVideoTime]);exit;
            return ['percent' => ($lastVideoTime / $duration) * 100, 'lastVideoTime' => $lastVideoTime, 'duration' => $duration];
        }

        public function getRrating()
        {
            return $this->rrating;
        }

        public function getRratingHTML()
        {
            global $global;
            if (!empty($this->rrating)) {
                $filePath = $global['systemRootPath'] . 'view/rrating/rating-' . $this->rrating . '.php';
                if (file_exists($filePath)) {
                    return getIncludeFileContent($filePath);
                }
            }
            return '';
        }

        public function setRrating($rrating)
        {
            $rrating = mb_strtolower($rrating);
            if (!in_array($rrating, self::$rratingOptions)) {
                $rrating = '';
            }
            AVideoPlugin::onVideoSetRrating($this->id, $this->rrating, $rrating);
            $this->rrating = $rrating;
        }

        public static function getVideoTypeFromId($videos_id)
        {
            if (empty($videos_id)) {
                return false;
            }
            $video = Video::getVideoLight($videos_id);
            if (empty($video['filename'])) {
                return false;
            }
            return self::getVideoType($video['filename']);
        }

        public static function getVideoType($filename)
        {
            global $_getVideoType;

            if (!isset($_getVideoType)) {
                $_getVideoType = [];
            }
            if (isset($_getVideoType[$filename])) {
                return $_getVideoType[$filename];
            }

            $obj = new stdClass();
            $paths = self::getVideosPaths($filename);
            //var_dump($paths);exit;
            $obj->mp4 = !empty($paths['mp4']) ? true : false;
            $obj->webm = !empty($paths['webm']) ? true : false;
            $obj->m3u8 = !empty($paths['m3u8']) ? true : false;
            $obj->pdf = !empty($paths['pdf']) ? true : false;
            $obj->mp3 = !empty($paths['mp3']) ? true : false;

            $_getVideoType[$filename] = $obj;
            return $obj;
        }

        public static function getVideoTypeLabels($filename)
        {
            $obj = self::getVideoType($filename);
            $labels = '';
            if (empty($obj->mp4) && empty($obj->webm) && empty($obj->m3u8) && empty($obj->pdf) && empty($obj->mp3)) {
                return '<span class="label label-default">Other</span>';
            }
            if ($obj->mp4) {
                $labels .= '<span class="label label-success">MP4</span>';
            }
            if ($obj->webm) {
                $labels .= '<span class="label label-warning">Webm</span>';
            }
            if ($obj->m3u8) {
                $labels .= '<span class="label label-primary">HLS</span>';
            }
            if ($obj->pdf) {
                $labels .= '<span class="label label-danger">PDF</span>';
            }
            if ($obj->mp3) {
                $labels .= '<span class="label label-info">MP3</span>';
            }
            return $labels;
        }

        /**
         * Based on Roku Type
         * @param string $filename
         * @return string
         */
        public static function getVideoTypeText($filename)
        {
            $obj = self::getVideoType($filename);
            $labels = '';
            if (empty($obj->mp4) && empty($obj->webm) && empty($obj->m3u8) && empty($obj->pdf) && empty($obj->mp3)) {
                return __('Other');
            }
            if ($obj->mp4) {
                return 'MP4';
            }
            if ($obj->webm) {
                return 'WEBM';
            }
            if ($obj->m3u8) {
                return 'HLS';
            }
            if ($obj->pdf) {
                return 'PDF';
            }
            if ($obj->mp3) {
                return 'MP3';
            }
            return $labels;
        }

        public static function isPublic($videos_id)
        {
            // check if the video is not public
            $rows = UserGroups::getVideosAndCategoriesUserGroups($videos_id);

            if (empty($rows)) {
                return true;
            }
            return false;
        }

        public static function userGroupAndVideoGroupMatch($users_id, $videos_id)
        {
            if (empty($videos_id)) {
                return false;
            }

            $ppv = AVideoPlugin::loadPluginIfEnabled("PayPerView");
            if ($ppv) {
                $ppv->userCanWatchVideo($users_id, $videos_id);
            }
            // check if the video is not public
            $rows = UserGroups::getVideosAndCategoriesUserGroups($videos_id);
            if (empty($rows)) {
                return true;
            }

            if (empty($users_id)) {
                return false;
            }

            $rowsUser = UserGroups::getUserGroups(User::getId());
            if (empty($rowsUser)) {
                return false;
            }

            foreach ($rows as $value) {
                foreach ($rowsUser as $value2) {
                    if ($value['id'] === $value2['id']) {
                        return true;
                    }
                }
            }
            return false;
        }

        public function getExternalOptions()
        {
            return $this->externalOptions;
        }

        public function setExternalOptions($externalOptions)
        {
            AVideoPlugin::onVideoSetExternalOptions($this->id, $this->externalOptions, $externalOptions);
            if (!is_string($externalOptions)) {
                $externalOptions = _json_encode($externalOptions);
            }
            $this->externalOptions = $externalOptions;
        }

        public function setVideoTags($tags)
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            if (!is_object($externalOptions)) {
                $externalOptions = new stdClass();
            }
            $externalOptions->VideoTags = $tags;
            $this->setExternalOptions(json_encode($externalOptions));
        }

        public function getVideoTags()
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            if (empty($externalOptions->VideoTags)) {
                return false;
            }
            return $externalOptions->VideoTags;
        }

        public function setVideoHigestResolution($HigestResolution)
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            if (!is_object($externalOptions)) {
                $externalOptions = new stdClass();
            }
            $externalOptions->HigestResolution = $HigestResolution;
            $this->setExternalOptions(json_encode($externalOptions));
            return $this->save(false, true);
        }

        public function getVideoHigestResolution()
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            if (empty($externalOptions->HigestResolution)) {
                return false;
            }
            return $externalOptions->HigestResolution;
        }

        public function setVideoStartSeconds($videoStartSeconds)
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            AVideoPlugin::onVideoSetVideoStartSeconds($this->id, $externalOptions->videoStartSeconds, $videoStartSeconds);
            $externalOptions->videoStartSeconds = intval($videoStartSeconds);
            $this->setExternalOptions(json_encode($externalOptions));
        }

        
        public function setPrivacyInfo($object)
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            if(empty($externalOptions)){
                $externalOptions = new stdClass();
            }
            $externalOptions->privacyInfo = $object;
            $this->setExternalOptions(json_encode($externalOptions));
            return $externalOptions->privacyInfo;
        }

        public function getPrivacyInfo()
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            if (empty($externalOptions->privacyInfo)) {
                $externalOptions->privacyInfo = self::_getPrivacyInfo($this->id);
                $this->setPrivacyInfo($externalOptions->privacyInfo);
            }
            return $externalOptions->privacyInfo;
        }


        public function setVideoEmbedWhitelist($embedWhitelist)
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            $externalOptions->embedWhitelist = $embedWhitelist;
            $this->setExternalOptions(json_encode($externalOptions));
        }

        public function getVideoEmbedWhitelist()
        {
            $externalOptions = _json_decode($this->getExternalOptions());
            if (empty($externalOptions->embedWhitelist)) {
                return '';
            }
            return $externalOptions->embedWhitelist;
        }

        public static function getEmbedWhitelist($videos_id)
        {
            $v = new Video('', '', $videos_id);
            return $v->getVideoEmbedWhitelist();
        }

        public function getSerie_playlists_id()
        {
            return $this->serie_playlists_id;
        }

        public function setSerie_playlists_id($serie_playlists_id)
        {
            AVideoPlugin::onVideoSetSerie_playlists_id($this->id, $this->serie_playlists_id, $serie_playlists_id);
            $this->serie_playlists_id = $serie_playlists_id;
        }

        public static function getVideoFromSeriePlayListsId($serie_playlists_id)
        {
            global $global, $config;
            $serie_playlists_id = intval($serie_playlists_id);
            $sql = "SELECT * FROM videos WHERE serie_playlists_id = '$serie_playlists_id' LIMIT 1";
            $res = sqlDAL::readSql($sql, "", []);
            $video = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return $video;
        }

        /**
         * if will show likes, comments, share, etc
         * @return boolean
         */
        public static function showYoutubeModeOptions()
        {
            global $video;
            if (!empty($_GET['evideo'])) {
                $v = self::decodeEvideo();
                if (empty($v['video']['views_count'])) {
                    return false;
                } else {
                    return true;
                }
            }
            if (empty($video) || $video['type'] === 'notfound') {
                return false;
            }
            return true;
        }

        public static function decodeEvideo()
        {
            $evideo = false;
            if (!empty($_GET['evideo'])) {
                $evideo = _json_decode(decryptString($_GET['evideo']));
            }
            $video = [];
            if (!empty($evideo)) {
                $video['id'] = 0;
                $video['type'] = 'embed';
                $video['rotation'] = 0;
                $video['videoLink'] = $evideo->videoLink;
                $video['title'] = $evideo->title;
                $video['clean_title'] = preg_replace('/[!#$&\'()*+,\\/:;=?@[\\] ]+/', '-', trim(mb_strtolower(cleanString($evideo->title))));
                if (empty($evideo->description) && !empty($evideo->videos_id)) {
                    $divId = uniqid();
                    $video['description'] = '<div id="' . $divId . '"></div>
                    <script>
                        $(document).ready(function () {
                            $.ajax({
                                url: "' . $evideo->webSiteRootURL . 'plugin/API/get.json.php?APIName=video&videos_id=' . $evideo->videos_id . '",
                                success: function (response) {
                                    if(!response.error && response.response.rows[0] && response.response.rows[0].description){
                                        $("#' . $divId . '").html(response.response.rows[0].description);
                                    }

                                }
                            });
                        });
                    </script>';
                } else {
                    $video['description'] = @$evideo->description;
                }

                $video['duration'] = @$evideo->duration;
                $video['creator'] = @$evideo->creator;
                $video['likes'] = '';
                $video['dislikes'] = '';
                $video['category'] = "embed";
                $video['views_count'] = intval(@$evideo->views_count);
            }
            return ['evideo' => $evideo, 'video' => $video];
        }

        private static function getBlockedUsersIdsArray($users_id = 0)
        {
            if (empty($users_id)) {
                $users_id = intval(User::getId());
            }
            if (empty($users_id)) {
                return [];
            }
            if (!User::isLogged()) {
                return [];
            }
            $report = AVideoPlugin::getDataObjectIfEnabled("ReportVideo");
            if (empty($report)) {
                return [];
            }
            return ReportVideo::getAllReportedUsersIdFromUser($users_id);
        }

        public static function forceAudio()
        {
            if (!empty($_REQUEST['includeType'])) {
                if ($_REQUEST['includeType'] === 'audio') {
                    return true;
                }
            }
            return false;
        }

        public static function forceArticle()
        {
            if (!empty($_REQUEST['includeType'])) {
                if ($_REQUEST['includeType'] === 'article') {
                    return true;
                }
            }
            return false;
        }

        public static function getIncludeType($video)
        {
            if (self::forceAudio()) {
                return 'audio';
            }
            if (self::forceArticle()) {
                return 'article';
            }
            $vType = $video['type'];
            if ($vType == 'linkVideo') {
                if (!preg_match('/m3u8/', $video['videoLink'])) {
                    $vType = isHTMLPage($video['videoLink']) ? 'embed' : 'video';
                } else {
                    $vType = 'video';
                }
            } elseif ($vType == 'live') {
                $vType = '../../plugin/Live/view/liveVideo';
            } elseif ($vType == 'linkAudio') {
                $vType = 'audio';
            }
            if (!in_array($vType, Video::$typeOptions)) {
                $vType = 'video';
            }
            return $vType;
        }

        private static function getFullTextSearch($columnsArray, $search, $connection = "OR")
        {
            global $global;
            $search = (xss_esc($search));
            $search = str_replace('&quot;', '"', $search);
            $search = mb_strtolower($search);
            if (empty($columnsArray) || empty($search)) {
                return "";
            }
            $sql = "(";
            $matches = [];
            foreach ($columnsArray as $value) {
                $matches[] = " (MATCH({$value}) AGAINST ('{$search}' IN NATURAL LANGUAGE MODE)) ";
            }
            $sql .= implode(" OR ", $matches);
            $sql .= ")";
            return "{$connection} {$sql}";
        }

        public static function getChangeVideoStatusButton($videos_id)
        {
            global $statusThatTheUserCanUpdate;
            $video = new Video('', '', $videos_id);

            /**
             *
             * @var string $status
             */
            $status = $video->getStatus();

            $buttons = [];
            $totalStatusButtons = count($statusThatTheUserCanUpdate);
            foreach ($statusThatTheUserCanUpdate as $key => $value) {
                $index = $key + 1;
                if ($index > $totalStatusButtons - 1) {
                    $index = 0;
                }
                $nextStatus = $statusThatTheUserCanUpdate[$index][0];
                $format = __("This video is %s, click here to make it %s");
                $statusIndex = $value[0];
                $statusColor = $value[1];
                $tooltip = sprintf($format, Video::$statusDesc[$statusIndex], Video::$statusDesc[$nextStatus]);

                $buttons[] = "<button type=\"button\" style=\"color: {$statusColor}\" class=\"btn btn-default btn-xs getChangeVideoStatusButton_{$statusIndex}\"  onclick=\"changeVideoStatus({$videos_id}, '{$nextStatus}');return false\"  "
                    . "type=\"button\" nextStatus=\"{$nextStatus}\"  data-toggle=\"tooltip\" title=" . printJSString($tooltip, true) . ">"
                    . str_replace("'", '"', Video::$statusIcons[$statusIndex]) . "</button>";
            }

            return "<span class='getChangeVideoStatusButton getChangeVideoStatusButton_{$videos_id} status_{$status}'>" . implode('', $buttons) . "</span>";
        }

        public static function canVideoBePurchased($videos_id)
        {
            global $global;
            $obj = new stdClass();
            $obj->plugin = '';
            $obj->buyURL = '';
            $obj->canVideoBePurchased = false;
            // check for Subscription plugin
            if (AVideoPlugin::isEnabledByName('Subscription')) {
                $sub = new Subscription();
                $plans = $sub->getPlansFromVideo($videos_id);
                if (!empty($plans)) {
                    $obj->plugin = 'Subscription';
                    $obj->buyURL = "{$global['webSiteRootURL']}plugin/Subscription/showPlans.php?videos_id={$videos_id}";
                    $obj->canVideoBePurchased = true;
                    return $obj;
                }
            }

            // check for PPV plugin
            if (AVideoPlugin::isEnabledByName('PayPerView')) {
                if (PayPerView::isVideoPayPerView($videos_id) || $obj->onlyPlayVideosWithPayPerViewActive) {
                    $url = "{$global['webSiteRootURL']}plugin/PayPerView/page/buy.php";
                    if (isSerie()) {
                        $redirectUri = getSelfURI();
                    } else {
                        $redirectUri = getRedirectToVideo($videos_id);
                    }
                    if (!empty($redirectUri)) {
                        $url = addQueryStringParameter($url, 'redirectUri', $redirectUri);
                    }
                    $url = addQueryStringParameter($url, 'videos_id', $videos_id);
                    $obj->plugin = 'PayPerView';
                    $obj->buyURL = $url;
                    $obj->canVideoBePurchased = true;
                    return $obj;
                }
            }

            // check for fansSubscription
            if (AVideoPlugin::isEnabledByName('FansSubscriptions')) {
                if (FansSubscriptions::hasPlansFromVideosID($videos_id)) {
                    $url = "{$global['webSiteRootURL']}plugin/FansSubscriptions/View/buy.php";
                    if (isSerie()) {
                        $redirectUri = getSelfURI();
                    } else {
                        $redirectUri = getRedirectToVideo($videos_id);
                    }
                    if (!empty($redirectUri)) {
                        $url = addQueryStringParameter($url, 'redirectUri', $redirectUri);
                    }
                    $url = addQueryStringParameter($url, 'videos_id', $videos_id);
                    $obj->plugin = 'FansSubscriptions';
                    $obj->buyURL = $url;
                    $obj->canVideoBePurchased = true;
                    return $obj;
                }
            }
            return false;
        }

        public static function getCreatorHTML($users_id, $html = '', $small = false, $ignoreLinks = false)
        {
            if (empty($users_id)) {
                return '';
            }
            global $global;
            if ($small) {
                $template = $global['systemRootPath'] . 'view/videoCreatorSmall.html';
            } else {
                $template = $global['systemRootPath'] . 'view/videoCreator.html';
            }

            require_once $global['systemRootPath'] . 'objects/subscribe.php';
            $content = local_get_contents($template);
            $name = User::getNameIdentificationById($users_id);

            $search = [
                '{photo}',
                '{channelLink}',
                '{name}',
                '{icon}',
                '{subscriptionButton}',
                '{html}'
            ];

            if ($ignoreLinks) {
                $channelLink = '#';
            } else {
                $channelLink = User::getChannelLink($users_id);
            }

            $replace = [
                User::getPhoto($users_id),
                $channelLink,
                strip_tags($name),
                User::getEmailVerifiedIcon($users_id),
                Subscribe::getButton($users_id),
                $html,
            ];

            $btnHTML = str_replace($search, $replace, $content);
            return $btnHTML;
        }

        public static function getVideosListItem($videos_id, $divID = '', $style = '')
        {
            global $global, $advancedCustom;
            $get = [];
            $get = ['channelName' => @$_GET['channelName'], 'catName' => @$_REQUEST['catName']];

            if (empty($divID)) {
                $divID = "divVideo-{$videos_id}";
            }
            $objGallery = AVideoPlugin::getObjectData("Gallery");
            $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
            $template = $global['systemRootPath'] . 'view/videosListItem.html';
            $templateContent = file_get_contents($template);
            $value = Video::getVideoLight($videos_id);
            $link = Video::getLink($value['id'], $value['clean_title'], "", $get);
            if (!empty($_GET['page']) && $_GET['page'] > 1) {
                $link = addQueryStringParameter($link, 'page', $_GET['page']);
            }

            $title = safeString($value['title']);

            $thumbsImage = Video::getVideoImagewithHoverAnimationFromVideosId($value);

            $loggedUserHTML = '';
            if (User::isLogged() && !empty($program)) {
                $value['favoriteId'] = self::getFavoriteIdFromUser(User::getId());
                $value['watchLaterId'] = self::getWatchLaterIdFromUser(User::getId());
                if (!empty($value['isWatchLater'])) {
                    $watchLaterBtnAddedStyle = '';
                    $watchLaterBtnStyle = "display: none;";
                } else {
                    $watchLaterBtnAddedStyle = "display: none;";
                    $watchLaterBtnStyle = '';
                }
                if (!empty($value['isFavorite'])) {
                    $favoriteBtnAddedStyle = '';
                    $favoriteBtnStyle = "display: none;";
                } else {
                    $favoriteBtnAddedStyle = "display: none;";
                    $favoriteBtnStyle = '';
                }
                $loggedUserHTML = '<!-- getVideosListItem --><div class="galleryVideoButtons ' . getCSSAnimationClassAndStyle('animate__flipInY', uniqid(), 0) . '">';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', false, ' . $value['watchLaterId'] . ');return false;" '
                    . 'class="btn btn-dark btn-xs watchLaterBtnAdded watchLaterBtnAdded' . $value['id'] . '" '
                    . 'title="' . __("Added On Watch Later") . '" style="color: #4285f4;' . $watchLaterBtnAddedStyle . '" ><i class="fas fa-check"></i></button> ';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', true, ' . $value['watchLaterId'] . ');return false;" class="btn btn-dark btn-xs watchLaterBtn watchLaterBtn' . $value['id'] . '" title=' . printJSString('Watch Later', true) . ' style="' . $watchLaterBtnStyle . '" ><i class="fas fa-clock"></i></button>';
                $loggedUserHTML .= '<br>';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', false, ' . $value['favoriteId'] . ');return false;" class="btn btn-dark btn-xs favoriteBtnAdded favoriteBtnAdded' . $value['id'] . '" title=' . printJSString('Added On Favorite', true) . ' style="color: #4285f4; ' . $favoriteBtnAddedStyle . '"><i class="fas fa-check"></i></button>  ';
                $loggedUserHTML .= '<button onclick="addVideoToPlayList(' . $value['id'] . ', true, ' . $value['favoriteId'] . ');return false;" class="btn btn-dark btn-xs favoriteBtn favoriteBtn' . $value['id'] . '" title=' . printJSString('Favorite', true) . ' style="' . $favoriteBtnStyle . '" ><i class="fas fa-heart" ></i></button>    ';
                $loggedUserHTML .= '</div>';
            }
            //$progress = self::getVideoPogressPercent($value['id']);
            $category = new Category($value['categories_id']);

            $categoryLink = $category->getLink();
            $categoryIcon = $category->getIconClass();
            $category = $category->getName();
            $tagsHTML = Video::getTagsHTMLLabelIfEnable($value['id']);
            //var_dump($value['id'], $tagsHTML);exit;
            $viewsHTML = '';

            if (empty($advancedCustom->doNotDisplayViews)) {
                if (AVideoPlugin::isEnabledByName('LiveUsers')) {
                    $viewsHTML = '<div class="text-muted pull-right" style="display:flex;">' . getLiveUsersLabelVideo($value['id'], $value['views_count']) . '</div>';
                } else {
                    $viewsHTML = '<div class="text-muted pull-right"><i class="fas fa-eye"></i> ' . number_format($value['views_count'], 0) . '</div>';
                }
            }
            $creator = self::getCreatorHTML($value['users_id'], '', true);
            $search = [
                '{style}',
                '{divID}',
                '{link}',
                '{title}',
                '{thumbsImage}',
                '{loggedUserHTML}',
                '{categoryLink}',
                '{categoryIcon}',
                '{category}',
                '{tagsHTML}',
                '{viewsHTML}',
                '{creator}'
            ];

            $replace = [
                $style,
                $divID,
                $link,
                getSEOTitle($title),
                $thumbsImage,
                $loggedUserHTML,
                $categoryLink,
                $categoryIcon,
                $category,
                $tagsHTML,
                $viewsHTML,
                $creator,
            ];
            $btnHTML = @str_replace(
                $search,
                $replace,
                $templateContent
            );
            return $btnHTML;
        }

        static function getVideoImagewithHoverAnimationFromVideosId($videos_id, $addThumbOverlay = true, $addLink = true, $galeryDetails = false, $preloadImage = false, $doNotUseAnimatedGif = false)
        {
            if (empty($videos_id)) {
                return '';
            }
            if (is_array($videos_id)) {
                $video = $videos_id;
                $videos_id = $video['id'];
            } else {
                $video = Video::getVideoLight($videos_id);
            }
            if (empty($video['images'])) {
                $images = object_to_array(Video::getImageFromFilename($video['filename'], $video['type']));
            } else {
                $images = object_to_array($video['images']);
            }
            //var_dump($videos_id, $video, $images);
            $img = getVideoImagewithHoverAnimation($images['poster'], $images['thumbsGif'], $video['title'], $preloadImage, $doNotUseAnimatedGif);
            $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
            $isserie = Video::isSerie($videos_id);
            $isserieClass = "";
            if ($isserie) {
                $isserieClass = "isserie";
            }

            if (isToShowDuration($video['type'])) {
                $duration = Video::getCleanDuration($video['duration']);
                if (self::isValidDuration($duration)) {
                    $img .= "<time class=\"duration\" "
                        . "itemprop=\"duration\" "
                        . "datetime=\"" . Video::getItemPropDuration($video['duration']) . "\" >"
                        . $duration . "</time>";
                }
            }
            $progress = Video::getVideoPogressPercent($video['id']);
            //var_dump($video['id'], $progress);
            $img .= "<div class=\"progress\">"
                . "<div class=\"progress-bar progress-bar-danger\" role=\"progressbar\" "
                . "style=\"width: {$progress['percent']}%;\" "
                . "aria-valuenow=\"{$progress['percent']}\" "
                . "aria-valuemin=\"0\" aria-valuemax=\"100\"></div></div>";
            if ($addThumbOverlay) {
                $img .= AVideoPlugin::thumbsOverlay($videos_id);
            }
            $alternativeLink = '';
            if ($galeryDetails && !empty($program) && $isserie) {
                $alternativeLink = PlayLists::getLink($video['serie_playlists_id']);
                $plids = PlayList::getVideosIDFromPlaylistLight($video['serie_playlists_id']);
                $totalPL = count($plids);
                $img .= '<div class="gallerySerieOverlay"><div class="gallerySerieOverlayTotal">' . $totalPL . '<br><i class="fas fa-list"></i></div><i class="fas fa-play"></i>' . __("Play All") . '</div>';
            }
            $galleryVideoButtons = '';
            if (!empty($program) && User::isLogged()) {
                $isFavorite = self::isFavorite($videos_id);
                $isWatchLater = self::isWatchLater($videos_id);
                $favoriteId = self::getFavoriteIdFromUser(User::getId());
                $watchLaterId = self::getWatchLaterIdFromUser(User::getId());
                if ($isWatchLater) {
                    $watchLaterBtnAddedStyle = "";
                    $watchLaterBtnStyle = "display: none;";
                } else {
                    $watchLaterBtnAddedStyle = "display: none;";
                    $watchLaterBtnStyle = "";
                }
                if ($isFavorite) {
                    $favoriteBtnAddedStyle = "";
                    $favoriteBtnStyle = "display: none;";
                } else {
                    $favoriteBtnAddedStyle = "display: none;";
                    $favoriteBtnStyle = "";
                }

                $galleryDropDownMenu = Gallery::getVideoDropdownMenu($videos_id);
                $galleryVideoButtons .= '
                <!-- getVideoImagewithHoverAnimationFromVideosId --><div class="galleryVideoButtons ' . getCSSAnimationClassAndStyle('animate__flipInY', uniqid(), 0) . '">
                    <button onclick="addVideoToPlayList(' . $videos_id . ', false, ' . $watchLaterId . ');return false;" class="btn btn-dark btn-xs watchLaterBtnAdded watchLaterBtnAdded' . $videos_id . '" data-toggle="tooltip" data-placement="left" title=' . printJSString("Added On Watch Later", true) . ' style="color: #4285f4;' . $watchLaterBtnAddedStyle . '" ><i class="fas fa-check"></i></button>
                    <button onclick="addVideoToPlayList(' . $videos_id . ', true, ' . $watchLaterId . ');return false;" class="btn btn-dark btn-xs watchLaterBtn watchLaterBtn' . $videos_id . '" data-toggle="tooltip" data-placement="left" title=' . printJSString("Watch Later", true) . ' style="' . $watchLaterBtnStyle . '" ><i class="fas fa-clock"></i></button>
                    <br>
                    <button onclick="addVideoToPlayList(' . $videos_id . ', false, ' . $favoriteId . ');return false;" class="btn btn-dark btn-xs favoriteBtnAdded favoriteBtnAdded' . $videos_id . '" data-toggle="tooltip" data-placement="left" title=' . printJSString("Added On Favorite", true) . ' style="color: #4285f4; ' . $favoriteBtnAddedStyle . '"><i class="fas fa-check"></i></button>
                    <button onclick="addVideoToPlayList(' . $videos_id . ', true, ' . $favoriteId . ');return false;" class="btn btn-dark btn-xs favoriteBtn favoriteBtn' . $videos_id . ' faa-parent animated-hover" data-toggle="tooltip" data-placement="left" title=' . printJSString("Favorite", true) . ' style="' . $favoriteBtnStyle . '" ><i class="fas fa-heart faa-pulse faa-fast" ></i></button>
                    <br>';

                if (Video::canEdit($videos_id)) {
                    $galleryVideoButtons .= '
                    <button onclick="avideoModalIframe(webSiteRootURL + \'view/managerVideosLight.php?image=1&avideoIframe=1&videos_id=' . $videos_id . '\');return false;" class="btn btn-dark btn-xs" data-toggle="tooltip" data-placement="left" title=' . printJSString("Edit Thumbnail", true) . '><i class="fas fa-edit"></i></button>
                    <br>';
                }

                $galleryVideoButtons .= $galleryDropDownMenu . '</div>';
            }
            $href = Video::getLink($video['id'], $video['clean_title']);
            $embed = Video::getLink($video['id'], $video['clean_title'], true);
            $title = safeString($video['title']);
            $a = '<a videos_id="' . $videos_id . '"
                                       href="' . $href . '"
                                       embed="' . $embed . '"
                                       title="' . $title . '" alternativeLink="' . $alternativeLink . '">';
            if ($addLink) {
                $img = $a . $img . '</a>';
            }

            $galeryDetailsHTML = '';
            if ($galeryDetails) {
                $galeryDetailsHTML = '<strong class="title">' . getSEOTitle($title) . '</strong>';
            }

            return '<div class="thumbsImageContainer ' . $isserieClass . '"><div class="aspectRatio16_9">' . $img . '</div>' . $galleryVideoButtons . $galeryDetailsHTML . '</div>';
        }

        public function getTotal_seconds_watching()
        {
            return $this->total_seconds_watching;
        }

        public function setTotal_seconds_watching($total_seconds_watching)
        {
            $this->total_seconds_watching = $total_seconds_watching;
        }

        public function getDuration_in_seconds()
        {
            return $this->duration_in_seconds;
        }

        public function setDuration_in_seconds($duration_in_seconds)
        {
            $this->duration_in_seconds = intval($duration_in_seconds);
        }

        public function getLikes()
        {
            return $this->likes;
        }

        public function getDislikes()
        {
            return $this->dislikes;
        }

        public function setLikes($likes): void
        {
            $this->likes = intval($likes);
        }

        public function setDislikes($dislikes): void
        {
            $this->dislikes = intval($dislikes);
        }

        /**
         *
         * @param string $videos_id
         * @param string $type [like or dislike]
         * @param string $value
         * @return boolean
         *
         * automatic = will get from like table
         * +1 = add one
         * -1 = remove one
         * any number = will change the database
         */
        public static function updateLikesDislikes($videos_id, $type, $value = 'automatic')
        {
            global $config, $global, $_updateLikesDislikes;
            if ($config->currentVersionLowerThen('11.5')) {
                return false;
            }

            $index = "$videos_id, $type, $value";
            if (!isset($_updateLikesDislikes)) {
                $_updateLikesDislikes = [];
            }

            if (isset($_updateLikesDislikes[$index])) {
                return $_updateLikesDislikes[$index];
            }

            require_once $global['systemRootPath'] . 'objects/like.php';
            $videos_id = intval($videos_id);
            if (empty($videos_id)) {
                return false;
            }

            if (mb_strtolower($type) == 'likes') {
                $type = 'likes';
            } else {
                $type = 'dislikes';
            }
            //var_dump($videos_id, $type, $value);
            $sql = "UPDATE videos SET ";
            if ($value === 'automatic') {
                $likes = Like::getLikes($videos_id);
                return self::updateLikesDislikes($videos_id, $type, $likes->$type);
            } elseif (preg_match('/\+([0-9]+)/', $value, $matches)) {
                $value = intval($matches[1]);
                $sql .= " {$type} = {$type}+{$value} ";
            } elseif (preg_match('/-([0-9]+)/', $value, $matches)) {
                $value = intval($matches[1]);
                $sql .= " {$type} = {$type}-{$value} ";
            } else {
                $value = intval($value);
                $sql .= " {$type} = {$value} ";
            }
            $sql .= ", modified = now() WHERE id = {$videos_id}";
            //secho $sql.PHP_EOL;
            $saved = sqlDAL::writeSql($sql);
            self::clearCache($videos_id);
            $_updateLikesDislikes[$index] = $value;
            return $value;
        }

        public static function checkIfIsBroken($videos_id)
        {
            $video = new Video('', '', $videos_id);
            if (!empty($video->getSerie_playlists_id())) {
                return false;
            }
            if ($video->getStatus() == Video::$statusActive || $video->getStatus() == Video::$statusUnlisted || $video->getStatus() == Video::$statusUnlistedButSearchable) {
                if ($video->getType() == 'audio' || $video->getType() == 'video') {
                    if (self::isMediaFileMissing($video->getFilename())) {
                        _error_log("Video::checkIfIsBroken($videos_id) true " . $video->getFilename());
                        $video->setStatus(Video::$statusBrokenMissingFiles);
                        Video::clearCache($videos_id);
                        return true;
                    }
                }
            }
            return $video->getStatus() == Video::$statusBrokenMissingFiles;
        }

        public static function isMediaFileMissing($filename, $cacheCleared = false)
        {
            $sources = getVideosURL_V2($filename);
            $search = ['mp3', 'mp4', 'm3u8', 'webm'];
            $found = false;
            foreach ($sources as $key => $value1) {
                foreach ($search as $value2) {
                    if (preg_match("/^{$value2}/i", $key)) {
                        $found = true;
                        break 2;
                    }
                }
            }

            if (!$cacheCleared && !$found) {
                global $getVideosURL_V2Array;
                ObjectYPT::deleteCache("getVideosURL_V2$filename");
                unset($getVideosURL_V2Array);
                return self::isMediaFileMissing($filename, true);
            }

            return !$found;
        }

        public static function getTableName()
        {
            return 'videos';
        }

        static function getSeoTags($videos_id)
        {
            global $advancedCustom, $_getSeoTags;

            if (!isset($_getSeoTags)) {
                $_getSeoTags = [];
            }

            if (!empty($_getSeoTags[$videos_id])) {
                return $_getSeoTags[$videos_id];
            }

            $video = new Video('', '', $videos_id);

            $H1_title = getSEOTitle($video->getTitle());

            $externalOptions = _json_decode($video->getExternalOptions());
            $SEO = @$externalOptions->SEO;
            //var_dump($externalOptions);exit;
            if (!empty($SEO)) {
                $H2_Short_summary = getSEODescription($SEO->ShortSummary);
                $MetaDescription = getSEODescription($SEO->MetaDescription);
            } else {
                $H2_Short_summary = '';
                $MetaDescription = getSEODescription(emptyHTML($video->getDescription()) ? $video->getTitle() : $video->getDescription());
            }

            $keywords = strip_tags($advancedCustom->keywords);
            if (AVideoPlugin::isEnabledByName('VideoTags')) {
                //$keywords .= ", $videos_id";
                $tags = VideoTags::getArrayFromVideosId($videos_id);
                if (!empty($tags)) {
                    if (!empty($keywords)) {
                        $keywords .= ', ';
                    }
                    $keywords .= implode(', ', $tags);
                }
            }

            $image = Video::getImageFromID($videos_id);

            $tags = [
                'h1' => $H1_title,
                'h2' => $H2_Short_summary,
            ];
            $meta = [
                'description' => $MetaDescription,
                'keywords' => $keywords,
                'author' => User::getNameIdentificationById($video->getUsers_id())
            ];
            $itemprops = [
                'name' => $H1_title,
                'thumbnailUrl' => $image->default['url'],
                'contentURL' => Video::getLink($videos_id, $video->getClean_title()),
                'embedURL' => Video::getLink($videos_id, $video->getClean_title(), true),
                'uploadDate' => $video->getCreated(),
                'description' => $MetaDescription
            ];

            $head = '';
            foreach ($meta as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $head .= '<meta name="' . $key . '" content=' . printJSString($value, true) . '>';
            }
            $body = '<div class="SeoTags" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
            foreach ($tags as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $body .= "<{$key}>{$value}</{$key}>";
            }

            foreach ($itemprops as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $body .= "<span itemprop=\"{$key}\" content=\"" . str_replace('"', '', $value) . "\"></span>";
            }
            $body .= '</div>';
            $response = [];
            $response['assets'] = ['tags' => $tags, 'meta' => $meta, 'itemprops' => $itemprops];
            $response['head'] = $head;
            $response['body'] = $body;
            $_getSeoTags[$videos_id] = $response;
            //var_dump($_getSeoTags);exit;
            return $_getSeoTags[$videos_id];
        }

        public function getEpg_link()
        {
            return $this->epg_link;
        }

        public function setEpg_link($epg_link): void
        {
            $this->epg_link = $epg_link;
        }

        static public function getAllActiveEPGs()
        {
            global $config;
            $sql = "SELECT * FROM `videos` WHERE status = '" . Video::$statusActive . "' "
                . "AND `type` = 'linkVideo' "
                . "AND epg_link IS NOT NULL "
                . "AND epg_link != ''";
            $res = sqlDAL::readSql($sql);
            $fullResult2 = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);

            $rows = [];
            if ($res !== false) {
                foreach ($fullResult2 as $row) {
                    $rows[] = $row;
                }
            } else {
                //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            return $rows;
        }

        static public function getEPG($videos_id)
        {
            global $config, $_getEPG;

            if (!isset($_getEPG)) {
                $_getEPG = [];
            }

            if (!isset($_getEPG[$videos_id])) {
                $sql = "SELECT * FROM `videos` WHERE id = ? AND `type` = 'linkVideo' AND epg_link IS NOT NULL AND epg_link != ''";
                $res = sqlDAL::readSql($sql, 'i', [$videos_id]);

                $video = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                if (empty($video) || !isValidURL($video['epg_link'])) {
                    $_getEPG[$videos_id] = false;
                } else {
                    $_getEPG[$videos_id] = $video['epg_link'];
                }
            }
            return $_getEPG[$videos_id];
        }

        static public function getEPGLink($videos_id)
        {
            global $global;
            $url = $global['webSiteRootURL'] . 'plugin/PlayerSkins/epg.php';
            if (!empty($videos_id)) {
                $epg = self::getEPG($videos_id);
                if (!empty($epg)) {
                    $url = addQueryStringParameter($url, 'videos_id', $videos_id);
                    return $url;
                } else {
                    return false;
                }
            }
            return $url;
        }

        static public function getVideoWithMoreViews(){
            global $_getVideoWithMoreViews;
            if(empty($_getVideoWithMoreView)){
                $sql = 'SELECT * FROM `videos` ORDER BY `views_count` DESC LIMIT 1';
                $res = sqlDAL::readSql($sql);
                $_getVideoWithMoreViews = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
            }
            return $_getVideoWithMoreViews;
        }
    }
}
// Just to convert permalink into clean_title
if (!empty($_GET['v']) && empty($_GET['videoName'])) {
    $_GET['videoName'] = Video::get_clean_title($_GET['v']);
}
global $statusThatShowTheCompleteMenu, $statusSearchFilter, $statusThatTheUserCanUpdate;
$statusThatShowTheCompleteMenu = [
    Video::$statusActive,
    Video::$statusInactive,
    Video::$statusScheduledReleaseDate,
    Video::$statusActiveAndEncoding,
    Video::$statusUnlistedButSearchable,
    Video::$statusUnlisted,
    Video::$statusFansOnly,
];

$statusSearchFilter = [
    Video::$statusActive,
    Video::$statusInactive,
    Video::$statusScheduledReleaseDate,
    Video::$statusEncoding,
    Video::$statusTranfering,
    Video::$statusUnlisted,
    Video::$statusUnlistedButSearchable,
    Video::$statusBrokenMissingFiles,
];

$statusThatTheUserCanUpdate = [
    [Video::$statusActive, '#0A0'],
    [Video::$statusInactive, '#B00'],
    [Video::$statusUnlisted, '#AAA'],
    [Video::$statusUnlistedButSearchable, '#BBB'],
];
