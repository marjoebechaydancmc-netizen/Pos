<?php
$conn = new mysqli("localhost", "root", "", "pos_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ─── Handle Delete Actions ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_order_id'])) {
        $delId = intval($_POST['delete_order_id']);
        $conn->query("DELETE FROM orders WHERE id = $delId");
        header("Location: history.php");
        exit;
    }
    if (isset($_POST['clear_all'])) {
        $conn->query("DELETE FROM order_items");
        $conn->query("DELETE FROM orders");
        header("Location: history.php");
        exit;
    }
}

// Fetch all orders with their items
$orders = [];
$result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
while ($row = $result->fetch_assoc()) {
    $orderId = $row['id'];
    $items = [];
    $itemResult = $conn->query("SELECT * FROM order_items WHERE order_id = $orderId");
    while ($item = $itemResult->fetch_assoc()) {
        $items[] = $item;
    }
    $row['items'] = $items;
    $orders[] = $row;
}

// Calculate total income
$totalIncome = 0;
foreach ($orders as $order) {
    $totalIncome += $order['total'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaction History — QuickPOS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --bg:#0f1117;--surface:rgba(255,255,255,.06);--surface2:rgba(255,255,255,.1);
  --border:rgba(255,255,255,.08);--text:#e4e4e7;--text-dim:#9ca3af;
  --accent:#6366f1;--accent-glow:rgba(99,102,241,.35);
  --green:#22c55e;--green-glow:rgba(34,197,94,.3);
  --red:#ef4444;--red-glow:rgba(239,68,68,.3);--orange:#f59e0b;--cyan:#06b6d4;
  --radius:14px;
}
html{font-size:14px;}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse at 20% 0%,rgba(99,102,241,.12) 0%,transparent 60%),radial-gradient(ellipse at 80% 100%,rgba(6,182,212,.08) 0%,transparent 50%);pointer-events:none;z-index:0;}

/* ─── Topbar ─────────────────────────────────── */
.topbar{position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:14px 28px;border-bottom:1px solid var(--border);backdrop-filter:blur(20px);background:rgba(15,17,23,.85);}
.topbar h1{font-size:1.3rem;font-weight:800;display:flex;align-items:center;gap:10px;}
.topbar h1 span.icon{font-size:1.5rem;}
.topbar-actions{display:flex;align-items:center;gap:10px;}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border:none;border-radius:10px;font-family:inherit;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .25s;text-decoration:none;}
.btn-home{background:var(--surface);border:1px solid var(--border);color:var(--text);}
.btn-home:hover{border-color:var(--accent);color:#fff;box-shadow:0 0 18px var(--accent-glow);}
.btn-print{background:linear-gradient(135deg,var(--accent),#8b5cf6);color:#fff;}
.btn-print:hover{box-shadow:0 0 24px var(--accent-glow);transform:translateY(-1px);}
.btn-danger{background:linear-gradient(135deg,var(--red),#dc2626);color:#fff;}
.btn-danger:hover{box-shadow:0 0 24px var(--red-glow);transform:translateY(-1px);}

/* ─── Stats Cards ────────────────────────────── */
.stats-row{position:relative;z-index:1;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;padding:24px 28px;}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px 20px;display:flex;align-items:center;gap:16px;transition:all .25s;}
.stat-card:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.25);}
.stat-icon{font-size:2.2rem;width:56px;height:56px;display:flex;align-items:center;justify-content:center;border-radius:14px;flex-shrink:0;}
.stat-icon.income{background:var(--green-glow);}
.stat-icon.orders{background:rgba(99,102,241,.2);}
.stat-icon.avg{background:rgba(6,182,212,.2);}
.stat-info .stat-label{font-size:.78rem;color:var(--text-dim);font-weight:500;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;}
.stat-info .stat-value{font-size:1.5rem;font-weight:800;}
.stat-info .stat-value.green{color:var(--green);}
.stat-info .stat-value.accent{color:var(--accent);}
.stat-info .stat-value.cyan{color:var(--cyan);}

/* ─── Table ──────────────────────────────────── */
.table-container{position:relative;z-index:1;padding:0 28px 28px;}
.table-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.table-header h2{font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:8px;}
.table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
table{width:100%;border-collapse:collapse;font-size:.88rem;}
thead{background:rgba(255,255,255,.04);}
th{text-align:left;padding:14px 18px;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);border-bottom:1px solid var(--border);}
td{padding:14px 18px;border-bottom:1px solid var(--border);vertical-align:top;}
tr:last-child td{border-bottom:none;}
tr{transition:background .2s;}
tr:hover{background:rgba(255,255,255,.03);}
.order-num{color:var(--accent);font-weight:700;}
.order-date{color:var(--text-dim);font-size:.8rem;}
.order-items{display:flex;flex-wrap:wrap;gap:4px;}
.item-chip{background:rgba(255,255,255,.06);padding:3px 8px;border-radius:6px;font-size:.78rem;white-space:nowrap;}
.order-total{color:var(--green);font-weight:800;font-size:1rem;}
.order-cash{font-weight:600;}
.order-change{color:var(--cyan);font-weight:600;}
.no-data{text-align:center;padding:60px 20px;color:var(--text-dim);}
.no-data .nd-icon{font-size:3rem;margin-bottom:12px;opacity:.4;}
.no-data p{font-size:.95rem;}

/* ─── Action Buttons in Table ────────────────── */
.td-actions{display:flex;gap:6px;align-items:center;}
.btn-sm{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border:none;border-radius:8px;font-family:inherit;font-size:.76rem;font-weight:600;cursor:pointer;transition:all .25s;}
.btn-view{background:rgba(99,102,241,.15);color:var(--accent);border:1px solid rgba(99,102,241,.25);}
.btn-view:hover{background:rgba(99,102,241,.3);box-shadow:0 0 14px var(--accent-glow);}
.btn-del{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.2);}
.btn-del:hover{background:rgba(239,68,68,.25);box-shadow:0 0 14px var(--red-glow);}

