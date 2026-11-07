<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Cake\Datasource\EntityInterface> $pending
 */

$this->assign('title', 'Jelentkezők');
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header"><strong>Új jelentkezők</strong></div>
            <div class="card-body">
                <?php if ($pending->count() === 0) : ?>
                    <p>Nincs elbírálásra váró jelentkező.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>E-mail</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $applicant) : ?>
                                    <tr>
                                        <td><?= $this->element('user_name', ['user' => $applicant]) ?></td>
                                        <td><?= h((string)$applicant->get('email')) ?></td>
                                        <td class="text-end">
                                            <?= $this->Form->create(null, [
                                                'url' => ['action' => 'approve', $applicant->get('id')],
                                                'style' => 'display:inline',
                                            ]) ?>
                                                <?= $this->Form->button('Elfogad', ['class' => 'btn btn-sm btn-success']) ?>
                                            <?= $this->Form->end() ?>
                                            <?php if ((string)$applicant->get('membership_request') !== 'denied') : ?>
                                                <?= $this->Form->create(null, [
                                                    'url' => ['action' => 'deny', $applicant->get('id')],
                                                    'style' => 'display:inline',
                                                    'data-swal-title' => 'Nem tag',
                                                    'data-swal-text' => 'Megjelölöd, hogy nem tag, csak kipróbálta az oldalt?',
                                                    'data-swal-confirm' => 'Igen',
                                                    'data-swal-cancel' => 'Mégse',
                                                ]) ?>
                                                    <?= $this->Form->button('Nem tag, csak kipróbálta', ['class' => 'btn btn-sm btn-danger']) ?>
                                                <?= $this->Form->end() ?>
                                            <?php endif; ?>
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
