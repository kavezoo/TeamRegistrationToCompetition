<?php
/**
 * Alcsapat választó egy nevezéshez.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var array<int|string, string> $teams
 */
?>
<?= $this->Form->create($registration, [
    'url' => ['prefix' => 'President', 'controller' => 'Registrations', 'action' => 'assign', $registration->id],
    'class' => 'd-flex flex-wrap gap-2 align-items-center',
]) ?>
    <?= $this->Form->control('team_id', [
        'label' => false,
        'options' => $teams,
        'empty' => 'Nincs besorolva',
        'class' => 'form-select form-select-sm',
        'style' => 'min-width: 12rem; width: auto;',
    ]) ?>
    <?= $this->Form->button('Mentés', [
        'class' => 'btn btn-sm btn-primary',
        'disabled' => $teams === [],
    ]) ?>
<?= $this->Form->end() ?>
