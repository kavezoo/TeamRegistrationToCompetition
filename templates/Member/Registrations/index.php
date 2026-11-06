<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Registration> $mine
 * @var iterable<\App\Model\Entity\Registration> $rows
 */
use App\Model\Entity\User;

$this->assign('title', 'Jelentkezők');

$grouped = [];
foreach ($rows as $row) {
    $key = (string)$row->competition_id;
    $grouped[$key]['name'] = (string)$row->competition->name;
    $grouped[$key]['rows'][] = $row;
}
?>
<?= $this->Flash->render() ?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header"><strong>Saját besorolás</strong></div>
            <div class="card-body">
                <?php if ($mine->count() === 0) : ?>
                    <p>Még nincs versenynevezésed, vagy a klubelnök még nem sorolt alcsapatba.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Verseny</th>
                                    <th>Alcsapat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mine as $registration) : ?>
                                    <tr>
                                        <td><?= h($registration->competition->name) ?></td>
                                        <td><?= h($registration->team->name ?? 'Nincs besorolva') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                                            <td><?= h($registration->team->name ?? 'Nincs besorolva') ?></td>
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