/* ─── Modal ──────────────────────────────────── */
.modal-overlay{display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,.65);backdrop-filter:blur(6px);align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal{background:#1a1d27;border:1px solid var(--border);border-radius:18px;width:90%;max-width:550px;max-height:85vh;overflow-y:auto;box-shadow:0 25px 80px rgba(0,0,0,.6);animation:modalIn .25s ease;}
@keyframes modalIn{from{opacity:0;transform:translateY(20px) scale(.97)}to{opacity:1;transform:none}}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border);}
.modal-head h3{font-size:1.05rem;font-weight:700;display:flex;align-items:center;gap:8px;}
.modal-close{background:var(--surface);border:1px solid var(--border);color:var(--text-dim);width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;transition:all .2s;}
.modal-close:hover{border-color:var(--red);color:var(--red);}
.modal-body{padding:20px 24px 24px;}
.modal-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:.88rem;}
.modal-row:last-child{border-bottom:none;}
.modal-row .label{color:var(--text-dim);font-weight:500;}
.modal-row .value{font-weight:700;text-align:right;}
.modal-row .value.green{color:var(--green);}
.modal-row .value.cyan{color:var(--cyan);}
.modal-items-title{margin-top:14px;margin-bottom:10px;font-size:.82rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;}
.modal-item{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--surface);border-radius:10px;margin-bottom:6px;font-size:.85rem;}
.modal-item .mi-name{display:flex;align-items:center;gap:8px;}
.modal-item .mi-price{color:var(--green);font-weight:700;}

