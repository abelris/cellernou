            </main>
            <!-- End Page Content -->

            <!-- Footer -->
            <footer style="text-align: center; padding: 1.5rem; color: var(--gray-500); font-size: 0.875rem; border-top: 1px solid var(--gray-200); background: white;">
                ALMABRIS GROUP 2024 S.L. &copy; <?php echo date('Y'); ?> - Panell d'Administració
            </footer>
        </div>
        <!-- End Main Content -->
    </div>

    <script src="<?php echo getAdminUrl('assets/js/admin.js'); ?>"></script>
    <?php if (isset($extraJs)): ?>
    <?php echo $extraJs; ?>
    <?php endif; ?>
</body>
</html>
