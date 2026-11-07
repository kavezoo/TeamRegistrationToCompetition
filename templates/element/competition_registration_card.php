<?php
/**
 * Egy verseny a nevezési listán. A leírás külön fülön van, hogy ne foglaljon helyet.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var \App\Model\Entity\Registration|null $registration
 * @var string $deleteAction
 */
use App\Model\Entity\Registration;

$registration = $registration ?? null;
$closed = $competition->registrationClosed();
$tab = 'competition-' . (int)$competition->id;
?>
<div class="border rounded p-3 mb-3">
    <h2 class="h5 mb-2"><?= h($competition->name) ?></h2>
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="<?= h($tab) ?>-data-btn" data-bs-toggle="tab" href="#<?= h($tab) ?>-data" role="tab" aria-controls="<?= h($tab) ?>-data" aria-selected="true">Adatok</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="<?= h($tab) ?>-description-btn" data-bs-toggle="tab" href="#<?= h($tab) ?>-description" role="tab" aria-controls="<?= h($tab) ?>-description" aria-selected="false">Leírás</a>
        </li>
    </ul>
    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="<?= h($tab) ?>-data" role="tabpanel" aria-labelledby="<?= h($tab) ?>-data-btn" tabindex="0">
            <?= $this->element('competition_schedule', ['competition' => $competition]) ?>
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
                <div class="d-flex flex-wrap gap-2">
                    <?= $this->Html->link(
                        'Módosítás',
                        ['action' => 'edit', $registration->id],
                        ['class' => 'btn btn-outline-primary btn-sm']
                    ) ?>
                    <?php if (!$closed) : ?>
                        <?= $this->Form->create(null, ['url' => ['action' => $deleteAction, $registration->id]]) ?>
                            <?= $this->Form->button('Törlés', [
                                'class' => 'btn btn-outline-danger btn-sm',
                                'onclick' => 'return confirm(' . json_encode('Biztosan törlöd a jelentkezést?') . ');',
                            ]) ?>
                        <?= $this->Form->end() ?>
                    <?php endif; ?>
                </div>
            <?php elseif ($closed || !(bool)$competition->visible) : ?>
                <p class="mb-0">A jelentkezés lezárult.</p>
            <?php else : ?>
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'add', $competition->id],
                ]) ?>
                    <?= $this->Form->button('Jelentkezés', ['class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="<?= h($tab) ?>-description" role="tabpanel" aria-labelledby="<?= h($tab) ?>-description-btn" tabindex="0">
            <?php if ($competition->descriptionHtml() === null) : ?>
                <p class="mb-0">Nincs leírás.</p>
            <?php else : ?>
                <?= $this->element('competition_description', ['competition' => $competition]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
