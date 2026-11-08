<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
        $builder->connect('/profil', ['controller' => 'Pages', 'action' => 'profile']);
        $builder->connect('/fiok-torlese', ['controller' => 'Pages', 'action' => 'deleteAccount']);

        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        $builder->connect('/pages/*', 'Pages::display');

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * It is NOT recommended to use fallback routes after your initial prototyping phase!
         * See https://book.cakephp.org/5/en/development/routing.html#fallbacks-method for more information
         */
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
	$routes->prefix('Admin', function (RouteBuilder $builder) {
		$builder->setRouteClass(DashedRoute::class);
		$builder->connect('/', ['controller' => 'Users', 'action' => 'index']);
		$builder->connect('/profile', ['controller' => 'Users', 'action' => 'profile']);
		$builder->fallbacks();
	});

	$routes->prefix('Member', function (RouteBuilder $builder) {
		$builder->setRouteClass(DashedRoute::class);
		$builder->connect('/', ['controller' => 'Registrations', 'action' => 'index']);
		$builder->connect('/profile', ['controller' => 'Dashboard', 'action' => 'profile']);
		$builder->fallbacks();
	});

	$routes->prefix('President', function (RouteBuilder $builder) {
		$builder->setRouteClass(DashedRoute::class);
		$builder->connect('/', ['controller' => 'Dashboard', 'action' => 'home']);
		$builder->connect('/profile', ['controller' => 'Dashboard', 'action' => 'profile']);
		$builder->fallbacks();
	});

    $routes->prefix('ApiV1', function (RouteBuilder $builder): void {
        $builder->setRouteClass(DashedRoute::class);
        $builder->setExtensions(['json']);
        $builder->connect('/registrations/roster', [
            'controller' => 'Registrations',
            'action' => 'roster',
        ])->setMethods(['GET']);
        $builder->connect('/registrations/{id}/absent', [
            'controller' => 'Registrations',
            'action' => 'absent',
        ])->setPass(['id'])->setMethods(['POST']);
        $builder->connect('/registrations/mine', [
            'controller' => 'Registrations',
            'action' => 'mine',
        ])->setMethods(['GET']);
        $builder->connect('/applicants', [
            'controller' => 'Applicants',
            'action' => 'index',
        ])->setMethods(['GET']);
        $builder->connect('/applicants/{id}/approve', [
            'controller' => 'Applicants',
            'action' => 'approve',
        ])->setPass(['id'])->setMethods(['POST']);
        $builder->connect('/applicants/{id}/deny', [
            'controller' => 'Applicants',
            'action' => 'deny',
        ])->setPass(['id'])->setMethods(['POST']);
        $builder->connect('/competitions/past', [
            'controller' => 'Competitions',
            'action' => 'past',
        ])->setMethods(['GET']);
        $builder->resources('Competitions');
        $builder->resources('Registrations');
        $builder->resources('Teams');
        $builder->connect('/users/{id}/role', [
            'controller' => 'Users',
            'action' => 'role',
        ])->setPass(['id'])->setMethods(['PATCH', 'PUT', 'POST']);
        $builder->connect('/users/{id}/enabled', [
            'controller' => 'Users',
            'action' => 'enabled',
        ])->setPass(['id'])->setMethods(['PATCH', 'PUT', 'POST']);
        $builder->connect('/users/profile', [
            'controller' => 'Users',
            'action' => 'profile',
        ])->setMethods(['GET', 'PATCH', 'PUT', 'POST']);
        $builder->connect('/users/delete-account', [
            'controller' => 'Users',
            'action' => 'deleteAccount',
        ])->setMethods(['POST', 'DELETE']);
        $builder->connect('/users/{id}', [
            'controller' => 'Users',
            'action' => 'remove',
        ])->setPass(['id'])->setMethods(['DELETE']);
        $builder->connect('/messages', [
            'controller' => 'Messages',
            'action' => 'index',
        ])->setMethods(['GET', 'POST']);
        $builder->connect('/messages/{id}/reply', [
            'controller' => 'Messages',
            'action' => 'reply',
        ])->setPass(['id'])->setMethods(['POST']);
        $builder->connect('/messages/{id}', [
            'controller' => 'Messages',
            'action' => 'view',
        ])->setPass(['id'])->setMethods(['GET']);
        $builder->connect('/flashes', [
            'controller' => 'Flashes',
            'action' => 'index',
        ])->setMethods(['GET']);
        $builder->connect('/flashes/{id}/seen', [
            'controller' => 'Flashes',
            'action' => 'seen',
        ])->setPass(['id'])->setMethods(['POST']);
        $builder->connect('/auth/google', [
            'controller' => 'Auth',
            'action' => 'google',
        ])->setMethods(['POST']);
        $builder->connect('/auth/refresh', [
            'controller' => 'Auth',
            'action' => 'refresh',
        ])->setMethods(['POST']);
        $builder->connect('/auth/logout', [
            'controller' => 'Auth',
            'action' => 'logout',
        ])->setMethods(['POST']);
        $builder->connect('/auth/logout-all', [
            'controller' => 'Auth',
            'action' => 'logoutAll',
        ])->setMethods(['POST']);
        $builder->connect('/auth/me', [
            'controller' => 'Auth',
            'action' => 'me',
        ])->setMethods(['GET']);
        $builder->connect('/members', [
            'controller' => 'Members',
            'action' => 'index',
        ])->setMethods(['GET']);
        $builder->connect('/members/remind-missing', [
            'controller' => 'Members',
            'action' => 'remindMissing',
        ])->setMethods(['POST']);
        $builder->connect('/members/{id}/remind', [
            'controller' => 'Members',
            'action' => 'remind',
        ])->setPass(['id'])->setMethods(['POST']);
    });

};
