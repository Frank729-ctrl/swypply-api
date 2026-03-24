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

