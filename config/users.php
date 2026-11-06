<?php
/**
 * CakeDC Users felülírások.
 *
 * A plugin alapértelmezései a vendor/cakedc/users/config/users.php fájlban vannak.
 * Ez a fájl csak a helyi eltéréseket tartalmazza.
 */

use function Cake\Core\env;

return [
    'Users' => [
        'Email' => [
            // Helyi környezetben nincs levélküldés, a regisztrált fiók azonnal beléphet.
            'validate' => false,
        ],
        'Social' => [
            'login' => true,
        ],
        'Registration' => [
            // Google-belépés és űrlapos regisztráció is ezzel a szereppel jön létre.
            'defaultRole' => 'new',
        ],
    ],
    'OAuth' => [
        'providers' => [
            'google' => [
                'options' => [
                    'clientId' => env('GOOGLE_CLIENT_ID'),
                    'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
                ],
                'authParams' => [
                    'scope' => ['openid', 'email', 'profile'],
                ],
            ],
        ],
    ],
    'Auth' => [
        'Authenticators' => [
            'Form' => [
                'fields' => [
                    'username' => 'email',
                    'password' => 'password',
                ],
                'identifier' => [
                    'Authentication.Password' => [
                        'fields' => [
                            'username' => 'email',
                            'password' => 'password',
                        ],
                    ],
                ],
            ],
        ],
        'FormLoginFailure' => [
            'defaultMessage' => 'Az e-mail-cím vagy a jelszó hibás',
        ],
        // Az /api-v1 hívás JSON-t kap, a többi oldal továbbra is a bejelentkezésre megy.
        'AuthorizationMiddleware' => [
            'unauthorizedHandler' => [
                'className' => 'ApiJson',
            ],
        ],
    ],
];
