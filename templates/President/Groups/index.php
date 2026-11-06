<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Registration> $rows
 */

$this->assign('title', 'Csoportok');

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
            <div class="card-header"><strong>Csoportok</strong></div>
            <div class="card-body">
                <?php if ($grouped === []) : ?>
                    <p>Még nincs jelentkező.</p>
                <?php else : ?>
                    <?php foreach ($grouped as $group) : ?>
                        <h2 class="h5"><?= h($group['name']) ?></h2>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Név</th>
                                        <th>Alcsapat</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['rows'] as $registration) : ?>
                                        <tr>
                                            <td><?= $this->element('user_name', ['user' => $registration->user]) ?></td>
                                            <td><?= h($registration->team?->name ?? 'Nincs besorolva') ?></td>
                                            <td class="text-end">
                                                <?= $this->Html->link(
                                                    'Szerkesztés',
                                                    ['action' => 'edit', $registration->id],
                                                    ['class' => 'btn btn-sm btn-outline-primary']
                                                ) ?>
                                            </td>
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
