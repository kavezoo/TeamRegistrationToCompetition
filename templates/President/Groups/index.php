<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Registration> $rows
 * @var array<int|string, string> $teams
 */
$this->assign('title', 'Nevezők');

$grouped = [];
foreach ($rows as $row) {
    $key = (string)$row->competition_id;
    $grouped[$key]['name'] = (string)($row->competition->name ?? 'Verseny');
    $grouped[$key]['competition'] = $row->competition;
    $grouped[$key]['rows'][] = $row;
}
foreach ($grouped as $key => $group) {
    usort($group['rows'], [\App\Model\Entity\Registration::class, 'compareByAssignment']);
    $grouped[$key] = $group;
}
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header"><strong>Jelentkezők</strong></div>
            <div class="card-body">
                <?php if ($grouped === []) : ?>
                    <p>Még nincs jelentkező.</p>
                <?php else : ?>
                    <?php if ($teams === []) : ?>
                        <p>Előbb hozz létre alcsapatot, utána lehet besorolni a jelentkezőket. <?= $this->Html->link('Alcsapatok', ['controller' => 'Teams', 'action' => 'index']) ?></p>
                    <?php endif; ?>
                    <?php foreach ($grouped as $group) : ?>
                        <h2 class="h5"><?= h($group['name']) ?></h2>
                        <?php if ($group['competition'] !== null) : ?>
                            <?= $this->element('competition_schedule', ['competition' => $group['competition']]) ?>
                        <?php endif; ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Név</th>
                                        <th>Alcsapat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['rows'] as $registration) : ?>
                                        <tr>
                                            <td><?= $this->element('user_name', ['user' => $registration->user]) ?></td>
                                            <td><?= $this->element('registration_team_form', ['registration' => $registration, 'teams' => $teams]) ?></td>
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
