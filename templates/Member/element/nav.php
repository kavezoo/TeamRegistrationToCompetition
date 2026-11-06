<?php
/**
 * Tagi menü.
 *
 * @var \Cake\View\View $this
 * @var string $controller
 * @var string $prefix
 */
$current = (string)($controller ?? $this->request->getParam('controller') ?? '');
$actionName = (string)($action ?? $this->request->getParam('action') ?? '');
$prefixName = (string)($prefix ?? $this->request->getParam('prefix') ?? 'Member');
$onProfile = $current === 'Dashboard' && $actionName === 'profile';
?>
<aside class="menu-sidebar" id="main-sidebar">
    <div class="logo">
        <a class="logo-link" href="<?= $this->Url->build(['prefix' => $prefixName, 'controller' => 'Registrations', 'action' => 'index']) ?>" aria-label="Tag">
            <span class="logo-mark" aria-hidden="true">T</span>
            <span class="logo-text">TeamReg</span>
        </a>
        <button type="button" class="sidebar-close js-sidebar-toggle" aria-label="Menü bezárása">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
    <div class="menu-sidebar__content js-scrollbar1">
        <nav class="navbar-sidebar">
            <ul class="list-unstyled navbar__list">
                <li<?= $onProfile ? ' class="active"' : '' ?>>
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-user" aria-hidden="true"></i>' . h('Profil'),
                        ['prefix' => $prefixName, 'controller' => 'Dashboard', 'action' => 'profile'],
                        ['escape' => false]
                    ) ?>
                </li>
                <li<?= $current === 'Registrations' ? ' class="active"' : '' ?>>
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>' . h('Jelentkezők'),
                        ['prefix' => $prefixName, 'controller' => 'Registrations', 'action' => 'index'],
                        ['escape' => false]
                    ) ?>
                </li>
            </ul>
        </nav>
    </div>
</aside>
