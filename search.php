<?php
// search.php is deprecated in favor of shop.php which handles all search logic natively.
// Redirecting to shop.php with preserving query string.

$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/shop.php' . ($query !== '' ? '?' . $query : '');

header("Location: $url", true, 301);
exit;
