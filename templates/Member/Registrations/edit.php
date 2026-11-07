<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Competition $competition
 */
use App\Model\Entity\Registration;

$this->assign('title', 'Jelentkezés');
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <?= $this->Form->create($registration, [
                      'id' => 'registration-form',
                      'class' => 'form-horizontal',
                  ]) ?>
                  <div class="card shadow" aria-labelledby="registration-form-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="registration-form-title"><?= h($competition->name) ?></strong>
                        <small class="d-block">Jelentkezés</small>
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
                          <?= $this->element('competition_description', ['competition' => $competition]) ?>
                          <?php foreach (Registration::TITLED_COUNTS as $titleField => $countField) : ?>
                            <?php $title = trim((string)$competition->get($titleField)); ?>
                            <?php if ($title === '') {
                                continue;
                            } ?>
                            <div class="row mb-3">
                              <div class="col-12 col-md-2 text-start text-md-end">
                                <?= $this->Form->label($countField, $title . ':', ['class' => 'form-control-label fw-bold']) ?>
                              </div>
                              <div class="col-12 col-md-9">
                                <?= $this->Input->control($countField, [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => 'form-control',
                                    'data-number-spinner' => true,
                                    'data-integer' => '1',
                                    'inputmode' => 'numeric',
                                    'min' => '0',
                                    'max' => '1000',
                                    'step' => '1',
                                    'autocomplete' => 'off',
                                    'value' => (int)($registration->get($countField) ?? 0),
                                ]) ?>
                              </div>
                            </div>
                          <?php endforeach; ?>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('comment', 'Megjegyzés:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Form->control('comment', [
                                  'type' => 'textarea',
                                  'label' => false,
                                  'class' => 'form-control',
                                  'rows' => 4,
                                  'maxlength' => 1000,
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
                        <?= $this->Html->link('Mégse', ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php if (!$competition->registrationClosed()) : ?>
                        <div class="ms-auto">
                          <button type="submit" class="btn btn-danger" form="registration-delete" onclick="return confirm(<?= h(json_encode('Biztosan törlöd a jelentkezést?')) ?>);"><?= $this->Icon->outline('trash') ?> <?= __('Delete') ?></button>
                        </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>
                  <?php if (!$competition->registrationClosed()) : ?>
                  <?= $this->Form->create(null, [
                      'id' => 'registration-delete',
                      'url' => ['action' => 'delete', $registration->id],
                  ]) ?>
                  <?= $this->Form->end() ?>
                  <?php endif; ?>
                </div>
              </div>
