<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Competition> $competitions
 * @var array<int, \App\Model\Entity\Registration> $mineByCompetition
 * @var iterable<\App\Model\Entity\Registration> $rows
 */
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
                        <?= $this->element('competition_registration_card', [
                            'competition' => $competition,
                            'registration' => $mineByCompetition[(int)$competition->id] ?? null,
                            'deleteAction' => 'delete',
                        ]) ?>
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
