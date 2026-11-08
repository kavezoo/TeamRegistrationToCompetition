                                    <?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
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
                  <?= $this->Form->create($team, [
                      'class' => 'form-horizontal',
                      'id' => 'team-view-form',
                      'onsubmit' => 'return false;',
                  ]) ?>
                  <div class="card shadow" aria-labelledby="team-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="team-form-title"><?= __('View Team') ?></strong>
                        <small class="d-block"><?= h($team->name) ?></small>
                      </div>

                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
<?php if (!empty($show['relatedTables'])) : ?>
                        <li class="nav-item dropdown ms-auto" role="presentation">
                          <a class="nav-link dropdown-toggle" href="#" id="tab-related-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Related tables') ?></a>
                          <ul class="dropdown-menu" aria-labelledby="tab-related-btn">
                            <li><?= $this->Html->link(__('List Registrations'), ['controller' => 'Registrations', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                          </ul>
                        </li>
<?php endif; ?>
                        <li class="nav-item<?= empty($show['relatedTables']) ? ' ms-auto' : '' ?>" role="presentation">
                          <a class="nav-link" id="tab-settings-btn" data-bs-toggle="tab" href="#tab-settings" role="tab" aria-controls="tab-settings" aria-selected="false"><?= __('Settings') ?></a>
                        </li>

                      </ul>

                      <?= $this->Action->close($team->id) ?>
                    </div>

                    <div class="card-body form-card-body">
                      <div class="tab-content">

                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('name', __('Name') . ':', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Input->control('name', ['class' => 'form-control', 'disabled' => true]) ?>
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
                        <?= $this->Action->editButton($team->id) ?>
<?php endif; ?>
<?php if (!empty($show['cancelButton'])) : ?>
                        <?= $this->Action->cancelButton($team->id) ?>
<?php endif; ?>
<?php if (!empty($show['deleteButton'])) : ?>
                        <div class="ms-auto">
                          <?= $this->Action->deleteButton($team->id, [], ['entity' => $team]) ?>
                        </div>
<?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>

<?php if (!empty($show['relatedTables'])) : ?>
                  <div class="card shadow related-card" style="margin-top: 16px;" aria-labelledby="team-related-title">
                    <div class="card-header related-card-header">
                      <div class="related-card-header__title">
                        <span id="team-related-title"><?= __('Related records') ?>: <?= h($team->name) ?></span>
                      </div>

                      <ul class="nav nav-tabs card-header-tabs related-card-header__tabs" id="relatedTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="related-tab-registrations-btn" data-bs-toggle="tab" href="#related-tab-registrations" role="tab" aria-controls="related-tab-registrations" aria-current="page" aria-selected="true"><?= __('Registrations') ?></a>
                        </li>
                      </ul>
                    </div>

                    <div class="card-body p-0">
                      <div class="tab-content">
                        <?php // ========== Related: Registrations (start) ========== ?>
                        <div class="tab-pane fade show active" id="related-tab-registrations" role="tabpanel" aria-labelledby="related-tab-registrations-btn" tabindex="0">
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
<?php if (!empty($relShow['name'])) : ?>
                                  <th class="string"><?= __('User Id') ?></th>
<?php endif; ?>
<?php /*
                                  <th class="string"><?= __('Competition Id') ?></th>
                                  <th class="string"><?= __('Competition Pipe 1') ?></th>
                                  <th class="string"><?= __('Competition Pipe 2') ?></th>
                                  <th class="string"><?= __('Competition Pipe 3') ?></th>
                                  <th class="string"><?= __('Extra Count 1') ?></th>
                                  <th class="string"><?= __('Extra Count 2') ?></th>
                                  <th class="string"><?= __('Extra Count 3') ?></th>
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
<?php foreach ($team->registrations as $registration) : ?>
                                <tr data-id="<?= h((string)$registration->id) ?>">
<?php if (!empty($relShow['rowId'])) : ?>
                                  <td class="integer id-col"><?= h((string)$registration->id) ?></td>
<?php endif; ?>
<?php if (!empty($relShow['name'])) : ?>
                                  <td class="string"><?= h(\App\Model\Entity\User::displayName($registration->user ?? null) ?: (string)$registration->user_id) ?><?= $this->element('registration_absence_note', ['registration' => $registration]) ?></td>
<?php endif; ?>
<?php /*
                                  <td class="string"><?= h($registration->competition_id) ?></td>
                                  <td class="string"><?= h($registration->competition_pipe_1) ?></td>
                                  <td class="string"><?= h($registration->competition_pipe_2) ?></td>
                                  <td class="string"><?= h($registration->competition_pipe_3) ?></td>
                                  <td class="string"><?= h($registration->extra_count_1) ?></td>
                                  <td class="string"><?= h($registration->extra_count_2) ?></td>
                                  <td class="string"><?= h($registration->extra_count_3) ?></td>
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
                      </div>
                    </div>
                  </div>
<?php endif; ?>
                </div>
              </div>
