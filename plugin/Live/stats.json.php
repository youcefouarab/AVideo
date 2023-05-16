<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';

/*
  if(!requestComesFromSafePlace()) {
  _error_log("Why are you requesting this ".getSelfURI()." ".json_encode($_SERVER));
  die();
  }
 *
 */

ini_set('max_execution_time', 10);
set_time_limit(10);
session_write_close();
$pobj = AVideoPlugin::getDataObjectIfEnabled("Live");
if (empty($pobj->server_type->value)) {
    ini_set('max_execution_time', 180);
    set_time_limit(180);
}
if (empty($pobj)) {
    die(json_encode("Plugin disabled"));
}
$live_servers_id = Live::getLiveServersIdRequest();
$cacheName = "getStats" . DIRECTORY_SEPARATOR . "live_servers_id_{$live_servers_id}" . DIRECTORY_SEPARATOR . "_statsCache_" . md5($global['systemRootPath'] . json_encode($_REQUEST));

/*
$cachefile = ObjectYPT::getCacheFileName($cacheName, false, $addSubDirs);
$cache = Cache::getCache($cacheName, $lifetime, $ignoreMetadata);
$c = @url_get_contents($cachefile);
var_dump($cachefile, $cache, $c);exit;
*/

$json = ObjectYPT::getCache($cacheName, $pobj->cacheStatsTimout, true);
//var_dump(ObjectYPT::getLastUsedCacheInfo(), $json);exit;
if (empty($json)) {
    $json = getStatsNotifications();
    //var_dump(ObjectYPT::getLastUsedCacheInfo(), $json);exit;
    ObjectYPT::setCache($cacheName, $json);
}
$json = object_to_array($json);

//var_dump($json);exit;
// check if application is online
if (!empty($_REQUEST['name'])) {
    $json['msg'] = 'OFFLINE';
    $json['name'] = $_REQUEST['name'];
    if (!empty($json['applications'])) {
        foreach ($json['applications'] as $value) {
            if (!empty($value['key']) && $value['key'] == $_REQUEST['name']) {
                $json['msg'] = 'ONLINE';
                break;
            }
        }
    }
    if (!empty($json['hidden_applications'])) {
        foreach ($json['hidden_applications'] as $value) {
            if (!empty($value['key']) && $value['key'] == $_REQUEST['name']) {
                $json['msg'] = 'ONLINE';
                break;
            }
        }
    }
}
$json['cache'] = ObjectYPT::getLastUsedCacheInfo();
echo json_encode($json);
