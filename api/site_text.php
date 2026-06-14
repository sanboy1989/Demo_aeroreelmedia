<?php
require_once __DIR__ . '/db.php';
session_start();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

/* Public: read the front-end text overrides as { key: content } */
if ($method === 'GET') {
  $rows = $pdo->query("SELECT text_key, content FROM site_text")->fetchAll();
  $out = [];
  foreach ($rows as $r) $out[$r['text_key']] = $r['content'];
  json_out($out);
}

/* Everything below requires an admin session */
require_auth();

if ($method === 'POST' || $method === 'PUT') {
  $d = body();
  $key = trim($d['key'] ?? '');
  if ($key === '') json_out(['error' => 'key required'], 422);
  $content = $d['content'] ?? '';

  // Empty content = reset this slot back to the page default.
  if (trim($content) === '') {
    $pdo->prepare("DELETE FROM site_text WHERE text_key = ?")->execute([$key]);
    json_out(['ok' => true, 'reset' => true]);
  }

  $pdo->prepare("INSERT INTO site_text (text_key, content) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE content = VALUES(content)")->execute([$key, $content]);
  json_out(['ok' => true]);
}

json_out(['error' => 'bad request'], 400);
