                        <?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FailedPasswordAttempt $failedPasswordAttempt
 * @var \Cake\Collection\CollectionInterface|string[] $users
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
                  <?= $this->Form->create($failedPasswordAttempt, [
                      'class' => 'form-horizontal',
                      'id' => 'failedPasswordAttempt-view-form',
                      'onsubmit' => 'return false;',
                  ]) ?>
                  <div class="card shadow" aria-labelledby="failedPasswordAttempt-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="failedPasswordAttempt-form-title"><?= __('View Failed Password Attempt') ?></strong>
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
                        </div>


                      </div>
                    </div>

                    <div class="card-footer border-top">
                      <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
<?php if (!empty($show['editButton'])) : ?>
                        <?= $this->Action->editButton($failedPasswordAttempt->id) ?>
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
