<?php
/**
 * Egy üzenetváltás üzenetei, és válasz, ami flashként megy ki.
 *
 * @var \App\View\AppView $this
 * @var array<string, mixed> $thread
 * @var string $kind
 */

use Cake\Core\Configure;

$show = Configure::read('JeffAdmin');
$showLocal = [];
$showLocal['view'] = [
    'editButton' => false,
    'cancelButton' => true,
    'deleteButton' => false,
    'relatedTables' => false,
];
$show = array_merge($show['view'] ?? [], $showLocal['view']);
$posts = $thread['posts'] ?? [];
$this->assign('title', (string)$thread['subject']);
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-12 col-xxl-11">
                  <div class="card shadow" aria-labelledby="exchange-view-title">
                    <div class="card-header form-card-header">
                      <div class="form-card-header__title">
                        <strong id="exchange-view-title"><?= h((string)$thread['subject']) ?></strong>
                        <small class="d-block"><?= h((string)$thread['peer_name']) ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs form-card-header__tabs" id="formTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" href="#tab-basic" role="tab" aria-controls="tab-basic" aria-current="page" aria-selected="true">Üzenetek</a>
                        </li>
                        <li class="nav-item ms-auto" role="presentation">
                          <a class="nav-link" id="tab-reply-btn" data-bs-toggle="tab" href="#tab-reply" role="tab" aria-controls="tab-reply" aria-selected="false">Válasz</a>
                        </li>
                      </ul>
                      <?= $this->Action->close($thread['id']) ?>
                    </div>
                    <div class="card-body form-card-body">
                      <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-btn" tabindex="0">
<?php foreach ($posts as $post) : ?>
                          <div class="border rounded p-3 mb-3<?= !empty($post['mine']) ? ' bg-light' : '' ?>">
                            <div class="d-flex justify-content-between">
                              <strong><?= h((string)$post['sender_name']) ?></strong>
                              <small><?= h((string)$post['created']) ?></small>
                            </div>
                            <div class="mt-2" style="white-space: pre-wrap;"><?= h((string)$post['body']) ?></div>
                          </div>
<?php endforeach; ?>
                        </div>
                        <div class="tab-pane fade" id="tab-reply" role="tabpanel" aria-labelledby="tab-reply-btn" tabindex="0">
                          <?= $this->Form->create(null) ?>
                          <div class="row mb-3">
                            <div class="col-12 col-md-2 text-start text-md-end">
                              <?= $this->Form->label('body', 'Válasz:', ['class' => 'form-control-label fw-bold']) ?>
                            </div>
                            <div class="col-12 col-md-9">
                              <?= $this->Form->textarea('body', [
                                  'class' => 'form-control',
                                  'rows' => 5,
                                  'required' => true,
                              ]) ?>
                              <small class="text-muted">A válasz flash üzenetként megy ki a <?= h((string)$thread['peer_name']) ?> telefonjára.</small>
                            </div>
                          </div>
                          <div class="offset-md-2">
                            <?= $this->Form->button(
                                $this->Icon->outline('device-floppy') . ' Válasz küldése',
                                ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                            ) ?>
                          </div>
                          <?= $this->Form->end() ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
