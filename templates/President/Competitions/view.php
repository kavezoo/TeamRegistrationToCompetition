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

<div class="row row-tight" style="margin-top: 16px;">
    <div class="col-12 col-xxl-11">
        <?php if ($mine === null) : ?>
            <div class="card shadow">
                <div class="card-header">
                    <strong>Saját jelentkezés</strong>
                    <small class="d-block text-muted"><?= h($competition->name) ?></small>
                </div>
                <div class="card-body">
                    <p>Erre a versenyre még nem jelentkeztél.</p>
                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Registrations', 'action' => 'join', $competition->id],
                    ]) ?>
                        <?= $this->Form->button('Jelentkezés', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        <?php else : ?>
            <?= $this->Form->create($mine, [
                'id' => 'own-registration-form',
                'url' => ['controller' => 'Registrations', 'action' => 'saveOwn', $mine->id],
                'class' => 'form-horizontal',
            ]) ?>
            <div class="card shadow" aria-labelledby="own-registration-title">
                <div class="card-header form-card-header">
                    <div class="form-card-header__title">
                        <strong id="own-registration-title">Saját jelentkezés</strong>
                        <small class="d-block"><?= h($competition->name) ?></small>
                    </div>
                    <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="ownFormTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="own-tab-basic-btn" data-bs-toggle="tab" href="#own-tab-basic" role="tab" aria-controls="own-tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
                    </ul>
                </div>
                <div class="card-body form-card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="own-tab-basic" role="tabpanel" aria-labelledby="own-tab-basic-btn" tabindex="0">
                            <?php foreach (Registration::TITLED_COUNTS as $titleField => $countField) : ?>
                                <?php $title = trim((string)$competition->get($titleField)); ?>
                                <?php if ($title === '') {
                                    continue;
                                } ?>
                                <div class="row mb-3">
                                    <div class="col-12 col-md-2 text-start text-md-end">
                                        <?= $this->Form->label('own-' . $countField, $title . ':', ['class' => 'form-control-label fw-bold']) ?>
                                    </div>
                                    <div class="col-12 col-md-9">
                                        <?= $this->Input->control($countField, [
                                            'type' => 'text',
                                            'label' => false,
                                            'id' => 'own-' . $countField,
                                            'class' => 'form-control',
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
                                <div class="col-12 col-md-2 text-start text-md-end">
                                    <?= $this->Form->label('own-comment', 'Megjegyzés:', ['class' => 'form-control-label fw-bold']) ?>
                                </div>
                                <div class="col-12 col-md-9">
                                    <?= $this->Form->control('comment', [
                                        'type' => 'textarea',
                                        'label' => false,
                                        'id' => 'own-comment',
                                        'class' => 'form-control',
                                        'rows' => 4,
                                        'maxlength' => 1000,
                                    ]) ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12 col-md-2 text-start text-md-end">
                                    <?= $this->Form->label('own-team', 'Alcsapat:', ['class' => 'form-control-label fw-bold']) ?>
                                </div>
                                <div class="col-12 col-md-9">
                                    <?= $this->Form->control('team_id', [
                                        'label' => false,
                                        'id' => 'own-team',
                                        'options' => $teams,
                                        'empty' => 'Nincs besorolva',
                                        'class' => 'form-select',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top">
                    <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
                        <?= $this->Form->button(
                            $this->Icon->outline('device-floppy') . ' ' . __('Save'),
                            ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                        ) ?>
                        <div class="ms-auto">
                            <button type="submit" class="btn btn-outline-danger" form="own-registration-delete" onclick="return confirm(<?= h(json_encode('Biztosan törlöd a jelentkezést?')) ?>);">Jelentkezés törlése</button>
                        </div>
                    </div>
                </div>
            </div>
            <?= $this->Form->end() ?>
            <?= $this->Form->create(null, [
                'id' => 'own-registration-delete',
                'url' => ['controller' => 'Registrations', 'action' => 'deleteOwn', $mine->id],
            ]) ?>
            <?= $this->Form->end() ?>
        <?php endif; ?>
    </div>
</div>
