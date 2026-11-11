<?php
$sql_db_host = "127.0.0.1";
$sql_db_user = "abotalal";
$sql_db_pass = "abotalal123";
$sql_db_name = "abotalal";

$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:8000';
$https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off');
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
    $https = true;
}
if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
    $https = true;
}
if (!empty($_SERVER['REQUEST_SCHEME']) && strtolower((string) $_SERVER['REQUEST_SCHEME']) === 'https') {
    $https = true;
}
if (!empty($host) && strpos($host, 'monkeycode-ai.live') !== false) {
    $https = true;
}
$scheme = $https ? 'https' : 'http';
$base_path = '';
$doc_root = !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
$app_root = rtrim(str_replace('\\', '/', __DIR__), '/');
if ($doc_root !== '' && strpos($app_root, $doc_root) === 0) {
    $base_path = substr($app_root, strlen($doc_root));
}
if ($base_path === '/' || $base_path === '\\' || $base_path === '.') {
    $base_path = '';
}
$site_url = $scheme . '://' . $host . $base_path;

$purchase_code = "";
?>
