<?php
$conn = new mysqli("localhost", "root", "", "pos_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Dummy orders data
$dummyOrders = 
    

$inserted = 0;
foreach ($dummyOrders as $order) {
    $stmt = $conn->prepare("INSERT INTO orders (order_number, total, cash_tendered, change_amount, order_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sddds", $order['order_number'], $order['total'], $order['cash_tendered'], $order['change_amount'], $order['order_date']);
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();

    foreach ($order['items'] as $item) {
        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_name, emoji, price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("issdid", $orderId, $item['product_name'], $item['emoji'], $item['price'], $item['quantity'], $item['line_total']);
        $stmt2->execute();
        $stmt2->close();
    }
    $inserted++;
}

$conn->close();
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Seed Done</title>";
echo "<style>body{font-family:Inter,sans-serif;background:#0f1117;color:#e4e4e7;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}";
echo ".box{text-align:center;background:rgba(255,255,255,.06);padding:40px 50px;border-radius:18px;border:1px solid rgba(255,255,255,.08);}";
echo "h1{font-size:3rem;margin-bottom:10px;}h2{color:#22c55e;margin-bottom:20px;}";
echo "a{color:#6366f1;text-decoration:none;font-weight:700;font-size:1.1rem;}a:hover{text-decoration:underline;}</style></head>";
echo "<body><div class='box'><h1>✅</h1><h2>$inserted dummy orders inserted!</h2><a href='history.php'>→ Go to History</a></div></body></html>";
?>
