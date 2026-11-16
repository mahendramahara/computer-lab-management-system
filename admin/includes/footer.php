        </main>

        <footer class="admin-footer">
            &copy; <?= date('Y') ?> Mahendra Multiple Campus &middot; Computer Lab Management System
        </footer>

    </div><!-- /admin-main -->
</div><!-- /admin-layout -->

<script src="<?= $base ?>assets/js/admin.js"></script>
<?php if (!empty($pageJs)): ?>
    <script src="<?= $base ?>assets/js/<?= (substr($pageJs, -3) === '.js') ? $pageJs : $pageJs . '.js' ?>"></script>
<?php endif; ?>

</body>
</html>