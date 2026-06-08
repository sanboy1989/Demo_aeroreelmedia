<?php
require_once __DIR__ . '/db.php';
session_start();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

/* Public: list projects */
if ($method === 'GET') {
  $rows = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC, created_at ASC")->fetchAll();
  foreach ($rows as &$r) {
    $r['services'] = json_decode($r['services'] ?: '[]', true);
    $r['media']    = json_decode($r['media'] ?: '[]', true);
  }
  json_out($rows);
}

/* Everything below requires an admin session */
require_auth();

if ($method === 'POST' || $method === 'PUT') {
  $d = body();
  $id       = $d['id'] ?: ('proj_' . time());
  $category = trim($d['category'] ?? '');
  $title    = trim($d['title'] ?? '');
  if ($category === '' || $title === '') json_out(['error' => 'title and category required'], 422);

  $st = $pdo->prepare("INSERT INTO projects
      (id, category, type, title, location, date, services, cover, media, sort_order)
      VALUES (:id,:category,:type,:title,:location,:date,:services,:cover,:media,:sort_order)
    ON DUPLICATE KEY UPDATE
      category=:category, type=:type, title=:title, location=:location,
      date=:date, services=:services, cover=:cover, media=:media");
  $st->execute([
    ':id' => $id,
    ':category' => $category,
    ':type' => $d['type'] ?? 'Photo',
    ':title' => $title,
    ':location' => $d['location'] ?? '',
    ':date' => $d['date'] ?? '',
    ':services' => json_encode($d['services'] ?? []),
    ':cover' => $d['cover'] ?? '',
    ':media' => json_encode($d['media'] ?? []),
    ':sort_order' => $d['sort_order'] ?? 999,
  ]);
  json_out(['ok' => true, 'id' => $id]);
}

if ($method === 'DELETE') {
  $ids = [];
  if (!empty($_GET['id'])) $ids[] = $_GET['id'];
  $d = body();
  if (!empty($d['ids']) && is_array($d['ids'])) $ids = array_merge($ids, $d['ids']);
  if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $pdo->prepare("DELETE FROM projects WHERE id IN ($in)")->execute($ids);
  }
  json_out(['ok' => true, 'deleted' => count($ids)]);
}

json_out(['error' => 'bad request'], 400);
