<?php
// Supports Supabase DATABASE_URL or individual env vars
define('DATABASE_URL',   getenv('DATABASE_URL')    ?: '');
define('DB_HOST',        getenv('DB_HOST')          ?: '127.0.0.1');
define('DB_PORT',        getenv('DB_PORT')          ?: '5432');
define('DB_NAME',        getenv('DB_NAME')          ?: 'postgres');
define('DB_USER',        getenv('DB_USER')          ?: 'postgres');
define('DB_PASS',        getenv('DB_PASS')          ?: '');
define('JWT_SECRET',     getenv('JWT_SECRET')       ?: 'change-this-in-production');
define('JWT_TTL',        60 * 60 * 24 * 30);         // 30 days
define('PAYSTACK_SECRET',getenv('PAYSTACK_SECRET')  ?: '');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD')   ?: 'swypply-admin');
