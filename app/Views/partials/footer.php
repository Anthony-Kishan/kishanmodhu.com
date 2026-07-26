<?php
/** @var App\Models\Setting $settings */
?>
<footer id="footer" class="footer">
    <div class="container">
        <div class="footer-section">
            <div class="brand-container">
                <div class="brand-text"><?= e($settings->get('footer_brand')) ?></div>
                <div class="green-circle"></div>
            </div>

            <div class="container p-0">
                <div class="row footer-row justify-content-between align-items-center">
                    <div class="col-auto">
                        <div class="footer-text"><?= e($settings->get('footer_copyright')) ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="footer-text"><?= e($settings->get('footer_location')) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
