<?php
/**
 * Üzenetváltások listája, a kavezoo bake index mintájára.
 *
 * @var \App\View\AppView $this
 * @var iterable<object> $exchanges
 * @var bool $unavailable
 * @var string $kind
 * @var string $pageTitle
 * @var string $pageSubtitle
 * @var string|null $lastRecordId
 * @var int|null $lastRecordPage
 */

use App\Model\Entity\User;
use App\Service\Conversation;
use Cake\Core\Configure;

$show = Configure::read('JeffAdmin');
$showLocal = [];
$showLocal['index'] = [
    'rowCheckbox' => false,
    'rowId' => false,
    'name' => true,
    'visible' => false,
    'pos' => false,
    'created' => true,
    'modified' => false,
    'counts' => false,
    'viewButton' => true,
    'editButton' => false,
    'deleteButton' => false,
    'relatedTables' => true,
    'rowDblClick' => 'view',
];
$show = array_merge($show['index'] ?? [], $showLocal['index']);
$rowDblClick = $show['rowDblClick'] ?? 'view';
if (!in_array($rowDblClick, ['edit', 'view'], true)) {
    $rowDblClick = 'none';
}
$listPage = max(1, (int)$this->request->getQuery('page', 1));
$prefix = (string)$this->request->getParam('prefix');
$other = $kind === Conversation::KIND_PRESIDENTS
    ? ['controller' => 'Broadcasts', 'action' => 'index', 'label' => 'Mindenkinek szóló üzenetváltások']
    : ['controller' => 'Boards', 'action' => 'index', 'label' => 'Elnöki üzenetváltások'];
$showOther = in_array($prefix, ['President', 'Admin'], true);
$this->assign('title', $pageTitle);
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-md-12">
                  <div class="card shadow" aria-labelledby="exchanges-title">
                    <div class="card-header index-card-header">
                      <div class="index-card-header__title">
                        <strong id="exchanges-title"><?= h($pageTitle) ?></strong>
                        <small class="d-block"><?= h($pageSubtitle) ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs index-card-header__tabs" id="indexTabs" role="tablist">
<?php if (!empty($show['relatedTables']) && $showOther) : ?>
                        <li class="nav-item dropdown" role="presentation">
                          <a class="nav-link dropdown-toggle" href="#" id="tab-related-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Related tables') ?></a>
                          <ul class="dropdown-menu" aria-labelledby="tab-related-btn">
                            <li><?= $this->Html->link($other['label'], ['prefix' => $prefix, 'controller' => $other['controller'], 'action' => $other['action']], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link('Személyes üzenetek', ['prefix' => $prefix, 'controller' => 'Messages', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                          </ul>
                        </li>
<?php endif; ?>
                      </ul>
                      <div class="index-card-header__actions">
                        <?= $this->Html->link(
                            $this->Icon->outline('plus') . ' Flash üzenet',
                            ['action' => 'add'],
                            ['class' => 'btn btn-success', 'escape' => false]
                        ) ?>
                      </div>
                    </div>
                    <div class="card-body p-0">
<?php if (!empty($unavailable)) : ?>
                      <p class="p-3 mb-0"><?= h(Conversation::UNAVAILABLE) ?></p>
<?php else : ?>
                      <div class="table-responsive text-nowrap">
                        <table class="table table-data2 table-border table-hover table-striped table-custom-hover mb-0" data-row-dblclick="view">
                          <thead>
                            <tr>
<?php if (!empty($show['name'])) : ?>
                              <th class="string"><?= $this->Paginator->sort('subject', 'Cím') ?></th>
<?php endif; ?>
                              <th class="string">Küldő</th>
                              <th class="string">Üzenet</th>
<?php if (!empty($show['created'])) : ?>
                              <th class="datetime-meta"><?= $this->Paginator->sort('created', 'Küldve') ?></th>
<?php endif; ?>
<?php if (!empty($show['viewButton'])) : ?>
                              <th class="action"><?= __('Actions') ?></th>
<?php endif; ?>
                            </tr>
                          </thead>
                          <tbody class="table-group-divider">
<?php foreach ($exchanges as $exchange) :
    $dblClickUrl = $rowDblClick === 'none'
        ? null
        : $this->Url->build([
            'action' => $rowDblClick,
            $exchange->id,
            '?' => ['listPage' => $listPage],
        ]);
    $rowClass = (isset($lastRecordId) && (string)$lastRecordId === (string)$exchange->id)
        ? ' is-last-touched'
        : '';
    $rowAttrs = ' data-id="' . h((string)$exchange->id) . '"'
        . ' id="row-' . h((string)$exchange->id) . '"';
    if ($dblClickUrl) {
        $rowAttrs .= ' data-dblclick-url="' . h($dblClickUrl) . '"';
    }
    if ($rowClass !== '') {
        $rowAttrs .= ' class="' . trim($rowClass) . '"';
    }
    $excerpt = trim((string)$exchange->body);
    if (mb_strlen($excerpt) > 120) {
        $excerpt = mb_substr($excerpt, 0, 117) . '…';
    }
?>
                            <tr<?= $rowAttrs ?>>
<?php if (!empty($show['name'])) : ?>
                              <td class="string"><?= h($exchange->subject) ?></td>
<?php endif; ?>
                              <td class="string"><?= h($exchange->sender ? User::displayName($exchange->sender) : 'Tag') ?></td>
                              <td class="string"><?= h($excerpt) ?></td>
<?php if (!empty($show['created'])) : ?>
                              <td class="datetime created-modified">
                                <span class="created"><?= $this->Input->formatDatetime($exchange->created) ?></span>
                              </td>
<?php endif; ?>
<?php if (!empty($show['viewButton'])) : ?>
                              <td class="action">
                                <div class="table-data-feature">
                                  <?= $this->Action->view($exchange->id) ?>
                                </div>
                              </td>
<?php endif; ?>
                            </tr>
<?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
<?php endif; ?>
                    </div>
<?php if (empty($unavailable)) : ?>
                    <div class="card-footer border-top d-flex align-items-center justify-content-between">
                      <?= $this->element('JeffAdmin.pagination') ?>
                    </div>
<?php endif; ?>
                  </div>
                </div>
              </div>
