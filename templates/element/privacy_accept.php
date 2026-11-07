<?php
/**
 * Az adatkezelési nyilatkozat elfogadása a profil mentése előtt.
 * A gomb a már kirakott nyilatkozatablakot nyitja.
 *
 * @var \App\View\AppView $this
 */
?>
<div class="d-flex align-items-center flex-wrap gap-2 offset-md-2 mb-3">
    <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" name="accept_privacy" value="1" id="accept-privacy" required oninvalid="this.setCustomValidity('A mentéshez el kell fogadni az adatkezelési nyilatkozatot.')" onchange="this.setCustomValidity('')">
        <label class="form-check-label" for="accept-privacy">Elfogadom az adatkezelési nyilatkozatban foglaltakat.</label>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#privacy-notice-modal">Adatkezelési nyilatkozat</button>
</div>
