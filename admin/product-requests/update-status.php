<?php
// This endpoint is now handled inline in view.php
// Redirect for backward compatibility
declare(strict_types=1);
$id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT) ?: 0;
header('Location: view.php?id=' . $id);
exit;
