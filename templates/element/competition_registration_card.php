<?php
/**
 * Egy verseny a nevezési listán. A leírás a pipaclub.hu linkje.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var \App\Model\Entity\Registration|null $registration
 * @var string $deleteAction
 * @var string|null $deleteLabel
 */
use App\Model\Entity\Registration;

$registration = $registration ?? null;
$deleteLabel = $deleteLabel ?? 'Törlés';
$closed = $competition->registrationClosed();
?>
<div class="border rounded p-3 mb-3" id="competition-<?= (int)$competition->id ?>">
    <h2 class="h5 mb-2"><?= h($competition->name) ?></h2>
    <div class="pt-1">
            <?= $this->element('competition_schedule', ['competition' => $competition]) ?>
            <?= $this->element('competition_info_link', ['competition' => $competition]) ?>
            <?php if ($registration !== null) : ?>
                <ul class="list-unstyled mb-2">
                    <?php foreach (Registration::TITLED_COUNTS as $titleField => $countField) : ?>
                        <?php $title = trim((string)$competition->get($titleField)); ?>
                        <?php if ($title === '') {
                            continue;
                        } ?>
                        <li><?= h($title) ?>: <strong><?= (int)$registration->get($countField) ?></strong></li>
                    <?php endforeach; ?>
                    <?php if (trim((string)$registration->comment) !== '') : ?>
                        <li>Megjegyzés: <strong><?= h($registration->comment) ?></strong></li>
                    <?php endif; ?>
                    <li>Alcsapat: <strong><?= h($registration->team?->name ?? 'Nincs besorolva') ?></strong></li>
                </ul>
            <?php elseif ($closed || !(bool)$competition->visible) : ?>
                <p class="mb-0">A jelentkezés lezárult.</p>
            <?php endif; ?>
    </div>
    <?php if ($registration !== null) : ?>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <?= $this->Html->link(
                'Módosítás',
                ['action' => 'edit', $registration->id],
                ['class' => 'btn btn-outline-primary btn-sm']
            ) ?>
            <?php if (!$closed) : ?>
                <?= $this->Form->postLink(
                    $this->Icon->outline('trash') . ' ' . h($deleteLabel),
                    ['action' => $deleteAction, $registration->id],
                    [
                        'class' => 'btn btn-danger btn-sm',
                        'escape' => false,
                        'confirm' => 'Biztosan törlöd a jelentkezést?',
                        'data-swal-title' => __('Are you sure?'),
                        'data-swal-text' => 'Biztosan törlöd a jelentkezést? ' . __('This action cannot be undone.'),
                        'data-swal-confirm' => __('Yes, delete it!'),
                        'data-swal-cancel' => __('Cancel'),
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    <?php elseif (!$closed && (bool)$competition->visible) : ?>
        <div class="mt-3">
            <?= $this->Form->create(null, [
                'url' => ['action' => 'add', $competition->id],
            ]) ?>
                <?= $this->Form->button('Jelentkezés', ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </div>
    <?php endif; ?>
</div>
