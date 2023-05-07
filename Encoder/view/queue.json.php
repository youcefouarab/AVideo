<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
require_once '../objects/Encoder.php';
require_once '../objects/Streamer.php';
require_once '../objects/Login.php';
header('Content-Type: application/json');
$rows = Encoder::getAll(true);
$resolutions = ['Low', 'SD', 'HD'];
if (!is_array($rows)) {
    $rows = [];
}
foreach ($rows as $key => $value) {
    $f = new Format($rows[$key]['formats_id']);
    $rows[$key]['format'] = $f->getName();
    $s = new Streamer($rows[$key]['streamers_id']);
    $rows[$key]['streamer'] = $s->getSiteURL();
    foreach ($resolutions as $value2) {
        $rows[$key]['fileInfo'] = Encoder::getAllFilesInfo($rows[$key]['id']);
        $files = Encoder::getTmpFiles($rows[$key]['id']);
        foreach ($files as $file) {
            $rows[$key]['mp4_filesize_'] = filesize($file);
        }

        $file_ = Encoder::getTmpFileName($rows[$key]['id'], 'm3u8', $value2);
        if (file_exists($file_)) {
            $rows[$key]['hls_filesize_' . $value2] = filesize($file_);
            $rows[$key]['hls_filesize_human_' . $value2] = humanFileSize($rows[$key]['mp4_filesize_' . $value2]);
        }

        $file_ = Encoder::getTmpFileName($rows[$key]['id'], 'zip', $value2);
        if (file_exists($file_)) {
            $rows[$key]['zip_filesize_' . $value2] = filesize($file_);
            $rows[$key]['zip_filesize_human_' . $value2] = humanFileSize($rows[$key]['mp4_filesize_' . $value2]);
        }

        $file_ = Encoder::getTmpFileName($rows[$key]['id'], 'mp4', $value2);
        if (file_exists($file_)) {
            $rows[$key]['mp4_filesize_' . $value2] = filesize($file_);
            $rows[$key]['mp4_filesize_human_' . $value2] = humanFileSize($rows[$key]['mp4_filesize_' . $value2]);
        }

        $file_ = Encoder::getTmpFileName($rows[$key]['id'], 'webm', $value2);
        if (file_exists($file_)) {
            $rows[$key]['webm_filesize_' . $value2] = filesize($file_);
            $rows[$key]['webm_filesize_human_' . $value2] = humanFileSize($rows[$key]['webm_filesize_' . $value2]);
        }
    }

    if (!empty($file) && is_dir($file)) {
        $rows[$key]['hls_filesize'] = directorysize($file);
        $rows[$key]['hls_filesize_human'] = humanFileSize($rows[$key]['hls_filesize']);
    }

    $rows[$key]['encoding_status'] = Encoder::getVideoConversionStatus($rows[$key]['id']);
}
$rows = array_values($rows);
$total = Encoder::getTotal(true);

if (empty($_POST['rowCount']) && !empty($total)) {
    $_POST['rowCount'] = $total;
}
// start queue now
execRun();
echo '{  "current": ' . $_POST['current'] . ',"rowCount": ' . $_POST['rowCount'] . ', "total": ' . ($total) . ', "rows":' . json_encode($rows) . '}';
