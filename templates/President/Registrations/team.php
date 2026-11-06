<?php
/**
 * Jelentkező alcsapatba sorolása.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var array<int|string, string> $teams
 * @var array<string, mixed>|string $returnUrl
 */
use App\Model\Entity\User;

$this->assign('title', 'Alcsapat');
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <?= $this->Form->create($registration, ['class' => 'form-horizontal']) ?>
                  <div class="card shadow" aria-labelledby="team-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="team-form-title"><?= h(User::displayName($registration->user)) ?></strong>
                        <small class="d-block"><?= h((string)$registration->competition->name) ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
                      </ul>
                      <?= $this->Action->close(null, ['action' => 'index']) ?>
                    </div>
                    <div class="card-body form-card-body">
                      <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <span class="form-control-label fw-bold">Jelentkező:</span>
                            </div>
                            <div class="col-12 col-md-9 d-flex align-items-center">
                              <?= $this->element('user_name', ['user' => $registration->user]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('team_id', 'Alcsapat:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?php if ($teams === []) : ?>
                                <p class="mb-0">Még nincs alcsapat. <?= $this->Html->link('Alcsapat létrehozása', ['controller' => 'Teams', 'action' => 'add']) ?></p>
                              <?php else : ?>
                                <?= $this->Form->control('team_id', [
                                    'label' => false,
                                    'options' => $teams,
                                    'empty' => 'Nincs besorolva',
                                    'class' => 'form-select',
                                ]) ?>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer border-top">
                      <div class="d-flex align-items-center flex-wrap gap-2 offset-md-2">
                        <?= $this->Form->button(
                            $this->Icon->outline('device-floppy') . ' ' . __('Save'),
                            ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false, 'disabled' => $teams === []]
                        ) ?>
                        <?= $this->Html->link('Mégse', $returnUrl, ['class' => 'btn btn-outline-secondary']) ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>
                </div>
              </div>
