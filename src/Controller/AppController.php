<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // A JeffAdmin plugin flash sablonjai (element/flash).
        $this->loadComponent('Flash', [
            'plugin' => 'JeffAdmin',
        ]);

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $action = (string)$this->request->getParam('action');
        if ($this->request->is('get') && in_array($action, ['add', 'edit', 'profile'], true)) {
            $this->rememberFormCaller();
        }
    }

    /**
     * A megnyitó oldal címét megjegyzi, hogy mentés után oda lehessen visszatérni.
     */
    protected function rememberFormCaller(): void
    {
        $referer = $this->request->referer(true);
        if (!is_string($referer) || !$this->isSafeReturnPath($referer)) {
            return;
        }

        $this->request->getSession()->write($this->formCallerKey(), $referer);
    }

    /**
     * Sikeres mentés: vissza a megnyitó oldalra, különben az indexre.
     *
     * @param array<string, mixed>|string $fallback
     */
    protected function redirectAfterSave(array|string $fallback = ['action' => 'index']): Response
    {
        $key = $this->formCallerKey();
        $return = $this->request->getSession()->read($key);
        $this->request->getSession()->delete($key);
        if (is_string($return) && $this->isSafeReturnPath($return)) {
            return $this->redirect($return);
        }

        return $this->redirect($fallback);
    }

    /**
     * @return array<string, string>|string
     */
    protected function homeAfterForm(): array|string
    {
        return match ((string)$this->request->getParam('prefix')) {
            'Admin' => ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
            'President' => ['prefix' => 'President', 'controller' => 'Applicants', 'action' => 'index'],
            'Member' => ['prefix' => 'Member', 'controller' => 'Registrations', 'action' => 'index'],
            default => '/',
        };
    }

    protected function formCallerKey(): string
    {
        $pass = $this->request->getParam('pass');
        $id = is_array($pass) && isset($pass[0]) ? (string)$pass[0] : '';

        return 'formReturn:' . implode(':', [
            (string)$this->request->getParam('prefix'),
            (string)$this->request->getParam('controller'),
            (string)$this->request->getParam('action'),
            $id,
        ]);
    }

    protected function isSafeReturnPath(string $referer): bool
    {
        if (
            $referer === ''
            || !str_starts_with($referer, '/')
            || str_starts_with($referer, '//')
            || str_contains($referer, '\\')
            || str_contains($referer, "\n")
            || str_contains($referer, "\r")
        ) {
            return false;
        }

        $path = parse_url($referer, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || str_contains($path, '..')) {
            return false;
        }
        if (preg_match('#^/(login|logout|auth)(/|$)#', $path) === 1) {
            return false;
        }

        $here = $this->request->getPath();
        $base = (string)$this->request->getAttribute('base');
        if ($base !== '' && $base !== '/' && str_starts_with($here, $base)) {
            $here = substr($here, strlen($base)) ?: '/';
        }
        if (!str_starts_with($here, '/')) {
            $here = '/' . $here;
        }

        return $path !== $here;
    }
}
