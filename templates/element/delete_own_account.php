<?php
/**
 * Új jelentkező törölheti a saját adatait.
 *
 * @var \App\View\AppView $this
 */
?>
<?= $this->Form->create(null, [
    'url' => ['controller' => 'Pages', 'action' => 'deleteAccount'],
    'data-swal-title' => 'Adatok törlése',
    'data-swal-text' => 'Törlöd az adataidat az oldalról? A fiókod ezután nem létezik, és ez nem vonható vissza.',
    'data-swal-confirm' => 'Igen, törlöm',
    'data-swal-cancel' => 'Mégse',
]) ?>
    <?= $this->Form->button('Az adataim törlése', ['class' => 'btn btn-danger']) ?>
<?= $this->Form->end() ?>
