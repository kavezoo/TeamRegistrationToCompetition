<?php
/**
 * Kilépés a név és a fiókmenü mellett. Minden prefix fejléce ezt használja.
 *
 * @var \App\View\AppView $this
 */
?>
<a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 ms-2" href="<?= $this->Url->build('/logout') ?>">
    <i class="fa-solid fa-power-off" aria-hidden="true"></i>
    Kilépés
</a>
