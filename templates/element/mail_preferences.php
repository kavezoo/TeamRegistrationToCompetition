<?php
/**
 * A saját levél- és telefonos üzenetfajták. A jelölés alapból be van kapcsolva.
 *
 * @var \App\View\AppView $this
 * @var object $user
 * @var bool|null $disabled
 */
use App\Model\Entity\User;

$disabled = !empty($disabled);
$role = (string)$user->get('role');
$choices = User::mailChoices($role);
$flashChoices = User::flashChoices($role);
if ($choices === [] && $flashChoices === []) {
    return;
}
?>
<?php if ($choices !== []) : ?>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <span class="form-control-label fw-bold">E-mailek:</span>
    </div>
    <div class="col-12 col-md-9">
        <p class="mb-2">Beállíthatod, milyen levelet kérsz. A jelölés alapból be van kapcsolva.</p>
        <?php foreach ($choices as $field => $label) : ?>
            <div class="form-check mb-1">
                <?= $this->Form->checkbox($field, [
                    'id' => $field,
                    'class' => 'form-check-input',
                    'checked' => User::wantsMail($user, $field),
                    'hiddenField' => true,
                    'disabled' => $disabled,
                    'value' => '1',
                ]) ?>
                <label class="form-check-label" for="<?= h($field) ?>"><?= h($label) ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php if ($flashChoices !== []) : ?>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <span class="form-control-label fw-bold">Telefon:</span>
    </div>
    <div class="col-12 col-md-9">
        <p class="mb-2">Beállíthatod, milyen üzenetet kérsz a telefonra. A jelölés alapból be van kapcsolva.</p>
        <?php foreach ($flashChoices as $field => $label) : ?>
            <div class="form-check mb-1">
                <?= $this->Form->checkbox($field, [
                    'id' => $field,
                    'class' => 'form-check-input',
                    'checked' => User::wantsFlash($user, $field),
                    'hiddenField' => true,
                    'disabled' => $disabled,
                    'value' => '1',
                ]) ?>
                <label class="form-check-label" for="<?= h($field) ?>"><?= h($label) ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
