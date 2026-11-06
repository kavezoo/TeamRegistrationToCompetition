<?php
/**
 * A verseny leírása, ha van kitöltve.
 *
 * @var \App\Model\Entity\Competition $competition
 */
$description = trim((string)($competition->description ?? ''));
if ($description === '') {
    return;
}
?>
<p class="mb-3"><?= nl2br(h($description)) ?></p>
