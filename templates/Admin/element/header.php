<?php
/**
 * Admin fejléc — kereső és a bejelentkezett fiók menüje.
 *
 * @var \Cake\View\View $this
 * @var string|null $action
 * @var string|null $listSearch
 * @var string|null $listSort
 * @var string|null $listDirection
 */
$isIndexAction = (($action ?? '') === 'index');
$searchValue = (string)($listSearch ?? '');
$searchAutofocus = $isIndexAction && $searchValue !== '';

$identity = $this->getRequest()->getAttribute('identity');
$accountName = 'Fiók';
$accountEmail = '';
if ($identity) {
    $accountName = trim((string)($identity['last_name'] ?? '') . ' ' . (string)($identity['first_name'] ?? ''));
    if ($accountName === '') {
        $accountName = (string)($identity['username'] ?? $identity['email'] ?? 'Fiók');
    }
    $accountEmail = (string)($identity['email'] ?? '');
}
$profileUrl = $this->Url->build('/admin/profile');
?>
		<header class="header-desktop">
          <div class="section__content section__content--p30">
            <div class="container-fluid">
              <div class="header-wrap">
                <button class="sidebar-toggle js-sidebar-toggle" type="button" aria-label="Menü" aria-expanded="false" aria-controls="main-sidebar"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
                <form class="form-header<?= $searchValue !== '' ? ' form-header--has-clear' : '' ?>" role="search" method="get" action="<?= h($this->Url->build(['action' => 'index'])) ?>">
                  <i class="fa-solid fa-magnifying-glass form-header__icon" aria-hidden="true"></i>
                  <input class="au-input au-input--xl" type="search" name="search" value="<?= h($searchValue) ?>" placeholder="<?= h(__('Search anything…')) ?>" aria-label="<?= h(__('Search')) ?>"<?= $searchAutofocus ? ' autofocus' : '' ?>>
<?php if ($searchValue !== '') :
    $clearQuery = ['search' => ''];
    if (!empty($listSort)) {
        $clearQuery['sort'] = (string)$listSort;
        $clearQuery['direction'] = (string)($listDirection ?? 'asc');
    }
?>
                  <a class="form-header__clear"
                     href="<?= h($this->Url->build(['action' => 'index', '?' => $clearQuery])) ?>"
                     data-bs-toggle="tooltip"
                     data-bs-title="<?= h(__('Clear the active search filter')) ?>"
                     aria-label="<?= h(__('Clear the active search filter')) ?>">
                    <?= $this->Icon->outline('x', 'form-header__clear-icon') ?>
                  </a>
<?php endif; ?>
<?php if (!empty($listSort)) : ?>
                  <input type="hidden" name="sort" value="<?= h((string)$listSort) ?>">
                  <input type="hidden" name="direction" value="<?= h((string)($listDirection ?? 'asc')) ?>">
<?php endif; ?>
                  <kbd class="form-header__hint" aria-hidden="true">⌘K</kbd>
                </form>
                <div class="header-button">
                  <div class="noti-wrap"></div>
                  <div class="account-wrap">
                    <div class="account-item clearfix js-item-menu" role="button" tabindex="0" aria-haspopup="true" aria-label="Fiók menü">
                      <div class="image"><?= $this->Icon->filled('user', ['alt' => $accountName]) ?></div>
                      <div class="content"><a class="js-acc-btn" href="<?= h($profileUrl) ?>"><?= h($accountName) ?></a></div>
                      <div class="account-dropdown js-dropdown border border-2 shadow">
                        <div class="info clearfix">
                          <div class="image"><a href="<?= h($profileUrl) ?>">
								<?= $this->Icon->filled('user', ['alt' => $accountName]) ?>
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
                </div>
              </div>
            </div>
          </div>
        </header>
