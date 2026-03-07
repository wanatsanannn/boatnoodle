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
<<<<<<< HEAD
let knownOrderIds = new Set();
let isFirstLoad = true;

function playNotificationSound() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = "sine";
        osc1.frequency.setValueAtTime(880, ctx.currentTime); 
        osc1.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
        gain1.gain.setValueAtTime(1, ctx.currentTime);
        gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(ctx.currentTime);
        osc1.stop(ctx.currentTime + 0.3);
        
        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = "sine";
        osc2.frequency.setValueAtTime(1320, ctx.currentTime + 0.1);
        osc2.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.4); 
        gain2.gain.setValueAtTime(0, ctx.currentTime);
        gain2.gain.setValueAtTime(0.8, ctx.currentTime + 0.1);
        gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(ctx.currentTime + 0.1);
        osc2.stop(ctx.currentTime + 0.4);
    } catch(e) {
        console.error(e);
    }
}
=======
let lastCount = 0;
const sound = new Audio("data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Hkot9bml0fYeRi3pqaHV+h5KKe2xqdn+IkYl6bGl2f4iSiXpsaXZ/iJKJe2xpdn6Hk4l7bGl2f4iTiXtranV+iJOJe2xqdn6Ik4l7bGp1foiTintranV+iJOJe2xqdX6Ik4p7bGl2foiTintranV+iJOKe2xqdX4=");
>>>>>>> 3de3ad1269bda8bc2ef5bfd0d54b33fd401fb403

function fetchReady() {
    fetch("../api/orders.php?status=ready")
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderReady(data.orders);
<<<<<<< HEAD
                let currentIds = data.orders.map(o => o.id);
                let hasNewOrder = false;
                
                if (!isFirstLoad) {
                    for (let id of currentIds) {
                        if (!knownOrderIds.has(id)) {
                            hasNewOrder = true;
                            break;
                        }
                    }
                    if (hasNewOrder) {
                        playNotificationSound();
                    }
                }
                
                knownOrderIds = new Set(currentIds);
                isFirstLoad = false;
=======
                if (data.orders.length > lastCount && lastCount > 0) {
                    sound.play().catch(() => {});
                }
                lastCount = data.orders.length;
>>>>>>> 3de3ad1269bda8bc2ef5bfd0d54b33fd401fb403
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
