<?php
/**
 * Új jelentkező menüje: főoldal és a saját adatlap.
 *
 * @var \Cake\View\View $this
 */
$action = (string)$this->request->getParam('action');
?>
<aside class="menu-sidebar" id="main-sidebar">
    <div class="logo">
        <a class="logo-link" href="<?= $this->Url->build('/') ?>" aria-label="Főoldal">
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
                <li<?= $action === 'display' ? ' class="active"' : '' ?>>
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-house" aria-hidden="true"></i>' . h('Főoldal'),
                        '/',
                        ['escape' => false]
                    ) ?>
                </li>
                <li<?= $action === 'profile' ? ' class="active"' : '' ?>>
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-user" aria-hidden="true"></i>' . h('Profil'),
                        ['controller' => 'Pages', 'action' => 'profile'],
                        ['escape' => false]
                    ) ?>
                </li>
            </ul>
        </nav>
    </div>
</aside>
