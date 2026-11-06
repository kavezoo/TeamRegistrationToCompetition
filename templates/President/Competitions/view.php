<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var array<int|string, string> $teams
 * @var \App\Model\Entity\Registration|null $mine
 */
use App\Model\Entity\Registration;
use App\Model\Entity\User;

$this->assign('title', $competition->name);
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?= h($competition->name) ?></strong>
                <span>
                    <?= $this->Html->link('Szerkesztés', ['action' => 'edit', $competition->id], ['class' => 'btn btn-sm btn-secondary']) ?>
                    <?= $this->Html->link('Vissza', ['action' => 'index'], ['class' => 'btn btn-sm btn-link']) ?>
                </span>
            </div>
            <div class="card-body">
                <p>
                    Időpont: <?= h((string)$competition->date_from) ?><br>
                    Nevezési határidő: <?= h((string)$competition->date_deadline) ?>
                </p>
                <h2 class="h5">Saját jelentkezés</h2>
                <?php if ($mine === null) : ?>
                    <p>Erre a versenyre még nem jelentkeztél.</p>
                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Registrations', 'action' => 'join', $competition->id],
                        'class' => 'mb-4',
                    ]) ?>
                        <?= $this->Form->button('Jelentkezés', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                <?php else : ?>
                    <?= $this->Form->create($mine, [
                        'url' => ['controller' => 'Registrations', 'action' => 'saveOwn', $mine->id],
                        'class' => 'mb-4',
                    ]) ?>
                        <?php foreach (Registration::TITLED_COUNTS as $titleField => $countField) : ?>
                            <?php $title = trim((string)$competition->get($titleField)); ?>
                            <?php if ($title === '') {
                                continue;
                            } ?>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label fw-bold" for="own-<?= h($countField) ?>"><?= h($title) ?></label>
                                <div class="col-md-4">
                                    <?= $this->Input->control($countField, [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => 'form-control',
                                        'id' => 'own-' . $countField,
                                        'data-number-spinner' => true,
                                        'data-integer' => '1',
                                        'inputmode' => 'numeric',
                                        'min' => '0',
                                        'max' => '1000',
                                        'step' => '1',
                                        'autocomplete' => 'off',
                                        'value' => (int)($mine->get($countField) ?? 0),
                                    ]) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label fw-bold" for="own-comment">Megjegyzés</label>
                            <div class="col-md-8">
                                <?= $this->Form->control('comment', [
                                    'type' => 'textarea',
                                    'label' => false,
                                    'class' => 'form-control',
                                    'id' => 'own-comment',
                                    'rows' => 3,
                                    'maxlength' => 1000,
                                ]) ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label fw-bold" for="own-team">Alcsapat</label>
                            <div class="col-md-4">
                                <?= $this->Form->control('team_id', [
                                    'label' => false,
                                    'id' => 'own-team',
                                    'options' => $teams,
                                    'empty' => 'Nincs besorolva',
                                    'class' => 'form-select',
                                ]) ?>
                            </div>
                        </div>
                        <?= $this->Form->button('Mentés', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Registrations', 'action' => 'deleteOwn', $mine->id],
                        'class' => 'mb-4',
                    ]) ?>
                        <?= $this->Form->button('Jelentkezés törlése', [
                            'class' => 'btn btn-outline-danger',
                            'onclick' => 'return confirm(' . json_encode('Biztosan törlöd a jelentkezést?') . ');',
                        ]) ?>
                    <?= $this->Form->end() ?>
                <?php endif; ?>
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
