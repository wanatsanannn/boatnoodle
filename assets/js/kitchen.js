// === JS สำหรับหน้าจอครัว ===

let lastOrderCount = 0;
let notificationSound = null;

document.addEventListener('DOMContentLoaded', function () {
    // สร้าง audio สำหรับแจ้งเตือน
    notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Hkot9bml0fYeRi3pqaHV+h5KKe2xqdn+IkYl6bGl2f4iSiXpsaXZ/iJKJe2xpdn6Hk4l7bGl2f4iTiXtranV+iJOJe2xqdn6Ik4l7bGp1foiTintranV+iJOJe2xqdX6Ik4p7bGl2foiTintranV+iJOKe2xqdX6Ik4p7bGp1foeTintranV+h5OKe2xpdX6Hk4p7bGp2foeTi3tsanV+h5OKe2xqdX6Hk4p7bGl1foeUi3tsanZ/h5OKe2xqdX6Hk4t7bGp2f4eTintranV+h5OLe2xqdn6Hk4p7bGp1foeTi3tsanZ+h5OKe2xqdX6HlIt7bGp2f4eTintranV+h5OLe2xqdn+Hk4p7bGp1foeTi3tsanZ+h5OKe2xpdX6HlIt7bGp2foeTintranV+h5OLe2xqdn6Hk4p7bGp1foeTi3tsanZ+h5OKe2xqdX6HlIt7bGp2foeTintranV+h5OKfGxqdn6Hk4p8bGp1foeTi3xsanV+h5OKfGxqdX6Hk4t8bGp1foeTinxsanV+h5OLfGxqdX6Hk4p8bGp1foeTi3xsanV+h5SKfGxqdn6Hk4p8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2f4eTinxsanV+h5OLfGxqdn6HlIp8bGp2foeTi3xsanZ+h5OKfGxqdX6HlIt8bGp2foeTinxsanV+h5OLfGxqdn6Hk4p8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2foeTinxsanV+h5OLfGxqdn6HlIp8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2foeTinxsanV+h5OLfGxqdn+Hk4p8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2foiTinxsanV+h5OLfGxqdn6HlIp8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2foeTinxsanV+h5OLfGxqdn6HlIp8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2foeTinxsanV+h5OLfGxqdn6HlIp8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2f4eTinxsanV+h5OLfGxqdn6Hk4p8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2foeTinxsanV+h5OLfGxqdn+HlIp8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2foeTinxsanV+h5OLfGxqdn6HlIp8bGp1foeTi3xsanZ/h5SKfGxqdX6Hk4t8bGp2foeTinxsanV+h5OLfGxqdn6HlIp8bGp1foeTi3xsanZ+h5SKfGxqdX6Hk4t8bGp2f4eTinxsanV+h5OLfGxqdn6Hk4p8bGp1foeTi3xsanZ+h5SKfGxqdX4=');

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
                // เล่นเสียงถ้ามีออเดอร์ใหม่
                if (data.orders.length > lastOrderCount && lastOrderCount > 0) {
                    playNotification();
                }
                lastOrderCount = data.orders.length;
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
    if (notificationSound) {
        notificationSound.play().catch(() => { });
    }
}
