<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Competition> $competitions
 * @var array<int, \App\Model\Entity\Registration> $mineByCompetition
 * @var iterable<\App\Model\Entity\Registration> $rows
 */
use App\Model\Entity\Registration;
use App\Model\Entity\User;

$this->assign('title', 'Versenyek');

$grouped = [];
foreach ($rows as $row) {
    $key = (string)$row->competition_id;
    $grouped[$key]['name'] = (string)$row->competition->name;
    $grouped[$key]['rows'][] = $row;
}
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header"><strong>Versenyek</strong></div>
            <div class="card-body">
                <?php if (count($competitions) === 0) : ?>
                    <p>Jelenleg nincs verseny, amire jelentkezni lehet.</p>
                <?php else : ?>
                    <?php foreach ($competitions as $competition) : ?>
                        <?php
                        $registration = $mineByCompetition[(int)$competition->id] ?? null;
                        $closed = $competition->registrationClosed();
                        ?>
                        <div class="border rounded p-3 mb-3">
                            <h2 class="h5 mb-1"><?= h($competition->name) ?></h2>
                            <?= $this->element('competition_schedule', ['competition' => $competition]) ?>
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
                                    <?php if (!$closed) : ?>
                                        <?= $this->Form->create(null, ['url' => ['action' => 'delete', $registration->id]]) ?>
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header"><strong>Jelentkezők</strong></div>
            <div class="card-body">
                <?php if ($grouped === []) : ?>
                    <p>Még nincs jelentkező.</p>
                <?php else : ?>
                    <?php foreach ($grouped as $group) : ?>
                        <h2 class="h5"><?= h($group['name']) ?></h2>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Név</th>
                                        <th>Alcsapat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['rows'] as $registration) : ?>
                                        <tr>
                                            <td><?= h(User::displayName($registration->user)) ?></td>
                                            <td><?= h($registration->team?->name ?? 'Nincs besorolva') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
