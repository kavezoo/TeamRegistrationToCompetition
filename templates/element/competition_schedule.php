<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 */
$when = $this->Input->formatDatetime($competition->date_competition ?? null);
$map = $competition->mapUrl();
?>
<p class="text-muted mb-2">
    Verseny ideje: <?= $when !== '' ? $when : 'nincs megadva' ?><br>
    Jelentkezés kezdete: <?= $this->Input->formatDate($competition->date_from) ?><br>
    Jelentkezési határidő: <?= $this->Input->formatDate($competition->date_deadline) ?>
    <?php if ($map !== null) : ?>
        <br><?= $this->Html->link('Helyszín a térképen', $map, ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?>
    <?php endif; ?>
</p>
