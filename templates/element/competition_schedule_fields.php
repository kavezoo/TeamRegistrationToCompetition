<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var bool $disabled
 */
$disabled = !empty($disabled);
$dateOptions = $disabled ? ['disabled' => true] : [];
$map = $competition->mapUrl();

// A Cake dátum szövegesen 2026. 11. 15. A naptár ebből csak az évet olvassa, és január 1-jét ír be.
// A mentett értéket Y-m-d alakban adjuk át. Üres mező (új verseny) üres marad.
$savedDate = static function (string $field, string $pattern) use ($competition): ?string {
    $value = $competition->get($field);
    if ($value instanceof \DateTimeInterface || $value instanceof \Cake\Chronos\ChronosDate) {
        return $value->format($pattern);
    }
    if (!is_string($value) || trim($value) === '') {
        return null;
    }
    try {
        return (new \DateTimeImmutable(trim($value)))->format($pattern);
    } catch (\Exception) {
        return null;
    }
};
$dateField = static function (string $field, string $pattern) use ($dateOptions, $savedDate): array {
    $options = $dateOptions;
    $formatted = $savedDate($field, $pattern);
    if ($formatted !== null) {
        $options['value'] = $formatted;
    }

    return $options;
};
?>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <?= $this->Form->label('date_competition', 'Verseny ideje:', ['class' => 'form-control-label fw-bold']) ?>
    </div>
    <div class="col-12 col-md-9">
        <?= $this->Input->datetime('date_competition', $dateField('date_competition', 'Y-m-d H:i:s')) ?>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <?= $this->Form->label('date_from', 'Jelentkezés kezdete:', ['class' => 'form-control-label fw-bold']) ?>
    </div>
    <div class="col-12 col-md-9">
        <?= $this->Input->date('date_from', $dateField('date_from', 'Y-m-d')) ?>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <?= $this->Form->label('date_deadline', 'Jelentkezési határidő:', ['class' => 'form-control-label fw-bold']) ?>
    </div>
    <div class="col-12 col-md-9">
        <?= $this->Input->date('date_deadline', $dateField('date_deadline', 'Y-m-d')) ?>
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
