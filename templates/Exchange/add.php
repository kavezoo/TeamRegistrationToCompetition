<?php
/**
 * Új flash üzenet a lista címzettkörének.
 *
 * @var \App\View\AppView $this
 * @var string $kind
 * @var string $pageTitle
 */

use App\Service\Conversation;
use Cake\Core\Configure;

$show = Configure::read('JeffAdmin');
$showLocal = [];
$showLocal['add'] = [
    'saveButton' => true,
    'cancelButton' => true,
    'relatedTables' => false,
];
$show = array_merge($show['add'] ?? [], $showLocal['add']);
$hint = $kind === Conversation::KIND_PRESIDENTS
    ? 'Csak a klubelnökök telefonjára megy ki.'
    : 'Minden tag és elnök telefonjára megy ki.';
$this->assign('title', $pageTitle);
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <?= $this->Form->create(null, ['class' => 'form-horizontal']) ?>
                  <div class="card shadow" aria-labelledby="exchange-add-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="exchange-add-title"><?= h($pageTitle) ?></strong>
                        <small class="d-block"><?= h($hint) ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true"><?= __('Basic data') ?></a>
                        </li>
                      </ul>
                      <?= $this->Action->close() ?>
                    </div>
                    <div class="card-body form-card-body">
                      <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('subject', 'Cím:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Form->control('subject', [
                                  'label' => false,
                                  'class' => 'form-control',
                                  'maxlength' => 180,
                                  'required' => true,
                                  'autofocus' => true,
                              ]) ?>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('body', 'Üzenet:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Form->control('body', [
                                  'type' => 'textarea',
                                  'label' => false,
                                  'class' => 'form-control',
                                  'rows' => 6,
                                  'required' => true,
                              ]) ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer border-top">
                      <div class="offset-md-2">
<?php if (!empty($show['saveButton'])) : ?>
                        <?= $this->Form->button(
                            $this->Icon->outline('device-floppy') . ' Küldés a telefonra',
                            ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                        ) ?>
<?php endif; ?>
<?php if (!empty($show['cancelButton'])) : ?>
                        <?= $this->Action->cancelButton() ?>
<?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?= $this->Form->end() ?>
                </div>
              </div>
