<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Team> $teams
 */
$this->assign('title', 'Alcsapatok');
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Alcsapatok</strong>
                <?= $this->Html->link('Új alcsapat', ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']) ?>
            </div>
            <div class="card-body">
                <?php if ($teams->count() === 0) : ?>
                    <p>Még nincs alcsapat.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Sorrend</th>
                                    <th>Látható</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teams as $team) : ?>
                                    <tr>
                                        <td><?= h($team->name) ?></td>
                                        <td><?= (int)$team->pos ?></td>
                                        <td><?= $team->visible ? 'Igen' : 'Nem' ?></td>
                                        <td class="text-end">
                                            <?= $this->Html->link('Szerkesztés', ['action' => 'edit', $team->id], ['class' => 'btn btn-sm btn-secondary']) ?>
                                        </td>
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
