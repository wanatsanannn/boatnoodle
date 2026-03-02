// === ระบบตะกร้าสินค้า (localStorage) ===

const Cart = {
    KEY: 'noodle_cart',
    TABLE_KEY: 'noodle_table',

    // ดึงรายการทั้งหมด
    getItems() {
        return JSON.parse(localStorage.getItem(this.KEY) || '[]');
    },

    // บันทึกรายการ
    save(items) {
        localStorage.setItem(this.KEY, JSON.stringify(items));
        this.updateCartBar();
    },

    // เปรียบเทียบ options 2 ชุด
    optionsMatch(a, b) {
        if (!a && !b) return true;
        if (!a || !b) return false;
        if (a.length !== b.length) return false;
        const idsA = a.map(o => o.choice_id).sort();
        const idsB = b.map(o => o.choice_id).sort();
        return idsA.every((v, i) => v === idsB[i]);
    },

    // เพิ่มรายการ
    addItem(item) {
        const items = this.getItems();
        // เช็คว่ามีรายการเดียวกัน (เมนู+ขนาด+เผ็ด+options) หรือไม่
        const idx = items.findIndex(i =>
            i.menu_id === item.menu_id &&
            i.size === item.size &&
            i.spice_level === item.spice_level &&
            this.optionsMatch(i.options, item.options)
        );
        if (idx >= 0) {
            items[idx].quantity += item.quantity;
            items[idx].subtotal = items[idx].quantity * items[idx].unit_price;
        } else {
            items.push(item);
        }
        this.save(items);
    },

    // ลบรายการ
    removeItem(index) {
        const items = this.getItems();
        items.splice(index, 1);
        this.save(items);
    },

    // อัปเดตจำนวน
    updateQuantity(index, qty) {
        const items = this.getItems();
        if (qty <= 0) {
            items.splice(index, 1);
        } else {
            items[index].quantity = qty;
            items[index].subtotal = qty * items[index].unit_price;
        }
        this.save(items);
    },

    // ยอดรวม
    getTotal() {
        return this.getItems().reduce((sum, i) => sum + i.subtotal, 0);
    },

    // จำนวนรายการ
    getCount() {
        return this.getItems().reduce((sum, i) => sum + i.quantity, 0);
    },

    // ล้างตะกร้า
    clear() {
        localStorage.removeItem(this.KEY);
        this.updateCartBar();
    },

    // เก็บหมายเลขโต๊ะ
    setTable(tableNum) {
        localStorage.setItem(this.TABLE_KEY, tableNum);
    },

    getTable() {
        return localStorage.getItem(this.TABLE_KEY) || '';
    },

    // อัปเดต header cart icon + checkout bar
    updateCartBar() {
        const count = this.getCount();
        const total = this.getTotal();

        // Header cart icon — แสดงตลอด แต่ซ่อน badge เมื่อไม่มีของ
        const headerCart = document.getElementById('headerCart');
        const headerBadge = document.getElementById('headerCartBadge');
        if (headerCart) {
            if (count > 0) {
                headerCart.classList.add('cart-has-items');
                if (headerBadge) {
                    headerBadge.textContent = count;
                    headerBadge.style.display = '';
                }
            } else {
                headerCart.classList.remove('cart-has-items');
                if (headerBadge) headerBadge.style.display = 'none';
            }
        }

        // Checkout bar — ซ่อนเมื่อไม่มีของ
        const checkoutBar = document.getElementById('checkoutBar');
        const checkoutCount = document.getElementById('checkoutCount');
        const checkoutTotal = document.getElementById('checkoutTotal');
        if (checkoutBar) {
            checkoutBar.classList.toggle('empty', count === 0);
            if (checkoutCount) checkoutCount.textContent = count;
            if (checkoutTotal) checkoutTotal.textContent = '฿' + total.toFixed(2);
        }
    }
};

// อัปเดต cart bar เมื่อโหลดหน้า
document.addEventListener('DOMContentLoaded', () => Cart.updateCartBar());
