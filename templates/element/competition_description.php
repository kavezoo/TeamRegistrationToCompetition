<?php
/**
 * A verseny leírása, ha van kitöltve.
 *
 * @var \App\Model\Entity\Competition $competition
 */
$html = $competition->descriptionHtml();
if ($html === null) {
    return;
}
?>
<div class="mb-3 competition-description"><?= $html ?></div>
