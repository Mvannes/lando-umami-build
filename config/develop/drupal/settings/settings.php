<?php

$databases['default']['default'] = [
  'database' => getenv('DB_NAME'),
  'username' => getenv('DB_USER'),
  'password' => getenv('DB_PASSWORD'),
  'prefix' => '',
  'host' => getenv('DB_HOST'),
  'port' => getenv('DB_PORT'),
  'driver' => 'mysql',
];
