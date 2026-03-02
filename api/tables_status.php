<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

try {
    $tables = $pdo->query("SELECT id, table_number, status, session_token FROM tables ORDER BY CAST(table_number AS UNSIGNED)")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'tables' => $tables]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
