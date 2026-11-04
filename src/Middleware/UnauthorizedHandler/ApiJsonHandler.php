<?php
declare(strict_types=1);

namespace App\Middleware\UnauthorizedHandler;

use Authorization\Exception\Exception;
use Authorization\Middleware\UnauthorizedHandler\HandlerInterface;
use Cake\Http\Response;
use CakeDC\Users\Middleware\UnauthorizedHandler\DefaultRedirectHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Az /api-v1 prefix JSON választ kap, a többi kérés a szokásos bejelentkezési átirányítást.
 */
class ApiJsonHandler implements HandlerInterface
{
    /**
     * @param \Authorization\Exception\Exception $exception Az elutasítás.
     * @param \Psr\Http\Message\ServerRequestInterface $request A kérés.
     * @param array<string, mixed> $options A kezelő beállításai.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(
        Exception $exception,
        ServerRequestInterface $request,
        array $options = [],
    ): ResponseInterface {
        if ($request->getParam('prefix') !== 'ApiV1') {
            return (new DefaultRedirectHandler())->handle($exception, $request, $options);
        }

        $status = $request->getAttribute('identity') ? 403 : 401;
        $message = $status === 401
            ? 'Bejelentkezés szükséges.'
            : 'Nincs jogosultság ehhez a művelethez.';

        $payload = json_encode([
            'success' => false,
            'message' => $message,
            'code' => $status,
        ], JSON_UNESCAPED_UNICODE);

        return (new Response())
            ->withStatus($status)
            ->withType('application/json')
            ->withStringBody($payload !== false ? $payload : '{}');
    }
}
