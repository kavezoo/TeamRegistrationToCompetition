<?php
/**
 * Új jelentkező főoldala a profil mentése után.
 *
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $user
 */
$state = (string)($user->get('membership_request') ?? '');
$this->assign('title', 'Főoldal');
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12 col-xxl-11">
        <div class="card shadow">
            <div class="card-header">
                <strong>Főoldal</strong>
            </div>
            <div class="card-body">
                <?php if ($state === 'denied') : ?>
                    <p>A klubelnök jelezte, hogy nem vagy tag, csak kipróbáltad az oldalt. Az adataidat törölheted. Ha mégis tag szeretnél lenni, a profil mentése újra a klubelnök elé viszi a kérelmet.</p>
                <?php elseif ($state === 'pending') : ?>
                    <p>A jelentkezésed a klubelnöknél van. Amíg el nem fogad tagként, várakoznod kell.</p>
                <?php else : ?>
                    <p>A profilod megvan. Mentsd újra, hogy a kérelem a klubelnökhöz kerüljön, és utána várnod kell a jóváhagyásra.</p>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?= $this->Html->link('Profil módosítása', ['controller' => 'Pages', 'action' => 'profile'], ['class' => 'btn btn-primary']) ?>
                    <?= $this->element('delete_own_account') ?>
                </div>
            </div>
        </div>
    </div>
</div>
