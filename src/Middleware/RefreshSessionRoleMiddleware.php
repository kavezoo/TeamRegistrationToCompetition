<?php
declare(strict_types=1);

namespace App\Middleware;

use ArrayAccess;
use Authentication\Identity;
use Cake\Datasource\EntityInterface;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A belépés a felhasználót a munkamenetbe menti, és a későbbi kérések nem olvassák újra a táblát.
 * Elfogadás után a táblában már tag a szerep, a munkamenetben viszont még new marad.
 * A tagi oldal ezért tiltott, a kezdőlap pedig visszadobja oda: a böngésző végtelen átirányításba kerül.
 * Ez a réteg a jogosultság ellenőrzése előtt cseréli a szerepet a táblában lévőre.
 */
class RefreshSessionRoleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $request->getAttribute('identity');
        $data = self::identityData($identity);
        $id = self::readId($data);
        if ($id === null) {
            return $handler->handle($request);
        }

        try {
            $user = TableRegistry::getTableLocator()->get('Users')->get($id);
        } catch (\Throwable) {
            return $handler->handle($request);
        }

        $role = (string)$user->get('role');
        $session = $request->getAttribute('session');
        if (!$session instanceof Session || $role === '' || $data === null) {
            return $handler->handle($request);
        }

        $previous = self::readRole($data);
        $updated = self::persistRole($session, $data, $role);
        if ($previous !== $role && is_array($updated)) {
            $request = $request->withAttribute('identity', new Identity($updated));
        }

        return $handler->handle($request);
    }

    /**
     * A munkamenetben lévő szerepet a táblában lévőre írja. Entitásnál ugyanaz az objektum frissül.
     */
    public static function persistRole(Session $session, mixed $data, string $role): mixed
    {
        if ($role === '' || !self::readable($data) || self::readRole($data) === $role) {
            return $data;
        }
        if ($data instanceof EntityInterface) {
            $data->set('role', $role, ['guard' => false]);
        } elseif (is_array($data)) {
            $data['role'] = $role;
        } elseif ($data instanceof ArrayAccess) {
            $data['role'] = $role;
        }
        $session->write('Auth', $data);

        return $data;
    }

    private static function identityData(mixed $identity): mixed
    {
        if (is_object($identity) && method_exists($identity, 'getOriginalData')) {
            return $identity->getOriginalData();
        }

        return $identity;
    }

    private static function readId(mixed $data): ?string
    {
        if (!self::readable($data)) {
            return null;
        }
        $id = $data['id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        return (string)$id;
    }

    private static function readRole(mixed $data): string
    {
        if (!self::readable($data)) {
            return '';
        }

        return (string)($data['role'] ?? '');
    }

    private static function readable(mixed $data): bool
    {
        return is_array($data) || $data instanceof ArrayAccess;
    }
}
