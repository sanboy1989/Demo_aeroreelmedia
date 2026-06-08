<?php
require_once __DIR__ . '/db.php';
session_start();
require_auth();

if (empty($_FILES['file'])) json_out(['error' => 'no file'], 400);
$f = $_FILES['file'];

$info = @getimagesize($f['tmp_name']);
if ($info === false) json_out(['error' => 'not an image'], 422);

$dir = __DIR__ . '/../uploads';
if (!is_dir($dir)) mkdir($dir, 0775, true);

$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
$name = 'img_' . bin2hex(random_bytes(6)) . '.' . $ext;

if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) json_out(['error' => 'save failed'], 500);
json_out(['ok' => true, 'url' => '/uploads/' . $name]);
