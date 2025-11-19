<?php
/**
 * Public Interface Footer
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */
?>
        </main>

        <footer class="app-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </footer>
    </div>

    <script src="<?php echo ASSETS_URL; ?>/js/public.js"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php echo $additionalScripts; ?>
    <?php endif; ?>
</body>
</html>
