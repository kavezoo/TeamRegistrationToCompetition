<?php
/**
 * Admin sidebar.
 *
 * @var \Cake\View\View $this
 * @var string $controller
 * @var string $prefix
 */
$current = (string)($controller ?? $this->request->getParam('controller') ?? '');
$actionName = (string)($action ?? $this->request->getParam('action') ?? '');
$prefixName = (string)($prefix ?? $this->request->getParam('prefix') ?? 'Admin');
$onProfile = $current === 'Users' && $actionName === 'profile';

$items = [
    'Competitions' => ['Versenyek', 'fa-trophy'],
    'Registrations' => ['Nevezések', 'fa-clipboard-list'],
    'Users' => ['Felhasználók', 'fa-users'],
    'SocialAccounts' => ['Közösségi fiókok', 'fa-share-nodes'],
    'FailedPasswordAttempts' => ['Sikertelen belépések', 'fa-lock'],
    'Teams' => ['Csapatok', 'fa-people-group'],
    'Messages' => ['Üzenetek', 'fa-comment-dots'],
    'Broadcasts' => ['Mindenkinek', 'fa-bullhorn'],
    'Boards' => ['Elnököknek', 'fa-user-tie'],
];
?>
<aside class="menu-sidebar" id="main-sidebar">
    <div class="logo">
        <a class="logo-link" href="<?= $this->Url->build(['prefix' => $prefixName, 'controller' => 'Competitions', 'action' => 'index']) ?>" aria-label="Admin">
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
                <?php foreach ($items as $name => [$label, $icon]) : ?>
                    <li<?= $current === $name && !($name === 'Users' && $onProfile) ? ' class="active"' : '' ?>>
                        <?= $this->Html->link(
                            '<i class="fa-solid ' . h($icon) . '" aria-hidden="true"></i>' . h($label),
                            ['prefix' => $prefixName, 'controller' => $name, 'action' => 'index'],
                            ['escape' => false]
                        ) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
