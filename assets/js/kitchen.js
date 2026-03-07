// === JS สำหรับหน้าจอครัว ===

let knownOrderIds = new Set();
let isFirstLoad = true;

document.addEventListener('DOMContentLoaded', function () {
    // เริ่ม polling
    updateClock();
    setInterval(updateClock, 1000);
    fetchOrders();
    setInterval(fetchOrders, 10000); // ทุก 10 วินาที
});

function updateClock() {
    const el = document.getElementById('clock');
    if (el) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('th-TH');
    }
}

function fetchOrders() {
    fetch('../api/orders.php?status=pending,cooking')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderOrders(data.orders);

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
                        playNotification();
                    }
                }

                knownOrderIds = new Set(currentIds);
                isFirstLoad = false;
            }
        })
        .catch(err => console.error('Fetch error:', err));
}

function renderOrders(orders) {
    const grid = document.getElementById('orderGrid');
    if (!grid) return;

    if (orders.length === 0) {
        grid.innerHTML = '<div class="no-orders"><i class="bi bi-cup-hot"></i>ไม่มีออเดอร์ค้าง</div>';
        return;
    }

    grid.innerHTML = orders.map(order => `
        <div class="kitchen-card ${order.status === 'pending' ? 'new-order' : ''}">
            <div class="kitchen-card-header">
                <div>
                    <div class="table-num">โต๊ะ ${order.table_number}</div>
                    <div class="order-num">#${order.order_number}</div>
                </div>
                <div class="order-time">${order.time_ago}</div>
            </div>
            <div class="kitchen-card-body">
                ${order.items.map(item => `
                    <div class="kitchen-item">
                        <span class="item-qty">${item.quantity}</span>
                        <div style="flex:1">
                            <div class="item-name">${item.menu_name}</div>
                            <div class="item-detail">
                                ${item.size === 'special' ? '🔴 พิเศษ' : 'ธรรมดา'}
                                ${item.spice_level > 0 ? ' | 🌶️'.repeat(item.spice_level) : ''}
                                ${item.options && item.options.length > 0 ? ' | ' + item.options.map(o => o.option_name).join(', ') : ''}
                                ${item.note ? ' | 📝 ' + item.note : ''}
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="kitchen-card-footer">
                ${order.status === 'pending' ?
            `<button class="btn btn-warning" onclick="updateOrder(${order.id}, 'cooking')">🔥 กำลังเตรียม</button>` :
            `<button class="btn btn-success" onclick="updateOrder(${order.id}, 'ready')">✅ พร้อมเสิร์ฟ</button>`
        }
            </div>
        </div>
    `).join('');
}

function updateOrder(orderId, status) {
    fetch('../kitchen/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId, status: status })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) fetchOrders();
            else alert(data.message || 'เกิดข้อผิดพลาด');
        })
        .catch(err => alert('เกิดข้อผิดพลาด'));
}

function playNotification() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();

        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
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
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(1320, ctx.currentTime + 0.1);
        osc2.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.4);
        gain2.gain.setValueAtTime(0, ctx.currentTime);
        gain2.gain.setValueAtTime(0.8, ctx.currentTime + 0.1);
        gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(ctx.currentTime + 0.1);
        osc2.stop(ctx.currentTime + 0.4);
    } catch (e) {
        console.error(e);
    }
}
