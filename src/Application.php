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
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use App\Middleware\HostHeaderMiddleware;
use App\Model\Entity\User;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\TableRegistry;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventInterface;
use Cake\Event\EventManagerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use CakeDC\Users\UsersPlugin;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        // By default, does not allow fallback classes.
        FactoryLocator::add(
            'Table',
            (new TableLocator())->allowFallbackClass(false),
        );
		$this->addPlugin('JeffAdmin', ['routes' => true, 'bootstrap' => true]);

        // A plugin bootstrapja ezután tölti be a config/users.php felülírásokat.
        Configure::write('Users.config', ['users']);
        $this->addPlugin('CakeDC/Users');
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Validate Host header to prevent Host Header Injection attacks.
            // In production, ensures App.fullBaseUrl is configured and validates
            // the incoming Host header against it.
            ->add(new HostHeaderMiddleware())

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/5/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/5/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            // Az /api-v1 prefix távoli hívásokhoz készül, ott nincs CSRF-ellenőrzés.
            ->add((new CsrfProtectionMiddleware([
                'httponly' => true,
            ]))->skipCheckCallback(function (ServerRequestInterface $request): bool {
                return $request->getParam('prefix') === 'ApiV1';
            }));

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
        // Allow your Tables to be dependency injected
        //$container->delegate(new \Cake\ORM\Locator\TableContainer());
    }

    /**
     * Register custom event listeners here
     *
     * @param \Cake\Event\EventManagerInterface $eventManager
     * @return \Cake\Event\EventManagerInterface
     * @link https://book.cakephp.org/5/en/core-libraries/events.html#registering-listeners
     */
    public function events(EventManagerInterface $eventManager): EventManagerInterface
    {
        // A CakeDC Users nyilvános képernyői a JeffAdmin auth kártyát használják, menü nélkül.
        $eventManager->on('Controller.initialize', function (EventInterface $event): void {
            $controller = $event->getSubject();
            if (!$controller instanceof Controller) {
                return;
            }

            $request = $controller->getRequest();
            if ($request->getParam('plugin') !== 'CakeDC/Users' || $request->getParam('controller') !== 'Users') {
                return;
            }

            $action = (string)$request->getParam('action');
            if ($action === 'profile') {
                $identity = $request->getAttribute('identity');
                $role = $identity ? (string)($identity['role'] ?? '') : '';
                $target = match ($role) {
                    User::ROLE_ADMIN => '/admin/profile',
                    User::ROLE_PRESIDENT => '/president/profile',
                    User::ROLE_MEMBER => '/member/profile',
                    User::ROLE_NEW => '/',
                    default => null,
                };
                if ($identity && !empty($identity['is_superuser'])) {
                    $target = '/admin/profile';
                }
                if ($target !== null) {
                    $event->setResult($controller->redirect($target));
                } else {
                    $controller->viewBuilder()->setLayout('login');
                    $controller->viewBuilder()->addHelper('CakeDC/Users.User');
                }

                return;
            }

            $authActions = [
                'login',
                'register',
                'requestResetPassword',
                'changePassword',
                'resendTokenValidation',
                'socialEmail',
            ];
            if (!in_array($action, $authActions, true)) {
                return;
            }

            $controller->viewBuilder()->setLayout('login');
            $controller->viewBuilder()->addHelper('CakeDC/Users.User');
        });

        // Új Google-felhasználó new szerepet kap. Meglévő fiók szerepe nem íródik felül.
        $eventManager->on(UsersPlugin::EVENT_BEFORE_SOCIAL_LOGIN_USER_CREATE, function (EventInterface $event): void {
            $user = $event->getData('userEntity');
            if (!$user instanceof EntityInterface) {
                return;
            }
            if ($user->isNew()) {
                $user->set('role', User::ROLE_NEW, ['guard' => false]);

                return;
            }
            $original = $user->getOriginal('role');
            if ($original !== null) {
                $user->set('role', $original, ['guard' => false]);
            }
        });

        // A szerep a belépéskor kerül a munkamenetbe. Elfogadás után a következő kérés már a friss szerepet látja.
        $eventManager->on('Controller.startup', function (EventInterface $event): void {
            $controller = $event->getSubject();
            if (!$controller instanceof Controller) {
                return;
            }
            $request = $controller->getRequest();
            $identity = $request->getAttribute('identity');
            $id = $identity !== null ? ($identity['id'] ?? null) : null;
            if ($id === null || $id === '') {
                return;
            }
            try {
                $user = TableRegistry::getTableLocator()->get('Users')->get($id);
            } catch (\Throwable) {
                return;
            }
            $session = $request->getSession();
            $auth = $session->read('Auth');
            if (!is_array($auth) || (string)($auth['role'] ?? '') === (string)$user->get('role')) {
                return;
            }
            $auth['role'] = $user->get('role');
            $session->write('Auth', $auth);
        });

        // A new és a tag szerep a nyilvános profilon csak a saját nevét menti.
        $eventManager->on('Controller.startup', function (EventInterface $event): void {
            $controller = $event->getSubject();
            if (!$controller instanceof Controller) {
                return;
            }
            $request = $controller->getRequest();
            if (
                $request->getParam('plugin') !== 'CakeDC/Users'
                || $request->getParam('controller') !== 'Users'
                || $request->getParam('action') !== 'profile'
                || !$request->is(['post', 'put', 'patch'])
            ) {
                return;
            }

            $identity = $request->getAttribute('identity');
            $id = $identity !== null ? ($identity['id'] ?? null) : null;
            if ($id === null || $id === '') {
                return;
            }

            $users = TableRegistry::getTableLocator()->get('Users');
            $user = $users->get($id);
            $user = $users->patchEntity($user, $request->getData(), [
                'fields' => ['first_name', 'last_name'],
            ]);
            if ($users->save($user)) {
                $controller->Flash->success('A profil mentve.');
            } else {
                $controller->Flash->error('A profilt nem sikerült menteni.');
            }
        });

        return $eventManager;
    }
}
