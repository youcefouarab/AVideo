<?php
if (empty($global)) {
    $global=[];
}
if (empty($global['systemRootPath'])) {
    require_once dirname(__FILE__) . '/../videos/configuration.php';
    require_once '../objects/Encoder.php';
    require_once '../objects/Login.php';
}
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($extension), $global['allowed'])) {
        $obj->msg = "File extension error (" . $_FILES['upl']['name'] . "), we allow only (" . implode(",", $global['allowed']) . ")";
        die(json_encode($obj));
    }

    //echo "Success: file extension OK\n";
    //chack if is an audio
    $type = "video";
    if (strcasecmp($extension, 'mp3') == 0 || strcasecmp($extension, 'wav') == 0) {
        $type = 'audio';
    }

    $path_parts = pathinfo($_FILES['upl']['name']);
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($path_parts['filename']));
    $filename = uniqid($mainName . "_YPTuniqid_", true);

    $destinationFile = "{$global['systemRootPath']}videos/original_" . $filename;
    $destinationFileURI = "{$global['webSiteRootURL']}videos/original_" . $filename;
    if (!empty($forceRename)) {
        if (!rename($_FILES['upl']['tmp_name'], $destinationFile)) {
            $obj->msg = "Error on rename(" . $_FILES['upl']['tmp_name'] . ",  {$destinationFile})";
            die(json_encode($obj));
        }
    } else {
        if (!move_uploaded_file($_FILES['upl']['tmp_name'], $destinationFile)) {
            $obj->msg = "Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ",  {$destinationFile})";
            die(json_encode($obj));
        }
    }

    $e = new Encoder("");
    if (!Login::canUpload()) {
        $obj->msg = "This user can not upload files";
    } elseif (!($streamers_id = Login::getStreamerId())) {
        $obj->msg = "There is no streamer site";
    } else {
        $e->setStreamers_id($streamers_id);
        $s = new Streamer($streamers_id);
        $e->setTitle($path_parts['filename']);
        $e->setFileURI($destinationFileURI);
        $e->setFilename($filename);
        $e->setStatus(Encoder::$STATUS_QUEUE);
        $e->setPriority($s->getPriority());
        //$e->setNotifyURL($global['AVideoURL'] . "aVideoEncoder.json");
        //error_log("Upload.php will set format");
        if ($type == "video") {
            if (!empty($_POST['audioOnly']) && $_POST['audioOnly'] !== 'false') {
                if (!empty($_POST['spectrum']) && $_POST['spectrum'] !== 'false') {
                    error_log("Upload.php set format 11");
                    $e->setFormats_id(11); // video to spectrum [(6)MP4 to MP3] -> [(5)MP3 to spectrum] -> [(2)MP4 to webm]
                } else {
                    error_log("Upload.php set format 12");
                    $e->setFormats_id(12);
                }
            } else {
                error_log("Upload.php will let function decide decideFormatOrder");
                $e->setFormats_idFromOrder(decideFormatOrder());
            }
        } else {
            if (!empty($_POST['inputAutoHLS']) && strtolower($_POST['inputAutoHLS']) !== "false") {
                error_log("Upload.php set format 33");
                $e->setFormats_id(33);
            } elseif (!empty($_POST['inputAutoMP4']) && strtolower($_POST['inputAutoMP4']) !== "false") {
                error_log("Upload.php set format 33");
                $e->setFormats_id(34);
            } elseif (empty($global['disableWebM']) && !empty($_POST['inputAutoWebm']) && strtolower($_POST['inputAutoWebm']) !== "false") {
                error_log("Upload.php set format 35");
                $e->setFormats_id(35);
            } elseif (!empty($_POST['spectrum']) && $_POST['spectrum'] !== 'false') {
                error_log("Upload.php set format 5");
                $e->setFormats_id(5);
            } else {
                error_log("Upload.php set format 3");
                $e->setFormats_id(3);
            }
        }
        if (!empty($_POST['override_status'])) {
            $e->setOverride_status($_POST['override_status']);
        }

        $obj = new stdClass();
        $obj->videos_id = 0;
        $obj->video_id_hash = '';
        $f = new Format($e->getFormats_id());
        $format = $f->getExtension();

        if (!empty($_POST['update_video_id'])) {
            $obj->videos_id = $_POST['update_video_id'];
        } else {
            $obj->videos_id = 0;
        }

        $obj->releaseDate = @$_REQUEST['releaseDate'];

        // This raises an harmless error
        error_log("Upload.php line: " . __LINE__ . ' ' . json_encode($format));
        $response = Encoder::sendFile('', $obj, $format, $e);
        if (!empty($response->response->video_id)) {
            $obj->videos_id = $response->response->video_id;
        }
        if (!empty($response->response->video_id_hash)) {
            $obj->video_id_hash = $response->response->video_id_hash;
        }
        $e->setReturn_vars(json_encode($obj));

        if (!empty($global['progressiveUpload'])) {
            Encoder::sendFile($destinationFile, $obj, $format, $e, 'HD');
        }

        $encoders_ids[] = $e->save();

        $obj->error = false;
        $obj->msg = "Your file ($filename) is queue";
    }
    die(json_encode($obj));
}

$obj->msg = print_r($_FILES, true);
die(json_encode($obj));
