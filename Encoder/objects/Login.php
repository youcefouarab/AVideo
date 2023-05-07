<?php

header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');
if (!class_exists('Login')) {
    require_once dirname(__FILE__) . '/../videos/configuration.php';
    require_once dirname(__FILE__) . '/Streamer.php';

    class Login {

        static function run(
            $user,
            #[\SensitiveParameter]
            $pass,
            $aVideoURL,
            $encodedPass = false
        ) {
            global $_runLogin;
            $index = "$user, $pass, $aVideoURL";
            if (!isset($_runLogin)) {
                $_runLogin = array();
            }
            if (empty($_runLogin[$index])) {
                ini_set('memory_limit', '50M');
                ini_set('max_execution_time', 10);
                error_log("Login::run ($user, ***, $aVideoURL, $encodedPass)");
                global $global;
                $agent = getSelfUserAgent();
                $aVideoURL = trim($aVideoURL);
                if (substr($aVideoURL, -1) !== '/') {
                    $aVideoURL .= "/";
                }
                $postdata = http_build_query(
                        array(
                            'user' => $user,
                            'pass' => $pass,
                            'encodedPass' => $encodedPass
                        )
                );

                $opts = array(
                    "ssl" => array(
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                        "allow_self_signed" => true
                    ),
                    'http' => array(
                        'method' => 'POST',
                        'header' => array(
                            "Content-type: application/x-www-form-urlencoded\r\n",
                            "User-Agent: {$agent}\r\n"),
                        'content' => $postdata
                    ),
                );

                $context = stream_context_create($opts);
                $url = $aVideoURL . 'login?user=' . urlencode($user) . '&pass=' . urlencode($pass) . '&encodedPass=' . urlencode($encodedPass);

                //echo $url;exit;
                error_log("Login::run request login user ($user)");
                $result = url_get_contents($url, $context);
                error_log("Login::run request login complete user ($user)");
                if (empty($result)) {
                    error_log("Get Login fail, try again user ($user)");
                    $result = url_get_contents($url);
                }

                //error_log("Login::run response: ($result)");
                if (empty($result)) {
                    $object = new stdClass();
                    $object->streamer = false;
                    $object->streamers_id = 0;
                    $object->isLogged = false;
                    $object->isStreamerAdmin = false;
                    $object->isAdmin = false;
                    $object->canUpload = false;
                    $object->canComment = false;
                    $object->canCreateCategory = false;
                    $object->theme = '';
                    $object->categories = array();
                    $object->userGroups = array();
                    error_log("Login::run Error on Login context");
                    error_log($url);
                    //error_log($result);
                } else {
                    $result = remove_utf8_bom($result);
                    $object = json_decode($result);
                    if (!empty($object)) {
                        error_log("Login::run got an object");
                        $object->streamer = $aVideoURL;
                        $object->streamers_id = 0;
                        if (!empty($object->canUpload) || !empty($object->isAdmin)) {
                            $object->streamers_id = Streamer::createIfNotExists($user, $pass, $aVideoURL, $encodedPass);
                        }
                        if ($object->streamers_id) {
                            $s = new Streamer($object->streamers_id);
                            $resultV = $s->verify();
                            if (!empty($resultV) && !$resultV->verified) {
                                error_log("Error on Login not verified");
                                return false;
                            }

                            $object->isStreamerAdmin = $object->isAdmin;
                            $object->isAdmin = $s->getIsAdmin();
                            if (!$encodedPass || $encodedPass === 'false') {
                                $pass = encryptPassword($pass, $aVideoURL);
                            }
                            // update pass
                            $s->setPass($object->pass);
                            $s->save();
                            $cookieLife = time() + 3600 * 24 * 2; // 2 day
                            setcookie("encoder_user", $user, $cookieLife, "/");
                            setcookie("encoder_pass", $pass, $cookieLife, "/");
                            setcookie("aVideoURL", $aVideoURL, $cookieLife, "/");
                            error_log("Login:: almost done");
                        }
                    } else {
                        $object = new stdClass();
                        error_log("Encoder Login Error: " . json_error()." $result");
                    }
                }
                $object->aVideoURL = $url;
                $object->result = $result;
            } else {
                $object = $_runLogin[$index];
            }
            _session_start();
            $object->PHPSESSID = session_id(); // to allow cross domain logins
            $_SESSION['login'] = $object;
            error_log("Login:: done session_id = " . session_id() . " session_login ");
        }

        static function logoff() {
            error_log("logoff:: done session_id = " . session_id());
            unset($_SESSION['login']);
            setcookie('encoder_user', null, -1, "/");
            setcookie('encoder_pass', null, -1, "/");
            unset($_COOKIE['encoder_user']);
            unset($_COOKIE['encoder_pass']);
        }

        static function isLogged() {
            $isLogged = !empty($_SESSION['login']->isLogged);
            if (!$isLogged && !empty($_COOKIE['encoder_user']) && !empty($_COOKIE['encoder_pass']) && !empty($_COOKIE['encoder_aVideoURL'])) {
                error_log("isLogged: Login::run");
                Login::run($_COOKIE['encoder_user'], $_COOKIE['encoder_pass'], $_COOKIE['encoder_aVideoURL'], true);
            } else if (!$isLogged && !empty($_SESSION['login'])) {
                error_log("isLogged: false ");
            }
            if (!empty($_GET['justLogin'])) {
                //$_GET['justLogin'] = $_SESSION['login']->isLogged;
                //error_log("isLogged:: session_login = " . json_encode($_SESSION['login']->isLogged));
            }
            return $isLogged;
        }

        static function isAdmin() {
            return !empty($_SESSION['login']->isAdmin);
        }

        static function isStreamerAdmin() {
            return !empty($_SESSION['login']->isStreamerAdmin);
        }

        static function canBulkEncode() {
            global $global;
            if (self::isAdmin() || empty($global['disableBulkEncode'])) {
                return true;
            }
            return false;
        }

        static function canUpload() {
            //error_log("canUpload: ". json_encode($_SESSION['login']));
            return self::isAdmin() || self::isStreamerAdmin() || (self::isLogged() && !empty($_SESSION['login']->canUpload));
        }

        static function canStream() {
            //error_log("canUpload: ". json_encode($_SESSION['login']));
            return self::isAdmin() || self::isStreamerAdmin() || (self::isLogged() && !empty($_SESSION['login']->canStream));
        }

        static function canComment() {
            return !empty($_SESSION['login']->canComment);
        }

        static function canCreateCategory() {
            return self::isStreamerAdmin() || !empty($_SESSION['login']->canCreateCategory);
        }

        static function getTheme() {
            return !empty($_SESSION['login']->theme);
        }

        static function getStreamerURL() {
            if (!static::isLogged()) {
                return Streamer::getFirstURL();
                ;
            }
            global $global;
            if (!empty($global['forceStreamerSiteURL'])) {
                return $global['forceStreamerSiteURL'];
            }
            return $_SESSION['login']->streamer;
        }

        static function getStreamerUser() {
            if (!static::isLogged()) {
                return false;
            }
            global $global;
            return $_SESSION['login']->user;
        }

        static function getStreamerPass() {
            if (!static::isLogged()) {
                return false;
            }
            global $global;
            return $_SESSION['login']->pass;
        }

        static function getStreamerUserId() {
            if (!static::isLogged()) {
                return false;
            }
            return intval($_SESSION['login']->id);
        }

        static function getStreamerId() {
            if (!static::isLogged()) {
                return 0;
            }
            return intval($_SESSION['login']->streamers_id);
        }

    }

}