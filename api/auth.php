<?php
require_once __DIR__ . '/db.php';
session_start();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'me') {
  json_out(['loggedIn' => !empty($_SESSION['admin']), 'email' => $_SESSION['admin']['email'] ?? null]);
}

if ($method === 'POST' && $action === 'login') {
  $d = body();
  $st = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
  $st->execute([$d['email'] ?? '']);
  $u = $st->fetch();
  if ($u && password_verify($d['password'] ?? '', $u['password_hash'])) {
    $_SESSION['admin'] = ['email' => $u['email']];
    json_out(['ok' => true, 'email' => $u['email']]);
  }
  json_out(['error' => 'Invalid email or password'], 401);
}

if ($method === 'POST' && $action === 'logout') {
  session_destroy();
  json_out(['ok' => true]);
}

json_out(['error' => 'bad request'], 400);
