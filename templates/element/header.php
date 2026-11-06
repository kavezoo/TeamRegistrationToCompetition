<?php
/**
 * Új jelentkező fejléce. A profil külön oldal, a főoldal a várakozás.
 *
 * @var \Cake\View\View $this
 */
$identity = $this->getRequest()->getAttribute('identity');
$accountName = 'Fiók';
$accountEmail = '';
if ($identity) {
    $accountName = trim((string)($identity['first_name'] ?? '') . ' ' . (string)($identity['last_name'] ?? ''));
    if ($accountName === '') {
        $accountName = (string)($identity['username'] ?? $identity['email'] ?? 'Fiók');
    }
    $accountEmail = (string)($identity['email'] ?? '');
}
$profileUrl = $this->Url->build(['controller' => 'Pages', 'action' => 'profile']);
?>
		<header class="header-desktop">
          <div class="section__content section__content--p30">
            <div class="container-fluid">
              <div class="header-wrap">
                <button class="sidebar-toggle js-sidebar-toggle" type="button" aria-label="Menü" aria-expanded="false" aria-controls="main-sidebar"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
                <div class="header-button ms-auto">
                  <div class="account-wrap">
                    <div class="account-item clearfix js-item-menu" role="button" tabindex="0" aria-haspopup="true" aria-label="Fiók menü">
                      <div class="image"><?= $this->element('account_avatar', ['alt' => $accountName]) ?></div>
                      <div class="content"><a class="js-acc-btn" href="<?= h($profileUrl) ?>"><?= h($accountName) ?></a></div>
                      <div class="account-dropdown js-dropdown border border-2 shadow">
                        <div class="info clearfix">
                          <div class="image"><a href="<?= h($profileUrl) ?>">
								<?= $this->element('account_avatar', ['alt' => $accountName]) ?>
							</a></div>
                          <div class="content">
                            <h5 class="name"><a href="<?= h($profileUrl) ?>"><?= h($accountName) ?></a></h5>
                            <?php if ($accountEmail !== '') : ?>
                              <span class="email"><?= h($accountEmail) ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="account-dropdown__body">
                          <div class="account-dropdown__item"><a href="<?= h($profileUrl) ?>"><i class="fa-solid fa-user"></i>Profil</a></div>
                        </div>
                        <div class="account-dropdown__footer"><a href="<?= $this->Url->build('/logout') ?>"><i class="fa-solid fa-power-off"></i>Kijelentkezés</a></div>
                      </div>
                    </div>
                  </div>
                  <?= $this->element('header_logout') ?>
                </div>
              </div>
            </div>
          </div>
        </header>
