<?php
/**
 * Új jelentkező törölheti a saját adatait.
 *
 * @var \App\View\AppView $this
 */
?>
<p class="mb-2">Ha csak véletlenül jártál erre az oldalra, és nem szeretnél tag lenni, az <strong>Az adataim törlése</strong> gomb törli a nevedet és a belépésedet az oldalról, majd kijelentkeztet.</p>
<?= $this->Form->create(null, [
    'url' => '/fiok-torlese',
    'data-swal-title' => 'Adatok törlése',
    'data-swal-text' => 'Törlöd az adataidat az oldalról? A fiókod ezután nem létezik, kijelentkeztetünk, és ez nem vonható vissza.',
    'data-swal-confirm' => 'Igen, törlöm',
    'data-swal-cancel' => 'Mégse',
]) ?>
    <?= $this->Form->button('Az adataim törlése', ['class' => 'btn btn-danger']) ?>
<?= $this->Form->end() ?>
