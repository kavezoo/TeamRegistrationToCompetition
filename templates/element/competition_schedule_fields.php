<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var bool $disabled
 */
$disabled = !empty($disabled);
$dateOptions = $disabled ? ['disabled' => true] : [];
$map = $competition->mapUrl();
?>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <?= $this->Form->label('date_competition', 'Verseny ideje:', ['class' => 'form-control-label fw-bold']) ?>
    </div>
    <div class="col-12 col-md-9">
        <?= $this->Input->datetime('date_competition', $dateOptions) ?>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <?= $this->Form->label('date_from', 'Jelentkezés kezdete:', ['class' => 'form-control-label fw-bold']) ?>
    </div>
    <div class="col-12 col-md-9">
        <?= $this->Input->date('date_from', $dateOptions) ?>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <?= $this->Form->label('date_deadline', 'Jelentkezési határidő:', ['class' => 'form-control-label fw-bold']) ?>
    </div>
    <div class="col-12 col-md-9">
        <?= $this->Input->date('date_deadline', $dateOptions) ?>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <?= $this->Form->label('map_url', 'Helyszín:', ['class' => 'form-control-label fw-bold']) ?>
    </div>
    <div class="col-12 col-md-9">
        <?= $this->Input->control('map_url', [
            'class' => 'form-control',
            'maxlength' => 2000,
            'placeholder' => 'https://maps.google.com/...',
            'disabled' => $disabled,
        ]) ?>
        <?php if ($map !== null) : ?>
            <div class="mt-2"><?= $this->Html->link('Megnyitás új lapon', $map, ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?></div>
        <?php endif; ?>
    </div>
</div>
