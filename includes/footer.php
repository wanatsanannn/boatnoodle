    </div><!-- /.container-fluid -->

    <footer class="text-center text-muted py-3 mt-4 border-top">
        <small>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?> — ระบบการสั่งอาหารออนไลน์ผ่านคิวอาร์โคด</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>
