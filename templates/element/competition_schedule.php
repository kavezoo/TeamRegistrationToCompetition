<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 */
$when = $this->Input->formatDatetime($competition->date_competition ?? null);
$map = $competition->mapUrl();
?>
<p class="mb-2">
    Verseny ideje: <strong><?= $when !== '' ? $when : 'nincs megadva' ?></strong><br>
    Jelentkezés kezdete: <strong><?= $this->Input->formatDate($competition->date_from) ?></strong><br>
    Jelentkezési határidő: <strong><?= $this->Input->formatDate($competition->date_deadline) ?></strong>
    <?php if ($map !== null) : ?>
        <br>Helyszín: <strong><?= $this->Html->link('Térkép', $map, ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?></strong>
    <?php endif; ?>
</p>
