<?php
/**
 * A pipaclub.hu-n lévő versenyleírás címe.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var bool $empty
 */
$url = $competition->infoUrl();
$empty = $empty ?? false;
?>
<?php if ($url !== null) : ?>
    <p class="mb-2">
        <a href="<?= h($url) ?>" target="_blank" rel="noopener noreferrer">A verseny leírása</a>
    </p>
<?php elseif ($empty) : ?>
    <p class="mb-2">Nincs link megadva a verseny leírásához.</p>
<?php endif; ?>
