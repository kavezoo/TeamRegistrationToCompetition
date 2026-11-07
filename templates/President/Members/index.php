<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition|null $competition
 * @var array<int, \App\Model\Entity\User> $members
 * @var \Cake\Datasource\Paging\PaginatedInterface $membersPage
 * @var array<string, true> $registered
 * @var array<string, array<int, \App\Model\Entity\RegistrationReminder>> $history
 * @var int $missing
 * @var string|null $lastRecordId
 */
use App\Model\Entity\User;

$this->assign('title', 'Tagok');

$canRemind = $competition !== null && $competition->visible && !$competition->registrationClosed();
$page = (int)$this->request->getQuery('page');
$pageQuery = $page > 1 ? ['page' => $page] : [];
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>Aktív tagok</strong>
                <?php if ($canRemind && $missing > 0) : ?>
                    <?= $this->Form->postLink(
                        'Emlékeztető mindenkinek, aki még nem jelentkezett (' . $missing . ')',
                        ['action' => 'remindMissing', '?' => $pageQuery],
                        [
                            'class' => 'btn btn-sm btn-primary',
                            'data' => ['competition_id' => $competition->id],
                            'confirm' => 'Elküldöd az emlékeztetőt mindenkinek, aki még nem jelentkezett?',
                            'data-swal-title' => 'E-mail küldése',
                            'data-swal-text' => 'Elküldöd az emlékeztetőt mindenkinek, aki még nem jelentkezett?',
                            'data-swal-confirm' => __('Yes'),
                            'data-swal-cancel' => __('No'),
                        ]
                    ) ?>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($canRemind) : ?>
                    <p>A jelentkezés és az emlékeztető ehhez a versenyhez tartozik: <strong><?= h($competition->name) ?></strong></p>
                <?php elseif ($competition === null) : ?>
                    <p>Nincs nyitott verseny, ezért emlékeztetőt nem lehet küldeni.</p>
                <?php endif; ?>

                <?php if ($members === []) : ?>
                    <p class="mb-0">Nincs aktív tag.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-data2 table-border table-hover table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>E-mail</th>
                                    <th>Jelentkezés</th>
                                    <th></th>
                                    <th>Elküldött üzenetek</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                <?php foreach ($members as $member) : ?>
                                    <?php
                                    $joined = isset($registered[(string)$member->id]);
                                    $messages = $history[(string)$member->id] ?? [];
                                    $isLast = isset($lastRecordId) && (string)$lastRecordId === (string)$member->id;
                                    ?>
                                    <tr<?= $isLast ? ' class="is-last-touched"' : '' ?> id="row-<?= h((string)$member->id) ?>" data-id="<?= h((string)$member->id) ?>">
                                        <td>
                                            <?= $this->element('user_name', ['user' => $member]) ?>
                                            <?php if ((string)$member->get('role') === User::ROLE_PRESIDENT) : ?>
                                                <span class="badge text-bg-secondary ms-1">Elnök</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= h($member->email) ?></td>
                                        <td><?= $canRemind ? ($joined ? 'Jelentkezett' : 'Még nem jelentkezett') : '' ?></td>
                                        <td>
                                            <?php if ($canRemind && !$joined && trim((string)$member->email) !== '') : ?>
                                                <?= $this->Form->postLink(
                                                    'E-mail küldése',
                                                    ['action' => 'remind', $member->id, '?' => $pageQuery],
                                                    [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'data' => ['competition_id' => $competition->id],
                                                        'confirm' => 'Elküldöd az emlékeztetőt, hogy még nem jelentkezett a versenyre?',
                                                        'data-swal-title' => 'E-mail küldése',
                                                        'data-swal-text' => 'Elküldöd az emlékeztetőt, hogy még nem jelentkezett a versenyre?',
                                                        'data-swal-confirm' => __('Yes'),
                                                        'data-swal-cancel' => __('No'),
                                                    ]
                                                ) ?>
                                            <?php elseif ($canRemind && !$joined) : ?>
                                                Nincs e-mail címe
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($messages === []) : ?>
                                                Még nem kapott emlékeztetőt.
                                            <?php else : ?>
                                                <?php foreach ($messages as $reminder) : ?>
                                                    <div class="mb-2">
                                                        <strong><?= $this->Input->formatDatetime($reminder->created) ?></strong>
                                                        <div class="small" style="white-space:pre-wrap"><?= h($reminder->message) ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($members !== []) : ?>
                <div class="card-footer border-top d-flex align-items-center justify-content-between">
                    <?php $this->Paginator->setPaginated($membersPage); ?>
                    <?= $this->element('JeffAdmin.pagination') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
