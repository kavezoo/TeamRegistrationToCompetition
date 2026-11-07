<?php
/**
 * Az adatkezelési nyilatkozat modal ablaka a new és a member felületen.
 *
 * @var \App\View\AppView $this
 */
?>
<div class="modal fade" id="privacy-notice-modal" tabindex="-1" aria-labelledby="privacy-notice-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="privacy-notice-title">Adatkezelési nyilatkozat</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
            </div>
            <div class="modal-body">
                <?= $this->element('privacy_statement') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
            </div>
        </div>
    </div>
</div>
