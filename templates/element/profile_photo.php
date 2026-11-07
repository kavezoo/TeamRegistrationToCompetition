<?php
/**
 * A saját Google-profilkép a profil adatlapon. Kép nélkül a kezdőbetű látszik.
 *
 * @var \App\View\AppView $this
 * @var object|array<string, mixed>|null $user
 */
use App\Model\Entity\User;

$user ??= null;
$url = User::avatarUrl($user);
$name = User::displayName($user);
$initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
?>
<div class="row mb-3">
    <div class="col-12 col-md-2 text-start text-md-end">
        <span class="form-control-label fw-bold">Profilkép:</span>
    </div>
    <div class="col-12 col-md-9">
        <?php if ($url !== null) : ?>
            <img src="<?= h($url) ?>" alt="<?= h($name) ?>" width="96" height="96" class="rounded-circle" style="width:96px;height:96px;object-fit:cover" referrerpolicy="no-referrer">
        <?php else : ?>
            <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:96px;height:96px;font-size:2rem;font-weight:600" aria-hidden="true"><?= h($initial) ?></span>
        <?php endif; ?>
    </div>
</div>
