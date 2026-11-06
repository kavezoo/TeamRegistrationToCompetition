<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var array<int|string, string> $teams
 * @var \App\Model\Entity\Registration|null $mine
 */
use App\Model\Entity\User;

$this->assign('title', $competition->name);
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?= h($competition->name) ?></strong>
                <span class="d-flex flex-wrap gap-2">
                    <?php if ($mine === null) : ?>
                        <?= $this->Form->create(null, [
                            'url' => ['controller' => 'Registrations', 'action' => 'join', $competition->id],
                            'class' => 'd-inline',
                        ]) ?>
                            <?= $this->Form->button('Jelentkezés', ['class' => 'btn btn-sm btn-primary']) ?>
                        <?= $this->Form->end() ?>
                    <?php else : ?>
                        <?= $this->Html->link(
                            'Saját jelentkezés',
                            ['controller' => 'Registrations', 'action' => 'edit', $mine->id],
                            ['class' => 'btn btn-sm btn-primary']
                        ) ?>
                    <?php endif; ?>
                    <?= $this->Html->link('Szerkesztés', ['action' => 'edit', $competition->id], ['class' => 'btn btn-sm btn-secondary']) ?>
                    <?= $this->Html->link('Vissza', ['action' => 'index'], ['class' => 'btn btn-sm btn-link']) ?>
                </span>
            </div>
            <div class="card-body">
                <p>
                    Időpont: <?= h((string)$competition->date_from) ?><br>
                    Nevezési határidő: <?= h((string)$competition->date_deadline) ?>
                </p>
                <?php if ($teams === []) : ?>
                    <p>Előbb hozz létre alcsapatot, utána lehet besorolni a nevezőket.</p>
                    <p><?= $this->Html->link('Alcsapatok', ['controller' => 'Teams', 'action' => 'index'], ['class' => 'btn btn-sm btn-primary']) ?></p>
                <?php endif; ?>
                <?php if (count($competition->registrations) === 0) : ?>
                    <p>Erre a versenyre még nem nevezett senki.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Alcsapat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($competition->registrations as $registration) : ?>
                                    <tr>
                                        <td><?= h(User::displayName($registration->user)) ?></td>
                                        <td>
                                            <?= $this->Form->create($registration, [
                                                'url' => ['controller' => 'Registrations', 'action' => 'assign', $registration->id],
                                                'class' => 'd-flex gap-2',
                                            ]) ?>
                                                <?= $this->Form->control('team_id', [
                                                    'label' => false,
                                                    'options' => $teams,
                                                    'empty' => $teams === [] ? 'Nincs alcsapat' : false,
                                                ]) ?>
                                                <?= $this->Form->button('Mentés', ['class' => 'btn btn-sm btn-primary', 'disabled' => $teams === []]) ?>
                                            <?= $this->Form->end() ?>
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
