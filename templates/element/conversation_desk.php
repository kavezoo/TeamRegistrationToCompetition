<?php
/**
 * Új üzenet és a kiküldött, illetve kapott beszélgetések.
 *
 * @var \App\View\AppView $this
 * @var iterable<object> $people
 * @var list<array<string, mixed>> $threads
 * @var string $selected
 */
use App\Model\Entity\User;

$prefix = (string)$this->request->getParam('prefix');
$selected = (string)($selected ?? '');
$threads = $threads ?? [];
$canBroadcast = in_array($prefix, ['President', 'Admin'], true);
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12 col-lg-7">
        <div class="card shadow">
            <div class="card-header"><strong>Üzenet a telefonra</strong></div>
            <div class="card-body">
                <p>Az üzenet a címzett telefonján jelenik meg. Koppintásra megnyílik, és tud rá válaszolni.</p>
                <?= $this->Form->create(null) ?>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="user-id">Címzett</label>
                    <select class="form-select" id="user-id" name="user_id"<?= $canBroadcast ? '' : ' required' ?>>
                        <?php if ($canBroadcast) : ?>
                            <option value="">Minden tag és elnök</option>
                            <option value="presidents"<?= $selected === 'presidents' ? ' selected' : '' ?>>Csak a klubelnökök</option>
                        <?php else : ?>
                            <option value=""<?= $selected === '' ? ' selected' : '' ?>>Válassz címzettet</option>
                        <?php endif; ?>
                        <?php foreach ($people as $person) : ?>
                            <?php
                            $id = (string)$person->id;
                            $role = (string)$person->get('role');
                            $mark = match ($role) {
                                User::ROLE_PRESIDENT => ' (elnök)',
                                User::ROLE_ADMIN => ' (admin)',
                                default => '',
                            };
                            ?>
                            <option value="<?= h($id) ?>"<?= $selected === $id ? ' selected' : '' ?>>
                                <?= h(User::displayName($person) . $mark) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <?= $this->Form->control('subject', [
                        'label' => 'Cím',
                        'class' => 'form-control',
                        'maxlength' => 180,
                        'required' => true,
                    ]) ?>
                </div>
                <div class="mb-3">
                    <?= $this->Form->control('body', [
                        'type' => 'textarea',
                        'label' => 'Üzenet',
                        'class' => 'form-control',
                        'rows' => 6,
                        'required' => true,
                    ]) ?>
                </div>
                <?= $this->Form->button('Üzenet küldése', ['class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow">
            <div class="card-header"><strong>Üzenetek</strong></div>
            <div class="card-body">
                <?php if ($threads === []) : ?>
                    <p class="mb-0">Még nincs kiküldött vagy kapott üzenet.</p>
                <?php else : ?>
                    <div class="list-group">
                        <?php foreach ($threads as $row) : ?>
                            <a class="list-group-item list-group-item-action" href="<?= $this->Url->build(['prefix' => $prefix, 'controller' => 'Messages', 'action' => 'view', $row['id']]) ?>">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="fw-bold fs-5"><?= h((string)$row['peer_name']) ?></div>
                                    <small class="text-nowrap"><?= h((string)$row['last_at']) ?></small>
                                </div>
                                <div class="fw-bold"><?= h((string)$row['subject']) ?></div>
                                <div class="fw-normal mt-1" style="white-space: pre-wrap;"><?= h((string)$row['last_body']) ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
