<?php
require_once __DIR__ . '/db.php';
session_start();
require_auth();

if (empty($_FILES['file'])) json_out(['error' => 'no file'], 400);
$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) json_out(['error' => 'upload failed (file too large?)'], 400);

$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$ext = preg_replace('/[^a-z0-9]/', '', $ext);

$videoExt = ['mp4', 'webm', 'ogg', 'ogv', 'm4v', 'mov'];
$isVideo  = in_array($ext, $videoExt, true);

if ($isVideo) {
  // Trust the whitelisted extension, but block anything that detects as a
  // script / markup / text type (defence in depth). Real video files report
  // video/* or, on some hosts, application/octet-stream — both are allowed.
  $mime = function_exists('mime_content_type') ? @mime_content_type($f['tmp_name']) : '';
  if ($mime && preg_match('#^(text/|image/svg|application/(x-php|x-httpd-php|xhtml))#i', $mime)) {
    json_out(['error' => 'not a valid video file'], 422);
  }
} else {
  if (@getimagesize($f['tmp_name']) === false) json_out(['error' => 'not an image'], 422);
  if ($ext === '') $ext = 'jpg';
}

$dir = __DIR__ . '/../uploads';
if (!is_dir($dir)) mkdir($dir, 0775, true);

$name = ($isVideo ? 'vid_' : 'img_') . bin2hex(random_bytes(6)) . '.' . $ext;
if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) json_out(['error' => 'save failed'], 500);
json_out(['ok' => true, 'url' => '/uploads/' . $name, 'type' => $isVideo ? 'video' : 'image']);
