<?php
/**
 * Tagi profil — JeffAdmin űrlapkártya.
 *
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $user
 */
use App\Model\Entity\User;

$this->Html->css('google-button', ['block' => true]);

$connectedGoogle = false;
foreach ($user->social_accounts ?? [] as $account) {
    if (strtolower((string)$account->provider) === 'google') {
        $connectedGoogle = true;
        break;
    }
}
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <?= $this->Form->create($user, ['class' => 'form-horizontal']) ?>
                  <div class="card shadow" aria-labelledby="profile-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="profile-form-title">Profil</strong>
                        <small class="d-block"><?= h(User::displayName($user)) ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
                      </ul>
                      <?= $this->Action->close(null, ['controller' => 'Registrations']) ?>
                    </div>

                    <div class="card-body form-card-body">
                      <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <?= $this->element('profile_photo', ['user' => $user]) ?>
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
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <span class="form-control-label fw-bold">Google:</span>
                            </div>
                            <div class="col-12 col-md-9">
                              <?php if ($connectedGoogle) : ?>
                                <p class="mb-0">Google-fiók csatlakoztatva</p>
                              <?php else : ?>
                                <?= $this->element('google_button', [
                                    'url' => $this->Url->build('/link-social/google'),
                                    'label' => 'Google-fiók csatlakoztatása',
                                ]) ?>
                              <?php endif; ?>
                            </div>
                          </div>
                          <?= $this->element('mail_preferences', ['user' => $user]) ?>
                        </div>
                      </div>
                    </div>

                    <div class="card-footer border-top">
                      <?= $this->element('privacy_accept') ?>
                      <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
                        <?= $this->Form->button(
                            $this->Icon->outline('device-floppy') . ' ' . __('Save'),
                            ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                        ) ?>
                        <?= $this->Action->cancelButton(null, ['controller' => 'Registrations']) ?>
                        <?= $this->Html->link(
                            'Jelszócsere',
                            ['plugin' => 'CakeDC/Users', 'prefix' => false, 'controller' => 'Users', 'action' => 'changePassword'],
                            ['class' => 'btn btn-outline-secondary']
                        ) ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>
                </div>
              </div>
