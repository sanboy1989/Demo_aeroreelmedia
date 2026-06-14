<?php
require_once __DIR__ . '/db.php';
session_start();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

/* Public: read the front-end image overrides as { key: url } */
if ($method === 'GET') {
  $rows = $pdo->query("SELECT img_key, url FROM site_images")->fetchAll();
  $out = [];
  foreach ($rows as $r) $out[$r['img_key']] = $r['url'];
  json_out($out);
}

/* Everything below requires an admin session */
require_auth();

if ($method === 'POST' || $method === 'PUT') {
  $d = body();
  $key = trim($d['key'] ?? '');
  if ($key === '') json_out(['error' => 'key required'], 422);
  $url = trim($d['url'] ?? '');

  // Empty url = reset this slot back to the page default.
  if ($url === '') {
    $pdo->prepare("DELETE FROM site_images WHERE img_key = ?")->execute([$key]);
    json_out(['ok' => true, 'reset' => true]);
  }

  $pdo->prepare("INSERT INTO site_images (img_key, url) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE url = VALUES(url)")->execute([$key, $url]);
  json_out(['ok' => true]);
}

json_out(['error' => 'bad request'], 400);
