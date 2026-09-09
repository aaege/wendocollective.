<?php
// ── Fill these in before going live. Nothing here works until you do. ──

// Database. cPanel: MySQL Databases. cPanel prefixes both the DB name and
// the DB user with your account username, e.g. "wendo_site", "wendo_admin".
define('DB_HOST', 'localhost');
define('DB_NAME', 'visiblerealtors_09');
define('DB_USER', 'visiblerealtors__BPczZpnWVCRx8MlgZbJBegaSXw5YjFil');
define('DB_PASS', '.@UjefO4K]FyNd5k');

// Pesapal. Get these from your Pesapal merchant account:
// Merchant Account > Settings > API Consumer Keys.
// Use the sandbox keys + PESAPAL_ENV = 'sandbox' until real payments are tested end to end.
define('PESAPAL_CONSUMER_KEY', 'REPLACE_WITH_PESAPAL_CONSUMER_KEY');
define('PESAPAL_CONSUMER_SECRET', 'REPLACE_WITH_PESAPAL_CONSUMER_SECRET');
define('PESAPAL_ENV', 'sandbox'); // 'sandbox' or 'live'

// Full public URL of this site, no trailing slash (e.g. https://wendocollective.co.ke).
// Pesapal needs real, publicly-reachable HTTPS URLs for the callback and IPN. This will
// not work on localhost.
define('SITE_URL', 'https://REPLACE_WITH_YOUR_DOMAIN');

define('PESAPAL_BASE_URL', PESAPAL_ENV === 'live'
    ? 'https://pay.pesapal.com/v3'
    : 'https://cybqa.pesapal.com/pesapalv3');
