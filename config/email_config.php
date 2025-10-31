<?php
// Konfigurasi Email Gmail SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noeemeitin@gmail.com'); // Ganti dengan Gmail Anda
define('SMTP_PASSWORD', 'atcq tdmm bvlk vddx'); // App Password Gmail (16 digit)
define('SMTP_FROM_EMAIL', 'noeemeitin@gmail.com');
define('SMTP_FROM_NAME', 'Sistem Gudang');

// Additional PHPMailer settings for better compatibility
define('SMTP_ENCRYPTION', 'tls'); // Use TLS encryption
define('SMTP_AUTH', true); // Enable SMTP authentication
