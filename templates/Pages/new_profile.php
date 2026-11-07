<?php
/**
 * Új jelentkező adatlapja. Csak a név szerkeszthető.
 *
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $user
 * @var bool $profileSaved
 */
use App\Model\Entity\User;

$this->assign('title', 'Saját adatok');
$submitsRequest = (string)($user->get('membership_request') ?? '') !== 'pending';
$explainsApplication = $submitsRequest || !$profileSaved;
$applicationNotice = 'A klubba jelentkezés a Mentés gomb megnyomásával történik. A kérelem a klubelnökhöz kerül, és a jóváhagyásáig várnod kell.';
$confirmSave = $applicationNotice . ' Mented?';
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <?= $this->Form->create($user, [
                      'url' => ['controller' => 'Pages', 'action' => 'profile'],
                      'class' => 'form-horizontal',
                      'data-swal-title' => $explainsApplication ? 'Biztosan?' : null,
                      'data-swal-text' => $explainsApplication ? $confirmSave : null,
                      'data-swal-confirm' => $explainsApplication ? 'Igen' : null,
                      'data-swal-cancel' => $explainsApplication ? 'Nem' : null,
                  ]) ?>
                  <div class="card shadow" aria-labelledby="profile-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="profile-form-title">Saját adatok</strong>
                        <small class="d-block"><?= h(User::displayName($user)) ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
                      </ul>
                    </div>
                    <div class="card-body form-card-body">
                      <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('username', 'Felhasználónév:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('username', ['class' => 'form-control', 'readonly' => true, 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('email', 'E-mail:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('email', ['type' => 'email', 'class' => 'form-control', 'readonly' => true, 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('first_name', 'Vezetéknév:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('first_name', ['class' => 'form-control', 'autofocus' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('last_name', 'Keresztnév:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('last_name', ['class' => 'form-control']) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-9 offset-md-2">
                              <?= $this->element('swap_names') ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer border-top">
                      <?php if ($explainsApplication) : ?>
                        <p class="alert alert-warning mb-3 offset-md-2"><?= h($applicationNotice) ?></p>
                      <?php endif; ?>
                      <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
                        <?= $this->Form->button(
                            $this->Icon->outline('device-floppy') . ' ' . __('Save'),
                            ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                        ) ?>
                        <?php if ($profileSaved) : ?>
                          <?= $this->Html->link('Mégse', '/', ['class' => 'btn btn-outline-secondary']) ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>
                  <div class="mt-3">
                    <?= $this->element('delete_own_account') ?>
                  </div>
                </div>
              </div>
