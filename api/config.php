<?php
/* Database + admin settings.
   On IONOS: replace these defaults with the credentials from your
   hosting control panel (or leave the getenv() fallbacks for Docker). */
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'aeroreel');
define('DB_USER', getenv('DB_USER') ?: 'aeroreel');
define('DB_PASS', getenv('DB_PASS') ?: 'aeroreelpass');

/* Default admin account, created automatically on first run. */
define('ADMIN_EMAIL',    getenv('ADMIN_EMAIL')    ?: 'Hallo@aeroreelmedia.com');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin123');

/* Where booking/contact notifications are emailed. */
define('NOTIFY_EMAIL', getenv('NOTIFY_EMAIL') ?: 'Hallo@aeroreelmedia.com');
