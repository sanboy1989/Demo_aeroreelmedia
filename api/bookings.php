<?php
require_once __DIR__ . '/db.php';
session_start();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

/* Public: submit a booking / proposal request */
if ($method === 'POST') {
  $d = body();
  if (empty($d['name']) || empty($d['email'])) json_out(['error' => 'name and email required'], 422);

  $st = $pdo->prepare("INSERT INTO bookings
      (name, email, phone, event_type, date, location, services, notes, newsletter)
      VALUES (?,?,?,?,?,?,?,?,?)");
  $st->execute([
    $d['name'], $d['email'], $d['phone'] ?? '', $d['eventType'] ?? '',
    $d['date'] ?? '', $d['location'] ?? '', json_encode($d['services'] ?? []),
    $d['notes'] ?? '', !empty($d['newsletter']) ? 1 : 0,
  ]);

  if (!empty($d['newsletter'])) add_subscriber($pdo, $d['name'], $d['email'], 'Booking Form');
  @mail(NOTIFY_EMAIL, 'New proposal request', json_encode($d, JSON_PRETTY_PRINT));
  json_out(['ok' => true]);
}

/* Everything below requires an admin session */
require_auth();

if ($method === 'GET') {
  $rows = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC")->fetchAll();
  foreach ($rows as &$r) $r['services'] = json_decode($r['services'] ?: '[]', true);
  json_out($rows);
}

if ($method === 'PUT') {
  $d = body();
  if (empty($d['id'])) json_out(['error' => 'id required'], 422);
  $st = $pdo->prepare("UPDATE bookings SET name=?, email=?, phone=?, event_type=?, location=?, status=?, notes=? WHERE id=?");
  $st->execute([
    $d['name'] ?? '', $d['email'] ?? '', $d['phone'] ?? '', $d['event_type'] ?? '',
    $d['location'] ?? '', $d['status'] ?? 'New', $d['notes'] ?? '', $d['id'],
  ]);
  json_out(['ok' => true]);
}

if ($method === 'DELETE') {
  $ids = [];
  if (!empty($_GET['id'])) $ids[] = $_GET['id'];
  $d = body();
  if (!empty($d['ids']) && is_array($d['ids'])) $ids = array_merge($ids, $d['ids']);
  if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $pdo->prepare("DELETE FROM bookings WHERE id IN ($in)")->execute($ids);
  }
  json_out(['ok' => true, 'deleted' => count($ids)]);
}

json_out(['error' => 'bad request'], 400);
