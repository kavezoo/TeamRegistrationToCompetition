                                                                                                                                                                            <?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $teams
 * @var \Cake\Collection\CollectionInterface|string[] $competitions
 */

use Cake\Core\Configure;

$show = Configure::read('JeffAdmin');
$showLocal = [];

$showLocal['view'] = [
//    'editButton'      => false,
//    'cancelButton'    => false,
//    'deleteButton'    => false,
//    'relatedTables'   => false,
//    'posStep'         => 5,   // pos spinner lépés (alap: 10)
];

$show = array_merge($show['view'] ?? [], $showLocal['view']);
$relatedShow = array_merge(
    ['deleteGuardByCounts' => true],
    Configure::read('JeffAdmin.index') ?? []
);
// Kapcsolt tábla felülírás asszociáció alias szerint (pl. 'Items'):
$relatedLocal = [
//    'Items' => [
//        'deleteGuardByCounts' => false, // *_count ne tiltsa a törlést
//        // 'deleteButton' => false,
//    ],
];
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <?= $this->Form->create($registration, [
                      'class' => 'form-horizontal',
                      'id' => 'registration-view-form',
                      'onsubmit' => 'return false;',
                  ]) ?>
                  <div class="card shadow" aria-labelledby="registration-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="registration-form-title"><?= __('View Registration') ?></strong>
                        <small class="d-block"><?= h($registration->user_id) ?></small>
                      </div>

                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
<?php if (!empty($show['relatedTables'])) : ?>
                        <li class="nav-item dropdown ms-auto" role="presentation">
                          <a class="nav-link dropdown-toggle" href="#" id="tab-related-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Related tables') ?></a>
                          <ul class="dropdown-menu" aria-labelledby="tab-related-btn">
                            <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List Teams'), ['controller' => 'Teams', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List Competitions'), ['controller' => 'Competitions', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                          </ul>
                        </li>
<?php endif; ?>
                        <li class="nav-item<?= empty($show['relatedTables']) ? ' ms-auto' : '' ?>" role="presentation">
                          <a class="nav-link" id="tab-settings-btn" data-bs-toggle="tab" href="#tab-settings" role="tab" aria-controls="tab-settings" aria-selected="false"><?= __('Settings') ?></a>
                        </li>

                      </ul>

                      <?= $this->Action->close($registration->id) ?>
                    </div>

                    <div class="card-body form-card-body">
                      <div class="tab-content">

                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('user_id', __('User Id') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <div class="select-with-action">
                                <?= $this->Input->control('user_id', ['options' => $users, 'class' => 'form-select', 'data-tom-select' => true, 'disabled' => true]) ?>
                                <button type="button" class="btn btn-outline-secondary select-with-action__btn" disabled aria-label="<?= h(__('More options')) ?>" data-bs-toggle="tooltip" data-bs-title="<?= h(__('More options')) ?>">
                                  <?= $this->Icon->outline('dots') ?>
                                </button>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('team_id', __('Team Id') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <div class="select-with-action">
                                <?= $this->Input->control('team_id', ['options' => $teams, 'class' => 'form-select', 'data-tom-select' => true, 'disabled' => true]) ?>
                                <button type="button" class="btn btn-outline-secondary select-with-action__btn" disabled aria-label="<?= h(__('More options')) ?>" data-bs-toggle="tooltip" data-bs-title="<?= h(__('More options')) ?>">
                                  <?= $this->Icon->outline('dots') ?>
                                </button>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('competition_id', __('Competition Id') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <div class="select-with-action">
                                <?= $this->Input->control('competition_id', ['options' => $competitions, 'class' => 'form-select', 'data-tom-select' => true, 'disabled' => true]) ?>
                                <button type="button" class="btn btn-outline-secondary select-with-action__btn" disabled aria-label="<?= h(__('More options')) ?>" data-bs-toggle="tooltip" data-bs-title="<?= h(__('More options')) ?>">
                                  <?= $this->Icon->outline('dots') ?>
                                </button>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('competition_pipe_1', __('Competition Pipe 1') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('competition_pipe_1', ['type' => 'text', 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('competition_pipe_2', __('Competition Pipe 2') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('competition_pipe_2', ['type' => 'text', 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('competition_pipe_3', __('Competition Pipe 3') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('competition_pipe_3', ['type' => 'text', 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('extra_count_1', __('Extra Count 1') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('extra_count_1', ['type' => 'text', 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('extra_count_2', __('Extra Count 2') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('extra_count_2', ['type' => 'text', 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('extra_count_3', __('Extra Count 3') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('extra_count_3', ['type' => 'text', 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                        </div>

                        <div class="tab-pane fade" id="tab-settings" role="tabpanel" aria-labelledby="tab-settings-btn" tabindex="0">
                          <section class="form-section">
                            <h5 class="form-section__title"><?= __('Record settings') ?></h5>
                            <div class="row mb-3 align-items-center">
                              <div class="col-12 col-md-2 text-start text-md-end">
                                <?= $this->Form->label('visible', __('Visible') . ':', ['class' => 'form-control-label fw-bold mb-0']) ?>
                              </div>
                              <div class="col-12 col-md-9 d-flex align-items-center flex-wrap pt-1">
                                <div class="form-check form-check-inline mb-0">
                                  <?= $this->Form->checkbox('visible', ['class' => 'form-check-input', 'id' => 'visible', 'disabled' => true]) ?>
                                  <?= $this->Form->label('visible', __('Visible'), ['class' => 'form-check-label']) ?>
                                </div>
                              </div>
                            </div>
                            <?php
                            $posStep = max(1, (int)($show['posStep'] ?? 10));
                            ?>
                            <div class="row mb-3">
                              <div class="col-12 col-md-2 text-start text-md-end">
                                <?= $this->Form->label('pos', __('Pos') . ':', ['class' => 'form-control-label fw-bold']) ?>
                              </div>
                              <div class="col-12 col-md-9">
                                <?= $this->Input->control('pos', ['type' => 'text', 'class' => 'form-control', 'data-number-spinner' => true, 'data-integer' => '1', 'inputmode' => 'numeric', 'min' => '0', 'step' => (string)$posStep, 'autocomplete' => 'off', 'placeholder' => '0', 'disabled' => true]) ?>
                              </div>
                            </div>
                          </section>
                        </div>

                      </div>
                    </div>

                    <div class="card-footer border-top">
                      <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
<?php if (!empty($show['editButton'])) : ?>
                        <?= $this->Action->editButton($registration->id) ?>
<?php endif; ?>
<?php if (!empty($show['cancelButton'])) : ?>
                        <?= $this->Action->cancelButton($registration->id) ?>
<?php endif; ?>
<?php if (!empty($show['deleteButton'])) : ?>
                        <div class="ms-auto">
                          <?= $this->Action->deleteButton($registration->id, [], ['entity' => $registration]) ?>
                        </div>
<?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>

                </div>
              </div>
