<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $members
 * @var string $selected
 */
use App\Model\Entity\User;

$this->assign('title', 'Üzenet a telefonra');
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12 col-lg-8">
        <div class="card shadow">
            <div class="card-header"><strong>Flash üzenet a mobilra</strong></div>
            <div class="card-body">
                <p>Az üzenet a telefonos alkalmazásban jelenik meg. Minden tagnak vagy egy kiválasztott tagnak megy.</p>
                <?= $this->Form->create(null) ?>
                <div class="mb-3">
                    <label class="form-label fw-bold" for="user-id">Címzett</label>
                    <select class="form-select" id="user-id" name="user_id">
                        <option value="">Minden tag</option>
                        <?php foreach ($members as $member) : ?>
                            <?php $id = (string)$member->id; ?>
                            <option value="<?= h($id) ?>"<?= $selected === $id ? ' selected' : '' ?>>
                                <?= h(User::displayName($member)) ?>
                                <?= (string)$member->get('role') === User::ROLE_PRESIDENT ? ' (elnök)' : '' ?>
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
                <?= $this->Form->button('Üzenet küldése a telefonra', ['class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
