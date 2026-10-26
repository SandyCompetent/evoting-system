    <footer class="mt-auto py-3 bg-white border-top text-center text-muted small">
        <div class="container-fluid">
            <span>&copy; <?= date('Y') ?> e-Voting Portal &bull; For Portfolio and Demo purposes only. Not for commercial use.</span>
        </div>
    </footer>
    <script src="<?= e(BOOTSTRAP_JS); ?>"></script>
    <script src="<?= e(asset_url('assets/js/app.js')); ?>"></script>
    <?php foreach ((is_array($pageScripts ?? null) ? $pageScripts : []) as $pageScript): ?>
        <script src="<?= e($pageScript); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
