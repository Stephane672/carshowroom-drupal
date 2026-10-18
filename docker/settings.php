<?php

/**
 * Production settings for the Render deployment.
 *
 * Database and secrets come from environment variables set in the Render
 * dashboard, never hardcoded here. Supports either generic DB_* names or
 * Clever Cloud's MYSQL_ADDON_* naming so either host's connection details
 * can be pasted in as-is.
 */

function cs_env($keys, $default = NULL) {
  foreach ((array) $keys as $key) {
    $value = getenv($key);
    if ($value !== FALSE && $value !== '') {
      return $value;
    }
  }
  return $default;
}

$databases['default']['default'] = [
  'database' => cs_env(['DB_NAME', 'MYSQL_ADDON_DB']),
  'username' => cs_env(['DB_USER', 'MYSQL_ADDON_USER']),
  'password' => cs_env(['DB_PASSWORD', 'MYSQL_ADDON_PASSWORD']),
  'host' => cs_env(['DB_HOST', 'MYSQL_ADDON_HOST']),
  'port' => cs_env(['DB_PORT', 'MYSQL_ADDON_PORT'], '3306'),
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];

$settings['hash_salt'] = cs_env('DRUPAL_HASH_SALT', '');

$settings['config_sync_directory'] = '../config/sync';

// Accept the Render-assigned hostname plus any custom domain supplied via env.
$trusted_patterns = ['^.+\.onrender\.com$'];
$extra_host = cs_env('TRUSTED_HOST_EXTRA');
if ($extra_host) {
  $trusted_patterns[] = '^' . preg_quote($extra_host, '/') . '$';
}
$settings['trusted_host_patterns'] = $trusted_patterns;

// Render terminates TLS in front of the container; trust its proxy headers
// so Drupal generates https:// URLs and knows requests arrived over HTTPS.
$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR'] ?? ''];

$settings['file_public_path'] = 'sites/default/files';

$config['system.logging']['error_level'] = 'hide';
