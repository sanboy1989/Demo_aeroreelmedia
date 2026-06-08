<?php
require_once __DIR__ . '/db.php';
session_start();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

/* Public: newsletter / brochure signup */
if ($method === 'POST') {
  $d = body();
  if (empty($d['email'])) json_out(['error' => 'email required'], 422);
  add_subscriber($pdo, $d['name'] ?? '', $d['email'], 'Website Signup');
  json_out(['ok' => true]);
}

/* Everything below requires an admin session */
require_auth();

if ($method === 'GET') {
  json_out($pdo->query("SELECT * FROM subscribers ORDER BY created_at DESC")->fetchAll());
}

if ($method === 'PUT') {
  $d = body();
  if (empty($d['id'])) json_out(['error' => 'id required'], 422);
  try {
    $st = $pdo->prepare("UPDATE subscribers SET name=?, email=?, source=? WHERE id=?");
    $st->execute([$d['name'] ?? '', $d['email'] ?? '', $d['source'] ?? 'Website', $d['id']]);
  } catch (PDOException $e) {
    json_out(['error' => 'That email is already on the list'], 409);
  }
  json_out(['ok' => true]);
}

if ($method === 'DELETE') {
  $ids = [];
  if (!empty($_GET['id'])) $ids[] = $_GET['id'];
  $d = body();
  if (!empty($d['ids']) && is_array($d['ids'])) $ids = array_merge($ids, $d['ids']);
  if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $pdo->prepare("DELETE FROM subscribers WHERE id IN ($in)")->execute($ids);
  }
  json_out(['ok' => true, 'deleted' => count($ids)]);
}

json_out(['error' => 'bad request'], 400);
