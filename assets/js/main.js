// === JS กลาง ===

// ยืนยันก่อนลบ
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-delete')) {
        if (!confirm('ยืนยันการลบ?')) {
            e.preventDefault();
        }
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
});
