<?php
/**
 * Admin Panel Footer
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */
?>
        </main>
    </div>

    <script src="<?php echo ASSETS_URL; ?>/js/admin.js"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php echo $additionalScripts; ?>
    <?php endif; ?>
</body>
</html>
