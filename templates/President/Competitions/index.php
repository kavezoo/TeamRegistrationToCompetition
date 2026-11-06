<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Competition> $competitions
 */
$this->assign('title', 'Versenyek');
?>
<?= $this->Flash->render() ?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Versenyek</strong>
                <?= $this->Html->link('Új verseny', ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']) ?>
            </div>
            <div class="card-body">
                <?php if ($competitions->count() === 0) : ?>
                    <p>Még nincs kiírt verseny.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Időpont</th>
                                    <th>Nevezési határidő</th>
                                    <th>Nevezők</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($competitions as $competition) : ?>
                                    <tr>
                                        <td><?= h($competition->name) ?></td>
                                        <td><?= h((string)$competition->date_from) ?></td>
                                        <td><?= h((string)$competition->date_deadline) ?></td>
                                        <td><?= (int)$competition->registration_count ?></td>
                                        <td class="text-end">
                                            <?= $this->Html->link('Nevezők', ['action' => 'view', $competition->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                            <?= $this->Html->link('Szerkesztés', ['action' => 'edit', $competition->id], ['class' => 'btn btn-sm btn-secondary']) ?>
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
