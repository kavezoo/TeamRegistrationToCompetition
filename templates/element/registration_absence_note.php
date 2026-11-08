<?php
/**
 * A határidő után jelzett hiányzás és az indoka.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration|null $registration
 * @var bool $block
 */
$registration = $registration ?? null;
$block = $block ?? false;
if ($registration === null || !$registration->isAbsent()) {
    return;
}
$reason = $registration->absenceReason();
$tag = $block ? 'div' : 'span';
?>
<<?= $tag ?> class="text-danger<?= $block ? ' mt-2' : ' d-block' ?>">Nem tud jönni<?php if ($reason !== '') : ?>: <?= h($reason) ?><?php endif; ?></<?= $tag ?>>
