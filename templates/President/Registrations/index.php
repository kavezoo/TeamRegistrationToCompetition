<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Competition> $competitions
 * @var array<int, \App\Model\Entity\Registration> $mineByCompetition
 */
use App\Model\Entity\Registration;

$this->assign('title', 'Nevezésem');
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header"><strong>Nevezésem</strong></div>
            <div class="card-body">
                <?php if (count($competitions) === 0) : ?>
                    <p>Jelenleg nincs verseny, amire jelentkezni lehet.</p>
                <?php else : ?>
                    <?php foreach ($competitions as $competition) : ?>
                        <?php
                        $registration = $mineByCompetition[(int)$competition->id] ?? null;
                        $closed = $competition->date_deadline !== null && $competition->date_deadline->isPast();
                        ?>
                        <div class="border rounded p-3 mb-3">
                            <h2 class="h5 mb-1"><?= h($competition->name) ?></h2>
                            <p class="text-muted mb-2">
                                <?= h($competition->date_from) ?> – jelentkezési határidő: <?= h($competition->date_deadline) ?>
                            </p>
                            <?= $this->element('competition_description', ['competition' => $competition]) ?>
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
                                        <li>Megjegyzés: <?= h($registration->comment) ?></li>
                                    <?php endif; ?>
                                    <li>Alcsapat: <?= h($registration->team?->name ?? 'Nincs besorolva') ?></li>
                                </ul>
                                <div class="d-flex flex-wrap gap-2">
                                    <?= $this->Html->link(
                                        'Módosítás',
                                        ['action' => 'edit', $registration->id],
                                        ['class' => 'btn btn-outline-primary btn-sm']
                                    ) ?>
                                    <?= $this->Form->create(null, ['url' => ['action' => 'deleteOwn', $registration->id]]) ?>
                                        <?= $this->Form->button('Törlés', [
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'onclick' => 'return confirm(' . json_encode('Biztosan törlöd a jelentkezést?') . ');',
                                        ]) ?>
                                    <?= $this->Form->end() ?>
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
