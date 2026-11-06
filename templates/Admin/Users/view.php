                                                                                                                                                                                                                                                                                                                            <?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var array<string, string> $roles
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
                  <?= $this->Form->create($user, [
                      'class' => 'form-horizontal',
                      'id' => 'user-view-form',
                      'onsubmit' => 'return false;',
                  ]) ?>
                  <div class="card shadow" aria-labelledby="user-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="user-form-title"><?= __('View User') ?></strong>
                        <small class="d-block"><?= h($user->full_name) ?></small>
                      </div>

                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
<?php if (!empty($show['relatedTables'])) : ?>
                        <li class="nav-item dropdown ms-auto" role="presentation">
                          <a class="nav-link dropdown-toggle" href="#" id="tab-related-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Related tables') ?></a>
                          <ul class="dropdown-menu" aria-labelledby="tab-related-btn">
                            <li><?= $this->Html->link(__('List FailedPasswordAttempts'), ['controller' => 'FailedPasswordAttempts', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List Registrations'), ['controller' => 'Registrations', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List SocialAccounts'), ['controller' => 'SocialAccounts', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                          </ul>
                        </li>
<?php endif; ?>

                      </ul>

                      <?= $this->Action->close($user->id) ?>
                    </div>

                    <div class="card-body form-card-body">
                      <div class="tab-content">

                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('username', __('Username') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('username', ['class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('email', __('Email') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('email', ['type' => 'email', 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('last_name', 'Vezetéknév:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('last_name', ['class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('first_name', 'Keresztnév:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('first_name', ['class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('token_expires', __('Token Expires') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->datetime('token_expires', ['disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('api_token', __('Api Token') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('api_token', ['class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('activation_date', __('Activation Date') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->datetime('activation_date', ['disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('secret', __('Secret') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('secret', ['class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3 align-items-center">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('secret_verified', __('Secret Verified') . ':', ['class' => 'form-control-label fw-bold mb-0']) ?>
                            </div>
                            <div class="col-12 col-md-9 d-flex align-items-center flex-wrap pt-1">
                              <div class="form-check form-check-inline mb-0">
                                <?= $this->Form->checkbox('secret_verified', ['class' => 'form-check-input', 'id' => 'secret_verified', 'disabled' => true]) ?>
                                <?= $this->Form->label('secret_verified', __('Secret Verified'), ['class' => 'form-check-label']) ?>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('tos_date', __('Tos Date') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->datetime('tos_date', ['disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3 align-items-center">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('active', __('Active') . ':', ['class' => 'form-control-label fw-bold mb-0']) ?>
                            </div>
                            <div class="col-12 col-md-9 d-flex align-items-center flex-wrap pt-1">
                              <div class="form-check form-check-inline mb-0">
                                <?= $this->Form->checkbox('active', ['class' => 'form-check-input', 'id' => 'active', 'disabled' => true]) ?>
                                <?= $this->Form->label('active', __('Active'), ['class' => 'form-check-label']) ?>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3 align-items-center">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('is_superuser', __('Is Superuser') . ':', ['class' => 'form-control-label fw-bold mb-0']) ?>
                            </div>
                            <div class="col-12 col-md-9 d-flex align-items-center flex-wrap pt-1">
                              <div class="form-check form-check-inline mb-0">
                                <?= $this->Form->checkbox('is_superuser', ['class' => 'form-check-input', 'id' => 'is_superuser', 'disabled' => true]) ?>
                                <?= $this->Form->label('is_superuser', __('Is Superuser'), ['class' => 'form-check-label']) ?>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('role', __('Role') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Form->control('role', [
                                  'type' => 'select',
                                  'options' => $roles,
                                  'label' => false,
                                  'class' => 'form-select',
                                  'disabled' => true,
                              ]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('additional_data', __('Additional Data') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('additional_data', ['type' => 'textarea', 'rows' => 4, 'class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('last_login', __('Last Login') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->datetime('last_login', ['disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('lockout_time', __('Lockout Time') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->datetime('lockout_time', ['disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('login_token', __('Login Token') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('login_token', ['class' => 'form-control', 'disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('login_token_date', __('Login Token Date') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->datetime('login_token_date', ['disabled' => true]) ?>
                            </div>
                          </div>
                          <div class="row mb-3 align-items-center">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('token_send_requested', __('Token Send Requested') . ':', ['class' => 'form-control-label fw-bold mb-0']) ?>
                            </div>
                            <div class="col-12 col-md-9 d-flex align-items-center flex-wrap pt-1">
                              <div class="form-check form-check-inline mb-0">
                                <?= $this->Form->checkbox('token_send_requested', ['class' => 'form-check-input', 'id' => 'token_send_requested', 'disabled' => true]) ?>
                                <?= $this->Form->label('token_send_requested', __('Token Send Requested'), ['class' => 'form-check-label']) ?>
                              </div>
                            </div>
                          </div>
                        </div>


                      </div>
                    </div>

                    <div class="card-footer border-top">
                      <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
<?php if (!empty($show['editButton'])) : ?>
                        <?= $this->Action->editButton($user->id) ?>
<?php endif; ?>
<?php if (!empty($show['cancelButton'])) : ?>
                        <?= $this->Action->cancelButton($user->id) ?>
<?php endif; ?>
<?php if (!empty($show['deleteButton'])) : ?>
                        <div class="ms-auto">
                          <?= $this->Action->deleteButton($user->id, [], ['entity' => $user]) ?>
                        </div>
<?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>

<?php if (!empty($show['relatedTables'])) : ?>
                  <div class="card shadow related-card" style="margin-top: 16px;" aria-labelledby="user-related-title">
                    <div class="card-header related-card-header">
                      <div class="related-card-header__title">
                        <span id="user-related-title"><?= __('Related records') ?>: <?= h($user->full_name) ?></span>
                      </div>

                      <ul class="nav nav-tabs card-header-tabs related-card-header__tabs" id="relatedTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="related-tab-failed_password_attempts-btn" data-bs-toggle="tab" href="#related-tab-failed_password_attempts" role="tab" aria-controls="related-tab-failed_password_attempts" aria-current="page" aria-selected="true"><?= __('FailedPasswordAttempts') ?></a>
                        </li>
                        <li class="nav-item" role="presentation">
                          <a class="nav-link" id="related-tab-registrations-btn" data-bs-toggle="tab" href="#related-tab-registrations" role="tab" aria-controls="related-tab-registrations" aria-selected="false"><?= __('Registrations') ?></a>
                        </li>
                        <li class="nav-item" role="presentation">
                          <a class="nav-link" id="related-tab-social_accounts-btn" data-bs-toggle="tab" href="#related-tab-social_accounts" role="tab" aria-controls="related-tab-social_accounts" aria-selected="false"><?= __('SocialAccounts') ?></a>
                        </li>
                      </ul>
                    </div>

                    <div class="card-body p-0">
                      <div class="tab-content">
                        <?php // ========== Related: FailedPasswordAttempts (start) ========== ?>
                        <div class="tab-pane fade show active" id="related-tab-failed_password_attempts" role="tabpanel" aria-labelledby="related-tab-failed_password_attempts-btn" tabindex="0">
                          <?php
                          $relShow = array_merge($relatedShow, $relatedLocal['FailedPasswordAttempts'] ?? []);
                          ?>
                          <div class="table-responsive text-nowrap">
                            <table class="table table-data2 table-border table-hover table-striped table-custom-hover mb-0">
                              <thead>
                                <tr>
<?php if (!empty($relShow['rowId'])) : ?>
                                  <th class="integer id-col"><?= __('Id') ?></th>
<?php endif; ?>
<?php if (!empty($relShow['created']) || !empty($relShow['modified'])) : ?>
                                  <th class="datetime-meta">
<?php     if (!empty($relShow['created'])) : ?>
                                    <?= __('Created') ?>
<?php     endif; ?>
<?php     if (!empty($relShow['created']) && !empty($relShow['modified'])) : ?>
                                    <br>
<?php     endif; ?>
<?php     if (!empty($relShow['modified'])) : ?>
                                    <?= __('Modified') ?>
<?php     endif; ?>
                                  </th>
<?php endif; ?>
<?php if (!empty($relShow['viewButton']) || !empty($relShow['editButton']) || !empty($relShow['deleteButton'])) : ?>
                                  <th class="action"><?= __('Actions') ?></th>
<?php endif; ?>
                                </tr>
                              </thead>
                              <tbody class="table-group-divider">
<?php foreach ($user->failed_password_attempts as $failedPasswordAttempt) : ?>
                                <tr data-id="<?= h((string)$failedPasswordAttempt->id) ?>">
<?php if (!empty($relShow['rowId'])) : ?>
                                  <td class="integer id-col"><?= h((string)$failedPasswordAttempt->id) ?></td>
<?php endif; ?>
<?php if (!empty($relShow['created']) || !empty($relShow['modified'])) : ?>
                                  <td class="datetime created-modified">
<?php     if (!empty($relShow['created'])) : ?>
                                    <span class="created"><?= $this->Input->formatDatetime($failedPasswordAttempt->created) ?></span>
<?php     endif; ?>
<?php     if (!empty($relShow['created']) && !empty($relShow['modified'])) : ?>
                                    <br>
<?php     endif; ?>
<?php     if (!empty($relShow['modified'])) : ?>
                                    <span class="modified"><?= $this->Input->formatDatetime($failedPasswordAttempt->modified) ?></span>
<?php     endif; ?>
                                  </td>
<?php endif; ?>
<?php if (!empty($relShow['viewButton']) || !empty($relShow['editButton']) || !empty($relShow['deleteButton'])) : ?>
                                  <td class="action">
                                    <div class="table-data-feature">
<?php         if (!empty($relShow['viewButton'])) : ?>
                                      <?= $this->Action->view($failedPasswordAttempt->id, ['controller' => 'FailedPasswordAttempts']) ?>
<?php         endif; ?>
<?php         if (!empty($relShow['editButton'])) : ?>
                                      <?= $this->Action->edit($failedPasswordAttempt->id, ['controller' => 'FailedPasswordAttempts']) ?>
<?php         endif; ?>
<?php         if (!empty($relShow['deleteButton'])) : ?>
                                      <?= $this->Action->delete($failedPasswordAttempt->id, ['controller' => 'FailedPasswordAttempts'], [
                                          'entity' => $failedPasswordAttempt,
                                          'checkCounts' => ($relShow['deleteGuardByCounts'] ?? true) !== false,
                                      ]) ?>
<?php         endif; ?>
                                    </div>
                                  </td>
<?php endif; ?>
                                </tr>
<?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <?php // ========== Related: FailedPasswordAttempts (end) ========== ?>
                        <?php // ========== Related: Registrations (start) ========== ?>
                        <div class="tab-pane fade" id="related-tab-registrations" role="tabpanel" aria-labelledby="related-tab-registrations-btn" tabindex="0">
                          <?php
                          $relShow = array_merge($relatedShow, $relatedLocal['Registrations'] ?? []);
                          ?>
                          <div class="table-responsive text-nowrap">
                            <table class="table table-data2 table-border table-hover table-striped table-custom-hover mb-0">
                              <thead>
                                <tr>
<?php if (!empty($relShow['rowId'])) : ?>
                                  <th class="integer id-col"><?= __('Id') ?></th>
<?php endif; ?>
                                  <th class="string"><?= __('Team') ?></th>
<?php /*
                                  <th class="string"><?= __('Team Id') ?></th>
                                  <th class="string"><?= __('Competition Id') ?></th>
                                  <th class="string"><?= __('Competition Pipe 1') ?></th>
                                  <th class="string"><?= __('Competition Pipe 2') ?></th>
                                  <th class="string"><?= __('Competition Pipe 3') ?></th>
                                  <th class="string"><?= __('Extra Count 1') ?></th>
                                  <th class="string"><?= __('Extra Count 2') ?></th>
                                  <th class="string"><?= __('Extra Count 3') ?></th>
                                  <th class="string"><?= __('Comment') ?></th>
*/ ?>
<?php if (!empty($relShow['visible'])) : ?>
                                  <th class="boolean"><?= __('Visible') ?></th>
<?php endif; ?>
<?php if (!empty($relShow['pos'])) : ?>
                                  <th class="integer pos"><?= __('Pos') ?></th>
<?php endif; ?>
<?php if (!empty($relShow['created']) || !empty($relShow['modified'])) : ?>
                                  <th class="datetime-meta">
<?php     if (!empty($relShow['created'])) : ?>
                                    <?= __('Created') ?>
<?php     endif; ?>
<?php     if (!empty($relShow['created']) && !empty($relShow['modified'])) : ?>
                                    <br>
<?php     endif; ?>
<?php     if (!empty($relShow['modified'])) : ?>
                                    <?= __('Modified') ?>
<?php     endif; ?>
                                  </th>
<?php endif; ?>
<?php if (!empty($relShow['viewButton']) || !empty($relShow['editButton']) || !empty($relShow['deleteButton'])) : ?>
                                  <th class="action"><?= __('Actions') ?></th>
<?php endif; ?>
                                </tr>
                              </thead>
                              <tbody class="table-group-divider">
<?php foreach ($user->registrations as $registration) : ?>
                                <tr data-id="<?= h((string)$registration->id) ?>">
<?php if (!empty($relShow['rowId'])) : ?>
                                  <td class="integer id-col"><?= h((string)$registration->id) ?></td>
<?php endif; ?>
                                  <td class="string">
                                    <?= $registration->hasValue('team')
                                        ? $this->Html->link(
                                            h((string)$registration->team->name)
                                                . $this->Icon->outline('link-chain', 'record-link__icon'),
                                            ['controller' => 'Teams', 'action' => 'view', $registration->team->id],
                                            [
                                                'class' => 'text-decoration-none text-dark fw-bold record-link',
                                                'escape' => false,
                                                'data-bs-toggle' => 'tooltip',
                                                'data-bs-title' => __('View {0}: {1}', __('Team'), (string)$registration->team->name),
                                            ]
                                        )
                                        : '' ?>
                                  </td>
<?php /*
                                  <td class="string"><?= h($registration->team_id) ?></td>
                                  <td class="string"><?= h($registration->competition_id) ?></td>
                                  <td class="string"><?= h($registration->competition_pipe_1) ?></td>
                                  <td class="string"><?= h($registration->competition_pipe_2) ?></td>
                                  <td class="string"><?= h($registration->competition_pipe_3) ?></td>
                                  <td class="string"><?= h($registration->extra_count_1) ?></td>
                                  <td class="string"><?= h($registration->extra_count_2) ?></td>
                                  <td class="string"><?= h($registration->extra_count_3) ?></td>
                                  <td class="string"><?= h($registration->comment) ?></td>
*/ ?>
<?php if (!empty($relShow['visible'])) : ?>
                                  <td class="boolean visible"><?= $this->Icon->boolean($registration->visible) ?></td>
<?php endif; ?>
<?php if (!empty($relShow['pos'])) : ?>
                                  <td class="integer pos"><?= $registration->pos === null ? '' : h((string)$registration->pos) ?></td>
<?php endif; ?>
<?php if (!empty($relShow['created']) || !empty($relShow['modified'])) : ?>
                                  <td class="datetime created-modified">
<?php     if (!empty($relShow['created'])) : ?>
                                    <span class="created"><?= $this->Input->formatDatetime($registration->created) ?></span>
<?php     endif; ?>
<?php     if (!empty($relShow['created']) && !empty($relShow['modified'])) : ?>
                                    <br>
<?php     endif; ?>
<?php     if (!empty($relShow['modified'])) : ?>
                                    <span class="modified"><?= $this->Input->formatDatetime($registration->modified) ?></span>
<?php     endif; ?>
                                  </td>
<?php endif; ?>
<?php if (!empty($relShow['viewButton']) || !empty($relShow['editButton']) || !empty($relShow['deleteButton'])) : ?>
                                  <td class="action">
                                    <div class="table-data-feature">
<?php         if (!empty($relShow['viewButton'])) : ?>
                                      <?= $this->Action->view($registration->id, ['controller' => 'Registrations']) ?>
<?php         endif; ?>
<?php         if (!empty($relShow['editButton'])) : ?>
                                      <?= $this->Action->edit($registration->id, ['controller' => 'Registrations']) ?>
<?php         endif; ?>
<?php         if (!empty($relShow['deleteButton'])) : ?>
                                      <?= $this->Action->delete($registration->id, ['controller' => 'Registrations'], [
                                          'entity' => $registration,
                                          'checkCounts' => ($relShow['deleteGuardByCounts'] ?? true) !== false,
                                      ]) ?>
<?php         endif; ?>
                                    </div>
                                  </td>
<?php endif; ?>
                                </tr>
<?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <?php // ========== Related: Registrations (end) ========== ?>
                        <?php // ========== Related: SocialAccounts (start) ========== ?>
                        <div class="tab-pane fade" id="related-tab-social_accounts" role="tabpanel" aria-labelledby="related-tab-social_accounts-btn" tabindex="0">
                          <?php
                          $relShow = array_merge($relatedShow, $relatedLocal['SocialAccounts'] ?? []);
                          ?>
                          <div class="table-responsive text-nowrap">
                            <table class="table table-data2 table-border table-hover table-striped table-custom-hover mb-0">
                              <thead>
                                <tr>
<?php if (!empty($relShow['rowId'])) : ?>
                                  <th class="integer id-col"><?= __('Id') ?></th>
<?php endif; ?>
<?php if (!empty($relShow['name'])) : ?>
                                  <th class="string"><?= __('Provider') ?></th>
<?php endif; ?>
<?php /*
                                  <th class="string"><?= __('Username') ?></th>
                                  <th class="string"><?= __('Reference') ?></th>
                                  <th class="string"><?= __('Avatar') ?></th>
                                  <th class="string"><?= __('Description') ?></th>
                                  <th class="string"><?= __('Link') ?></th>
                                  <th class="string"><?= __('Token Secret') ?></th>
                                  <th class="string"><?= __('Token Expires') ?></th>
                                  <th class="string"><?= __('Active') ?></th>
                                  <th class="string"><?= __('Data') ?></th>
*/ ?>
<?php if (!empty($relShow['created']) || !empty($relShow['modified'])) : ?>
                                  <th class="datetime-meta">
<?php     if (!empty($relShow['created'])) : ?>
                                    <?= __('Created') ?>
<?php     endif; ?>
<?php     if (!empty($relShow['created']) && !empty($relShow['modified'])) : ?>
                                    <br>
<?php     endif; ?>
<?php     if (!empty($relShow['modified'])) : ?>
                                    <?= __('Modified') ?>
<?php     endif; ?>
                                  </th>
<?php endif; ?>
<?php if (!empty($relShow['viewButton']) || !empty($relShow['editButton']) || !empty($relShow['deleteButton'])) : ?>
                                  <th class="action"><?= __('Actions') ?></th>
<?php endif; ?>
                                </tr>
                              </thead>
                              <tbody class="table-group-divider">
<?php foreach ($user->social_accounts as $socialAccount) : ?>
                                <tr data-id="<?= h((string)$socialAccount->id) ?>">
<?php if (!empty($relShow['rowId'])) : ?>
                                  <td class="integer id-col"><?= h((string)$socialAccount->id) ?></td>
<?php endif; ?>
<?php if (!empty($relShow['name'])) : ?>
                                  <td class="string"><?= h($socialAccount->provider) ?></td>
<?php endif; ?>
<?php /*
                                  <td class="string"><?= h($socialAccount->username) ?></td>
                                  <td class="string"><?= h($socialAccount->reference) ?></td>
                                  <td class="string"><?= h($socialAccount->avatar) ?></td>
                                  <td class="string"><?= h($socialAccount->description) ?></td>
                                  <td class="string"><?= h($socialAccount->link) ?></td>
                                  <td class="string"><?= h($socialAccount->token_secret) ?></td>
                                  <td class="string"><?= h($socialAccount->token_expires) ?></td>
                                  <td class="string"><?= h($socialAccount->active) ?></td>
                                  <td class="string"><?= h($socialAccount->data) ?></td>
*/ ?>
<?php if (!empty($relShow['created']) || !empty($relShow['modified'])) : ?>
                                  <td class="datetime created-modified">
<?php     if (!empty($relShow['created'])) : ?>
                                    <span class="created"><?= $this->Input->formatDatetime($socialAccount->created) ?></span>
<?php     endif; ?>
<?php     if (!empty($relShow['created']) && !empty($relShow['modified'])) : ?>
                                    <br>
<?php     endif; ?>
<?php     if (!empty($relShow['modified'])) : ?>
                                    <span class="modified"><?= $this->Input->formatDatetime($socialAccount->modified) ?></span>
<?php     endif; ?>
                                  </td>
<?php endif; ?>
<?php if (!empty($relShow['viewButton']) || !empty($relShow['editButton']) || !empty($relShow['deleteButton'])) : ?>
                                  <td class="action">
                                    <div class="table-data-feature">
<?php         if (!empty($relShow['viewButton'])) : ?>
                                      <?= $this->Action->view($socialAccount->id, ['controller' => 'SocialAccounts']) ?>
<?php         endif; ?>
<?php         if (!empty($relShow['editButton'])) : ?>
                                      <?= $this->Action->edit($socialAccount->id, ['controller' => 'SocialAccounts']) ?>
<?php         endif; ?>
<?php         if (!empty($relShow['deleteButton'])) : ?>
                                      <?= $this->Action->delete($socialAccount->id, ['controller' => 'SocialAccounts'], [
                                          'entity' => $socialAccount,
                                          'checkCounts' => ($relShow['deleteGuardByCounts'] ?? true) !== false,
                                      ]) ?>
<?php         endif; ?>
                                    </div>
                                  </td>
<?php endif; ?>
                                </tr>
<?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <?php // ========== Related: SocialAccounts (end) ========== ?>
                      </div>
                    </div>
                  </div>
<?php endif; ?>
                </div>
              </div>