/* ─── Confirm Dialog ─────────────────────────── */
.confirm-overlay{display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);align-items:center;justify-content:center;}
.confirm-overlay.active{display:flex;}
.confirm-box{background:#1a1d27;border:1px solid var(--border);border-radius:18px;padding:32px;text-align:center;max-width:400px;width:90%;box-shadow:0 25px 80px rgba(0,0,0,.6);animation:modalIn .25s ease;}
.confirm-box .cb-icon{font-size:2.5rem;margin-bottom:12px;}
.confirm-box h3{font-size:1.1rem;font-weight:700;margin-bottom:8px;}
.confirm-box p{color:var(--text-dim);font-size:.88rem;margin-bottom:24px;line-height:1.5;}
.confirm-btns{display:flex;gap:10px;justify-content:center;}
.confirm-btns .btn{min-width:110px;justify-content:center;}
.btn-cancel{background:var(--surface);border:1px solid var(--border);color:var(--text);}
.btn-cancel:hover{border-color:var(--accent);color:#fff;}

/* ─── Expand/Collapse Items ──────────────────── */
.items-toggle{background:transparent;border:1px solid var(--border);color:var(--text-dim);padding:3px 10px;border-radius:6px;font-size:.72rem;font-weight:600;cursor:pointer;margin-top:6px;transition:all .2s;}
.items-toggle:hover{border-color:var(--accent);color:var(--accent);}

/* ─── Responsive ─────────────────────────────── */
@media(max-width:768px){
  .topbar{flex-direction:column;gap:12px;padding:14px 16px;}
  .stats-row{padding:16px;grid-template-columns:1fr;}
  .table-container{padding:0 16px 16px;}
  th,td{padding:10px 12px;font-size:.82rem;}
  .order-items{flex-direction:column;}
  .td-actions{flex-direction:column;gap:4px;}
}

/* ─── Print ──────────────────────────────────── */
@media print{
  body{background:#fff!important;color:#000!important;}
  body::before{display:none;}
  .topbar{position:relative;background:#fff!important;backdrop-filter:none;border-bottom:2px solid #000;print-color-adjust:exact;-webkit-print-color-adjust:exact;}
  .topbar h1{color:#000;}
  .topbar-actions{display:none!important;}
  .stat-card{background:#f5f5f5!important;border:1px solid #ccc!important;print-color-adjust:exact;-webkit-print-color-adjust:exact;}
  .stat-info .stat-value,.stat-info .stat-value.green,.stat-info .stat-value.accent,.stat-info .stat-value.cyan{color:#000!important;}
  .stat-info .stat-label{color:#555!important;}
  .table-wrap{border:1px solid #ccc!important;background:#fff!important;}
  table{color:#000;}
  th{background:#eee!important;color:#000!important;border-bottom:2px solid #999!important;}
  td{border-bottom:1px solid #ddd!important;color:#000!important;}
  .order-num{color:#333!important;}
  .order-total{color:#000!important;}
  .order-change{color:#333!important;}
  .item-chip{background:#eee!important;color:#000!important;}
  tr:hover{background:transparent!important;}
  .td-actions{display:none!important;}
  .modal-overlay,.confirm-overlay{display:none!important;}
}

/* ─── Scrollbar ──────────────────────────────── */
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--surface2);border-radius:10px;}
</style>
</head>
<body>

<!-- ─── Topbar ───────────────────────────────────── -->
<header class="topbar">
  <h1><span class="icon">📋</span> Transaction History</h1>
  <div class="topbar-actions">
    <button class="btn btn-danger" onclick="confirmClearAll()" <?= count($orders) === 0 ? 'disabled style="opacity:.4;pointer-events:none"' : '' ?>>🗑️ Clear All History</button>
    <button class="btn btn-print" onclick="window.print()">🖨️ Print Report</button>
    <a href="front.php" class="btn btn-home">🏠 Back to POS</a>
  </div>
</header>

<!-- ─── Stats ────────────────────────────────────── -->
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon income">💰</div>
    <div class="stat-info">
      <div class="stat-label">Total Income</div>
      <div class="stat-value green">₱<?= number_format($totalIncome, 2) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orders">📦</div>
    <div class="stat-info">
      <div class="stat-label">Total Orders</div>
      <div class="stat-value accent"><?= count($orders) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon avg">📊</div>
    <div class="stat-info">
      <div class="stat-label">Average Order</div>
      <div class="stat-value cyan">₱<?= count($orders) > 0 ? number_format($totalIncome / count($orders), 2) : '0.00' ?></div>
    </div>
  </div>
</div>

<!-- ─── Transactions Table ───────────────────────── -->
<div class="table-container">
  <div class="table-header">
    <h2>📋 All Transactions</h2>
  </div>
  <div class="table-wrap">
    <?php if (count($orders) === 0): ?>
      <div class="no-data">
        <div class="nd-icon">📭</div>
        <p>No transactions yet. Start selling!</p>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Date & Time</th>
          <th>Items</th>
          <th>Total</th>
          <th>Cash</th>
          <th>Change</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
        <tr id="row-<?= $order['id'] ?>">
          <td class="order-num"><?= htmlspecialchars($order['order_number']) ?></td>
          <td class="order-date"><?= date('M d, Y — h:i A', strtotime($order['order_date'])) ?></td>
          <td>
            <div class="order-items">
              <?php foreach ($order['items'] as $item): ?>
                <span class="item-chip"><?= $item['emoji'] ?> <?= htmlspecialchars($item['product_name']) ?> ×<?= $item['quantity'] ?></span>
              <?php endforeach; ?>
            </div>
          </td>
          <td class="order-total">₱<?= number_format($order['total'], 2) ?></td>
          <td class="order-cash">₱<?= number_format($order['cash_tendered'], 2) ?></td>
          <td class="order-change">₱<?= number_format($order['change_amount'], 2) ?></td>
          <td>
            <div class="td-actions">
              <button class="btn-sm btn-view" onclick='viewOrder(<?= json_encode($order, JSON_HEX_APOS | JSON_HEX_TAG) ?>)'>👁️ View</button>
              <button class="btn-sm btn-del" onclick="confirmDelete(<?= $order['id'] ?>, '<?= htmlspecialchars($order['order_number'], ENT_QUOTES) ?>')">🗑️ Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- ─── View Order Modal ─────────────────────────── -->
<div class="modal-overlay" id="viewModal">
  <div class="modal">
    <div class="modal-head">
      <h3>🧾 <span id="modal-order-num"></span></h3>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="modal-row"><span class="label">Date & Time</span><span class="value" id="modal-date"></span></div>
      <div class="modal-row"><span class="label">Total</span><span class="value green" id="modal-total"></span></div>
      <div class="modal-row"><span class="label">Cash Tendered</span><span class="value" id="modal-cash"></span></div>
      <div class="modal-row"><span class="label">Change</span><span class="value cyan" id="modal-change"></span></div>
      <div class="modal-items-title">🛒 Items Ordered</div>
      <div id="modal-items-list"></div>
    </div>
  </div>
</div>

<!-- ─── Confirm Delete Dialog ────────────────────── -->
<div class="confirm-overlay" id="confirmDialog">
  <div class="confirm-box">
    <div class="cb-icon">⚠️</div>
    <h3 id="confirm-title">Delete Order?</h3>
    <p id="confirm-msg">Are you sure you want to delete this order? This cannot be undone.</p>
    <div class="confirm-btns">
      <button class="btn btn-cancel" onclick="closeConfirm()">Cancel</button>
      <form id="confirmForm" method="POST" style="display:inline">
        <input type="hidden" name="delete_order_id" id="confirmDeleteId">
        <button type="submit" class="btn btn-danger">🗑️ Delete</button>
      </form>
    </div>
  </div>
</div>

<!-- ─── Confirm Clear All Dialog ─────────────────── -->
<div class="confirm-overlay" id="clearAllDialog">
  <div class="confirm-box">
    <div class="cb-icon">🚨</div>
    <h3>Clear All History?</h3>
    <p>This will permanently delete <strong>ALL <?= count($orders) ?> transactions</strong>. This action cannot be undone!</p>
    <div class="confirm-btns">
      <button class="btn btn-cancel" onclick="closeClearAll()">Cancel</button>
      <form method="POST" style="display:inline">
        <input type="hidden" name="clear_all" value="1">
        <button type="submit" class="btn btn-danger">🗑️ Yes, Clear All</button>
      </form>
    </div>
  </div>
</div>

<script>
// ─── View Order Modal ──────────────────────────
function viewOrder(order) {
  document.getElementById('modal-order-num').textContent = 'Order ' + order.order_number;
  const d = new Date(order.order_date);
  document.getElementById('modal-date').textContent = d.toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'}) + ' — ' + d.toLocaleTimeString('en-US', {hour:'numeric',minute:'2-digit',hour12:true});
  document.getElementById('modal-total').textContent = '₱' + parseFloat(order.total).toLocaleString('en-US',{minimumFractionDigits:2});
  document.getElementById('modal-cash').textContent = '₱' + parseFloat(order.cash_tendered).toLocaleString('en-US',{minimumFractionDigits:2});
  document.getElementById('modal-change').textContent = '₱' + parseFloat(order.change_amount).toLocaleString('en-US',{minimumFractionDigits:2});

  const list = document.getElementById('modal-items-list');
  list.innerHTML = '';
  order.items.forEach(item => {
    const div = document.createElement('div');
    div.className = 'modal-item';
    div.innerHTML = `<span class="mi-name">${item.emoji} ${item.product_name} × ${item.quantity}</span><span class="mi-price">₱${parseFloat(item.line_total).toLocaleString('en-US',{minimumFractionDigits:2})}</span>`;
    list.appendChild(div);
  });

  document.getElementById('viewModal').classList.add('active');
}

function closeModal() {
  document.getElementById('viewModal').classList.remove('active');
}

// ─── Confirm Delete Single ─────────────────────
function confirmDelete(id, orderNum) {
  document.getElementById('confirm-title').textContent = 'Delete Order ' + orderNum + '?';
  document.getElementById('confirm-msg').textContent = 'Are you sure you want to delete order ' + orderNum + '? This cannot be undone.';
  document.getElementById('confirmDeleteId').value = id;
  document.getElementById('confirmDialog').classList.add('active');
}

function closeConfirm() {
  document.getElementById('confirmDialog').classList.remove('active');
}

// ─── Confirm Clear All ─────────────────────────
function confirmClearAll() {
  document.getElementById('clearAllDialog').classList.add('active');
}

function closeClearAll() {
  document.getElementById('clearAllDialog').classList.remove('active');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay, .confirm-overlay').forEach(el => {
  el.addEventListener('click', e => {
    if (e.target === el) {
      el.classList.remove('active');
    }
  });
});

// Close modals on Escape key
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.active, .confirm-overlay.active').forEach(el => el.classList.remove('active'));
  }
});
</script>

</body>
</html>
