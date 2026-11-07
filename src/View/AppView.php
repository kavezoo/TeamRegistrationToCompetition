<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use Cake\View\View;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // A törlés saját űrlapja a lap végére kerül, ne a szerkesztő form belsejébe.
        // Különben a böngésző a belső </form> címkével lezárja a mentő űrlapot,
        // a Mentés DELETE-ként megy el, és az edit() szó nélkül újrarajzolja az oldalt.
        $formHelper = $this->helpers['Form'] ?? [];
        if (!is_array($formHelper)) {
            $formHelper = [];
        }
        $formHelper['defaultPostLinkBlock'] = true;
        $this->helpers['Form'] = $formHelper;

        $this->loadHelper('Flash');
        $this->Html->css('flash', ['block' => true]);
        $this->Html->css('form-card', ['block' => true]);
        $identity = $this->getRequest()->getAttribute('identity');
        $role = $identity ? (string)($identity['role'] ?? '') : '';
        $showsPrivacy = (string)$this->getRequest()->getParam('prefix') === 'Member' || $role === 'new';
        if ($showsPrivacy) {
            $this->Html->css('member-chrome', ['block' => true]);
            $this->Html->css('privacy-notice', ['block' => true]);
        }
        $this->Html->script('confirm-question', ['block' => true]);
        $this->Html->script('date-guard', ['block' => true]);

        $this->getEventManager()->on('View.beforeLayout', function () use ($showsPrivacy): void {
            if ($showsPrivacy) {
                $this->append('script', $this->element('privacy_modal'));
            }
            $postLinks = $this->fetch('postLink');
            if ($postLinks !== '') {
                $this->append('content', $postLinks);
            }

            // A plugin elrendezése nem írja ki a flash üzenetet. A sablonok se fogyasszák el előre.
            if ((string)$this->getLayout() !== 'JeffAdmin.default') {
                return;
            }
            $flash = $this->Flash->render() . $this->Flash->render('auth');
            if ($flash !== '') {
                $this->prepend('content', $flash);
            }
        });
    }
}
