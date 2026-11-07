<?php
/**
 * A saját levélfajták. A jelölés alapból be van kapcsolva.
 *
 * @var \App\View\AppView $this
 * @var object $user
 * @var bool|null $disabled
 */
use App\Model\Entity\User;

$disabled = !empty($disabled);
$choices = User::mailChoices((string)$user->get('role'));
if ($choices === []) {
    return;
}
?>
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
