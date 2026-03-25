<?php
define('DB_HOST',        getenv('DB_HOST')          ?: 'aws-1-eu-west-1.pooler.supabase.com');
define('DB_PORT',        getenv('DB_PORT')          ?: '5432');
define('DB_NAME',        getenv('DB_NAME')          ?: 'postgres');
define('DB_USER',        getenv('DB_USER')          ?: 'postgres.jbkdfmdzirbbhzvqpxuz');
define('DB_PASS',        getenv('DB_PASS')          ?: 'Shequan123!');
define('JWT_SECRET',     getenv('JWT_SECRET')       ?: 'change-this-in-production');
define('JWT_TTL',        60 * 60 * 24 * 30);
define('PAYSTACK_SECRET',getenv('PAYSTACK_SECRET')  ?: '');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD')   ?: 'swypply-admin');

// ── SMTP (set these in .env) ──────────────────────────────────────────────────
define('SMTP_HOST',       getenv('MAIL_HOST')       ?: 'smtp.hostinger.com');
define('SMTP_PORT',       getenv('MAIL_PORT')       ?: '465');           // 465 = SSL, 587 = STARTTLS
define('SMTP_USER',       getenv('MAIL_USER')       ?: 'support@snafrate.com');
define('SMTP_PASS',       getenv('MAIL_PASS')       ?: '$Nafrate123');
define('SMTP_FROM_EMAIL', getenv('MAIL_FROM')       ?: getenv('MAIL_USER') ?: '');
define('SMTP_FROM_NAME',  getenv('MAIL_FROM_NAME')  ?: 'Swypply');

// ── App ───────────────────────────────────────────────────────────────────────
define('APP_URL',         getenv('APP_URL')         ?: 'https://swypply.com');

