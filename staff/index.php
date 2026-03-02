<?php
$pageTitle = 'พนักงานเสิร์ฟ';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager', 'waiter');
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="mb-4"><i class="bi bi-bell"></i> รายการพร้อมเสิร์ฟ</h4>
<p class="text-muted">อัปเดตอัตโนมัติทุก 10 วินาที</p>

<div id="readyOrders" class="row g-3">
    <div class="text-center py-5">
        <div class="spinner-border text-danger"></div>
    </div>
</div>

<?php $extraJS = '<script>
let lastCount = 0;
const sound = new Audio("data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Hkot9bml0fYeRi3pqaHV+h5KKe2xqdn+IkYl6bGl2f4iSiXpsaXZ/iJKJe2xpdn6Hk4l7bGl2f4iTiXtranV+iJOJe2xqdn6Ik4l7bGp1foiTintranV+iJOJe2xqdX6Ik4p7bGl2foiTintranV+iJOKe2xqdX4=");

function fetchReady() {
    fetch("../api/orders.php?status=ready")
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderReady(data.orders);
                if (data.orders.length > lastCount && lastCount > 0) {
                    sound.play().catch(() => {});
                }
                lastCount = data.orders.length;
            }
        });
}

function renderReady(orders) {
    const el = document.getElementById("readyOrders");
    if (orders.length === 0) {
        el.innerHTML = "<div class=\"col-12 text-center text-muted py-5\"><i class=\"bi bi-check-circle\" style=\"font-size:3rem\"></i><p class=\"mt-2\">ไม่มีรายการรอเสิร์ฟ</p></div>";
        return;
    }
    el.innerHTML = orders.map(o => `
        <div class="col-md-4 col-lg-3">
            <div class="card border-success">
                <div class="card-header bg-success text-white d-flex justify-content-between">
                    <strong>โต๊ะ ${o.table_number}</strong>
                    <small>${o.time_ago}</small>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">#${o.order_number}</p>
                    ${o.items.map(i => `
                        <div class="mb-1">
                            <strong>${i.quantity}×</strong> ${i.menu_name}
                            <span class="text-muted small">(${i.size === "special" ? "พิเศษ" : "ธรรมดา"}${i.spice_level > 0 ? " 🌶️".repeat(i.spice_level) : ""})</span>
                        </div>
                    `).join("")}
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary w-100" onclick="markServed(${o.id})">
                        🍜 เสิร์ฟแล้ว
                    </button>
                </div>
            </div>
        </div>
    `).join("");
}

function markServed(orderId) {
    fetch("serve.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({order_id: orderId})
    }).then(r => r.json()).then(data => {
        if (data.success) fetchReady();
        else alert(data.message || "เกิดข้อผิดพลาด");
    });
}

fetchReady();
setInterval(fetchReady, 10000);
</script>'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
