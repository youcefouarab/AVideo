<?php
// if you want to usea  third party app to submit video files, please check this https://github.com/WWBN/Upload-Sample
    
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$object = new stdClass();
$object->error = true;
$object->videos_id = 0;
if (!User::canUpload() && (empty($_REQUEST['user']) || empty($_REQUEST['pass']))) {
    $object->msg = "You need a user";
    die(json_encode($object));
}

$user = $_REQUEST['user'];
$password = $_REQUEST['pass'];

$userObj = new User(0, $user, $password);
$userObj->login(false, true);

if (!User::canUpload()) {
    $object->msg = "You can not upload [".User::getId()."] ".(User::getUserName());
    die(json_encode($object));
}

// A list of permitted file extensions
$allowed = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'mp3', 'wav', 'm4v', 'webm', 'wmv', 'mpg', 'mpeg', 'f4v', 'm4v', 'm4a', 'm2p', 'rm', 'vob', 'mkv', 'jpg', 'jpeg', 'gif', 'png', 'webp'];
_error_log("MOBILE UPLOAD: Starts");
if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
    _error_log("MOBILE UPLOAD: extension {$extension}");
    if (!in_array(strtolower($extension), $allowed)) {
        $object->msg = "File extension error (" . $_FILES['upl']['name'] . "), we allow only (" . implode(",", $allowed) . ")";
        _error_log("MOBILE UPLOAD: {$object->msg}");
        die(json_encode($object));
    }
    if ($image = isImage($_FILES['upl']['tmp_name'])) {
        $type = "image";
        $prefix = 'i';
        $extension = $image;
    } else {
        //chack if is an audio
        $type = "video";
        $prefix = 'v';
        if (strcasecmp($extension, 'mp3') == 0 || strcasecmp($extension, 'wav') == 0) {
            $type = 'audio';
            $prefix = 'a';
        }
    }

    require_once $global['systemRootPath'] . 'objects/video.php';
    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);

    // check if can upload video (about time limit storage)
    if (!empty($global['videoStorageLimitMinutes'])) {
        $maxDuration = $global['videoStorageLimitMinutes'] * 60;
        $currentStorageUsage = getSecondsTotalVideosLength();
        $thisFile = parseDurationToSeconds($duration);
        $limitAfterThisFile = $currentStorageUsage + $thisFile;
        if ($maxDuration < $limitAfterThisFile) {
            $object->msg = "Sorry, your storage limit has run out."
                    . "<br>[Max Duration: {$maxDuration} Seconds]"
                    . "<br>[Current Srotage Usage: {$currentStorageUsage} Seconds]"
                    . "<br>[This File Duration: {$thisFile} Seconds]"
                    . "<br>[Limit after this file: {$limitAfterThisFile} Seconds]";

            if (!empty($_FILES['upl']['videoId'])) {
                $video = new Video("", "", $_FILES['upl']['videoId']);
                $video->delete();
            }
            _error_log("MOBILE UPLOAD: {$object->msg}");
            die(json_encode($object));
        }
    }

    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($_FILES['upl']['name']));

    $paths = Video::getNewVideoFilename($prefix);
    $filename = $paths['filename'];

    $video = new Video(preg_replace("/_+/", " ", $_FILES['upl']['name']), $filename, 0);
    $video->setDuration($duration);
    $video->setType($type);

    if (!empty($_REQUEST['title'])) {
        $video->setTitle($_REQUEST['title']);
    }
    if (!empty($_REQUEST['description'])) {
        $video->setDescription($_REQUEST['description']);
    }
    if (!empty($_REQUEST['categories_id'])) {
        $video->setCategories_id($_REQUEST['categories_id']);
    }
    if (!empty($_REQUEST['can_share'])) {
        $video->setCan_share($_REQUEST['can_share']);
    }
    if (!empty($_REQUEST['video_password'])) {
        $video->setVideo_password($_REQUEST['video_password']);
    }
    if ($type == "image") {
        $video->setStatus(Video::$statusActive);
        make_path($paths['path']);
        $file = "{$paths['path']}{$paths['filename']}.{$extension}";
        if (!move_uploaded_file($_FILES['upl']['tmp_name'], $file)) {
            $object->msg = "Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ", " . $file . ")";
            _error_log("MOBILE UPLOAD IMAGE ERROR: ".  json_encode($object));
            die(json_encode($object));
        }
        $object->error = false;
        $object->msg = "your image was posted";
        $object->videos_id = $video->save();
    } else {
        $video->setStatus(Video::$statusEncoding);

        if (!move_uploaded_file($_FILES['upl']['tmp_name'], Video::getStoragePath()."original_" . $filename)) {
            $object->msg = "Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ", " . Video::getStoragePath()."original_" . $filename . ")";
            _error_log("MOBILE UPLOAD ERROR: ".  json_encode($object));
            die(json_encode($object));
        }
        $object->videos_id = $video->save();
        $video->queue();
        $object->error = false;
        $object->msg = "We sent your video to the encoder";
    }
    $object->posterSent = false;
    
    if(!empty($object->videos_id) && !empty($_REQUEST['base64PNG'])){
        $filePng = "{$paths['path']}{$paths['filename']}.png";
        $fileJpg = "{$paths['path']}{$paths['filename']}.jpg";
        $object->posterSent = saveBase64DataToPNGImage($_REQUEST['base64PNG'], $filePng);
        convertImage($filePng, $fileJpg, 90);
        Video::clearImageCache($paths['filename']);
    }
    
    _error_log("MOBILE SUCCESS UPLOAD: ".  json_encode($object));
    die(json_encode($object));
} else {
    _error_log("MOBILE UPLOAD: File Not exists - " . json_encode($_FILES));
}
