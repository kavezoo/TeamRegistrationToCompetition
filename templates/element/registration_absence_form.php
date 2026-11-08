<?php
/**
 * Határidő utáni jelzés: a nevezés megmarad, az indok kötelező.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var string $action
 */
$action = $action ?? 'absent';
?>
<?= $this->Form->create(null, [
    'url' => ['action' => $action, $registration->id],
    'class' => 'mt-3',
]) ?>
    <label class="form-label fw-bold" for="absent-reason">Miért nem tudsz jönni?</label>
    <textarea
        class="form-control"
        id="absent-reason"
        name="reason"
        rows="3"
        maxlength="1000"
        required
    ><?= h($registration->absenceReason()) ?></textarea>
    <div class="form-text mb-2">A jelentkezésed megmarad, és a listán ez az indok látszik.</div>
    <?= $this->Form->button('Nem tudok a versenyen részt venni', ['class' => 'btn btn-danger btn-sm']) ?>
<?= $this->Form->end() ?>
