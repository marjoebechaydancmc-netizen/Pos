<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "pos_db");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed: " . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['order_number'], $data['total'], $data['cash'], $data['change'], $data['items'])) {
    echo json_encode(["success" => false, "error" => "Invalid data"]);
    exit;
}

$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, total, cash_tendered, change_amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddd", $data['order_number'], $data['total'], $data['cash'], $data['change']);
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, emoji, price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($data['items'] as $item) {
        $stmt->bind_param("issdid", $orderId, $item['name'], $item['emoji'], $item['price'], $item['qty'], $item['lineTotal']);
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();

    // --- Save a permanent backup to db.json ---
    $jsonFile = __DIR__ . '/db.json';
    $jsonArray = [];
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $decoded = json_decode($jsonContent, true);
        if (is_array($decoded)) {
            $jsonArray = $decoded;
        }
    }
    
    $orderRecord = [
        'order_id' => $orderId,
        'order_number' => $data['order_number'],
        'total' => $data['total'],
        'cash_tendered' => $data['cash'],
        'change_amount' => $data['change'],
        'order_date' => date('Y-m-d H:i:s'),
        'items' => $data['items']
    ];
    $jsonArray[] = $orderRecord;
    file_put_contents($jsonFile, json_encode($jsonArray, JSON_PRETTY_PRINT));
    // ------------------------------------------

    echo json_encode(["success" => true, "order_id" => $orderId]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

$conn->close();
?>
