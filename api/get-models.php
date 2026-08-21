<?php
/**
 * API Endpoint: Get Vehicle Models by Make ID
 * Returns JSON for dynamic dropdowns.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$makeId = filter_input(INPUT_GET, 'make_id', FILTER_VALIDATE_INT);

if (!$makeId) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT model_id, model_name 
         FROM vehicle_model 
         WHERE make_id = ? 
         ORDER BY model_name ASC'
    );
    $stmt->execute([$makeId]);
    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($models);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
