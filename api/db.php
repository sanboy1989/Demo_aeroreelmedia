<?php
require_once __DIR__ . '/config.php';

/* PDO connection (with retry while MySQL boots) + schema bootstrap */
function db() {
  static $pdo = null;
  if ($pdo !== null) return $pdo;

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
  $opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];
  for ($i = 0; $i < 15; $i++) {
    try { $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts); break; }
    catch (PDOException $e) { if ($i === 14) throw $e; sleep(1); }
  }
  init_db($pdo);
  return $pdo;
}

function init_db($pdo) {
  $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
    id VARCHAR(64) PRIMARY KEY,
    category VARCHAR(64) NOT NULL,
    type VARCHAR(32) NOT NULL DEFAULT 'Photo',
    title VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    date VARCHAR(64),
    services TEXT,
    cover TEXT,
    media TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) CHARACTER SET utf8mb4");

  $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255), email VARCHAR(255), phone VARCHAR(64),
    event_type VARCHAR(64), date VARCHAR(64), location VARCHAR(255),
    services TEXT, notes TEXT, newsletter TINYINT DEFAULT 0,
    status VARCHAR(32) DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) CHARACTER SET utf8mb4");

  $pdo->exec("CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255), email VARCHAR(255) UNIQUE,
    source VARCHAR(64) DEFAULT 'Website',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) CHARACTER SET utf8mb4");

  $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) CHARACTER SET utf8mb4");

  // Overrides for the fixed front-end images (hero, service cards, logo, our story).
  // Empty table = the page keeps the defaults hard-coded in index.html.
  $pdo->exec("CREATE TABLE IF NOT EXISTS site_images (
    img_key VARCHAR(64) PRIMARY KEY,
    url TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) CHARACTER SET utf8mb4");

  // Overrides for the fixed front-end copy (headings, service cards, about, contact…).
  // Empty table = the page keeps the defaults hard-coded in index.html.
  $pdo->exec("CREATE TABLE IF NOT EXISTS site_text (
    text_key VARCHAR(64) PRIMARY KEY,
    content TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) CHARACTER SET utf8mb4");

  seed($pdo);
}

function seed($pdo) {
  if ($pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn() == 0) {
    $st = $pdo->prepare("INSERT INTO admin_users (email, password_hash) VALUES (?, ?)");
    $st->execute([ADMIN_EMAIL, password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT)]);
  }
  if ($pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn() == 0) {
    $st = $pdo->prepare("INSERT INTO projects
      (id, category, type, title, location, date, services, cover, media, sort_order)
      VALUES (?,?,?,?,?,?,?,?,?,?)");
    foreach (seed_projects() as $i => $p) {
      $st->execute([$p['id'], $p['category'], $p['type'], $p['title'], $p['location'],
        $p['date'], json_encode($p['services']), $p['cover'], json_encode($p['media']), $i]);
    }
  }
}

/* Seed data (mirrors the public site) */
function pics($seed, $n) {
  $a = [];
  for ($i = 0; $i < $n; $i++) $a[] = "https://picsum.photos/seed/{$seed}{$i}/1200/800";
  return $a;
}
function seed_projects() {
  $YT = 'https://www.youtube.com/watch?v=aqz-KE-bpKQ';
  $VM = 'https://vimeo.com/76979871';
  return [
    ['id'=>'cn1','category'=>'Construction','type'=>'Photo + Video','title'=>'Skyline Tower — Site Progress','location'=>'Calgary, AB','date'=>'Ongoing 2024','services'=>['Site Progress & Drone Media','Cinematic Video Production'],'cover'=>'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=70','media'=>array_merge([$YT], pics('cn1',7))],
    ['id'=>'cn2','category'=>'Construction','type'=>'Photo','title'=>'Harbourfront Development','location'=>'Kowloon Bay, Hong Kong','date'=>'2023–2024','services'=>['Site Progress & Drone Media'],'cover'=>'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=70','media'=>pics('cn2',7)],
    ['id'=>'ho1','category'=>'Hospitality','type'=>'Photo + Video','title'=>'The Peak Bistro Launch','location'=>'Central, Hong Kong','date'=>'April 2024','services'=>['Cinematic Video Production','Commercial Photography','Social Media Management'],'cover'=>'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=70','media'=>array_merge([$VM], pics('ho1',8))],
    ['id'=>'ho2','category'=>'Hospitality','type'=>'Photo + Video','title'=>'Lakeview Resort Promo','location'=>'Banff, AB','date'=>'January 2024','services'=>['Cinematic Video Production','Commercial Photography'],'cover'=>'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=70','media'=>array_merge([$YT], pics('ho2',6))],
    ['id'=>'cb1','category'=>'Commercial & Brand','type'=>'Photo + Video','title'=>'Nexus Tech Product Launch','location'=>'Causeway Bay, Hong Kong','date'=>'May 2024','services'=>['Short-Form Video Marketing','Commercial Photography'],'cover'=>'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800&q=70','media'=>array_merge([$YT], pics('cb1',6))],
    ['id'=>'cb2','category'=>'Commercial & Brand','type'=>'Photo','title'=>'Aurora Apparel Campaign','location'=>'Calgary, AB','date'=>'February 2024','services'=>['Commercial Photography','Short-Form Video Marketing','Social Media Management'],'cover'=>'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=70','media'=>pics('cb2',7)],
    ['id'=>'cb3','category'=>'Commercial & Brand','type'=>'Photo','title'=>'Meridian Brand Refresh','location'=>'Hong Kong','date'=>'April 2024','services'=>['Web Design & Google SEO','Commercial Photography'],'cover'=>'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=800&q=70','media'=>pics('cb3',6)],
    ['id'=>'ce1','category'=>'Corporate Events','type'=>'Photo + Video','title'=>'FinSummit Annual Gala 2024','location'=>'Wan Chai, Hong Kong','date'=>'February 2024','services'=>['Cinematic Video Production','Commercial Photography'],'cover'=>'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&q=70','media'=>array_merge([$YT], pics('ce1',6))],
    ['id'=>'ce2','category'=>'Corporate Events','type'=>'Video','title'=>'TechCon Keynote','location'=>'Calgary, AB','date'=>'November 2023','services'=>['Cinematic Video Production','Social Media Management'],'cover'=>'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=800&q=70','media'=>[$VM, $YT]],
    ['id'=>'ce3','category'=>'Corporate Events','type'=>'Photo + Video','title'=>'City Music Festival','location'=>'West Kowloon, Hong Kong','date'=>'November 2023','services'=>['Cinematic Video Production','Short-Form Video Marketing'],'cover'=>'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800&q=70','media'=>array_merge([$YT], pics('ce3',6))],
  ];
}

/* Shared helpers */
function json_out($data, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}
function body() {
  return json_decode(file_get_contents('php://input'), true) ?: [];
}
function require_auth() {
  if (empty($_SESSION['admin'])) json_out(['error' => 'unauthorized'], 401);
}
function add_subscriber($pdo, $name, $email, $source) {
  $st = $pdo->prepare("INSERT IGNORE INTO subscribers (name, email, source) VALUES (?, ?, ?)");
  $st->execute([$name, $email, $source]);
}
