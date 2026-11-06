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
                    <p>A klubelnök elutasította a felvételt. Ha módosítod a profilodat, a kérelem újra a klubelnök elé kerül.</p>
                <?php elseif ($state === 'pending') : ?>
                    <p>A jelentkezésed a klubelnöknél van. Amíg el nem fogad tagként, várakoznod kell.</p>
                <?php else : ?>
                    <p>A profilod megvan. Mentsd újra, hogy a kérelem a klubelnökhöz kerüljön, és utána várnod kell a jóváhagyásra.</p>
                <?php endif; ?>
                <p class="mb-0"><?= $this->Html->link('Profil módosítása', ['controller' => 'Pages', 'action' => 'profile'], ['class' => 'btn btn-primary']) ?></p>
            </div>
        </div>
    </div>
</div>
