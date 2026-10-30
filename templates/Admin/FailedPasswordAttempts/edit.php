<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FailedPasswordAttempt $failedPasswordAttempt
 * @var \Cake\Collection\CollectionInterface|string[] $users
 */

use Cake\Core\Configure;

$show = Configure::read('JeffAdmin');
$showLocal = [];

$showLocal['edit'] = [
//    'saveButton'     => false,
//    'cancelButton'   => false,
//    'deleteButton'   => false,
//    'relatedTables'  => false,
//    'posStep'        => 5,   // pos spinner lépés (alap: 10)
];

$show = array_merge($show['edit'] ?? [], $showLocal['edit']);
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <?= $this->Form->create($failedPasswordAttempt, [
                      'class' => 'form-horizontal',
                  ]) ?>
                  <div class="card shadow" aria-labelledby="failedPasswordAttempt-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="failedPasswordAttempt-form-title"><?= __('Edit Failed Password Attempt') ?></strong>
                        <small class="d-block"><?= h($failedPasswordAttempt->id) ?></small>
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
                          </ul>
                        </li>
<?php endif; ?>

                      </ul>

                      <?= $this->Action->close($failedPasswordAttempt->id) ?>
                    </div>

                    <div class="card-body form-card-body">
                      <div class="tab-content">
<div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('user_id', __('User') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <div class="select-with-action">
                                <?= $this->Input->control('user_id', ['options' => $users, 'empty' => __('Please select'), 'class' => 'form-select', 'data-tom-select' => true]) ?>
                                <button type="button" class="btn btn-outline-secondary select-with-action__btn" aria-label="<?= h(__('More options')) ?>" data-bs-toggle="tooltip" data-bs-title="<?= h(__('More options')) ?>">
                                  <?= $this->Icon->outline('dots') ?>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>


                      </div>
                    </div>

                    <div class="card-footer border-top">
                      <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
<?php if (!empty($show['saveButton'])) : ?>
                        <?= $this->Form->button(
                            $this->Icon->outline('device-floppy') . ' ' . __('Save'),
                            ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                        ) ?>
<?php endif; ?>
<?php if (!empty($show['cancelButton'])) : ?>
                        <?= $this->Action->cancelButton($failedPasswordAttempt->id) ?>
<?php endif; ?>
<?php if (!empty($show['deleteButton'])) : ?>
                        <div class="ms-auto">
                          <?= $this->Action->deleteButton($failedPasswordAttempt->id, [], ['entity' => $failedPasswordAttempt]) ?>
                        </div>
<?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>
                </div>
              </div>
