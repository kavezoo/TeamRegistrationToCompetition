<?php
/**
 * Név a Google-profilképpel. Kép nélkül a kezdőbetű látszik.
 *
 * @var \App\View\AppView $this
 * @var object|array<string, mixed>|null $user
 * @var bool|null $bold
 * @var string|null $line
 * @var string|null $mark
 */
use App\Model\Entity\User;

$user ??= null;
$name = User::displayName($user);
$url = User::avatarUrl($user);
$initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
$line = trim((string)($line ?? ''));
$bold = !empty($bold);
$mark = trim((string)($mark ?? ''));
?>
<span class="d-inline-flex align-items-center gap-2">
<?php if ($url !== null) : ?>
    <img src="<?= h($url) ?>" alt="" width="40" height="40" class="rounded-circle flex-shrink-0" style="width:40px;height:40px;object-fit:cover" referrerpolicy="no-referrer">
<?php else : ?>
    <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;font-weight:600" aria-hidden="true"><?= h($initial) ?></span>
<?php endif; ?>
    <span class="lh-sm">
        <span<?= $bold ? ' class="fw-bold"' : '' ?>><?= h($name) ?></span><?php if ($mark !== '') : ?><span class="badge text-bg-secondary ms-1"><?= h($mark) ?></span><?php endif; ?>
        <?php if ($line !== '') : ?>
            <span class="d-block fw-normal"><?= h($line) ?></span>
        <?php endif; ?>
    </span>
</span>
