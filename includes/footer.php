        </main>

        <footer class="footer">
            <span>&copy; <?= date('Y') ?> Mahendra Multiple Campus. All rights reserved.</span>
            <span>Computer Lab Management System</span>
        </footer>

    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<?php if (!empty($pageJs)) { ?>
    <script src="<?= BASE_URL ?>assets/js/<?= str_replace('.js', '', $pageJs) ?>.js"></script>
<?php } ?>
</body>
</html>