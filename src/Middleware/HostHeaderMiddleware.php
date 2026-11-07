<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\MailLinks;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to validate Host header and prevent Host Header Injection attacks.
 *
 * A kérés hostja érvényes domain kell legyen. A levél és a többi abszolút cím
 * ezt a domaint használja, ezért másik domain alatt a programot nem kell átírni.
 *
 * @see https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/17-Testing_for_Host_Header_Injection
 */
class HostHeaderMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and validate the Host header.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = MailLinks::originFromRequest($request);
        if ($origin !== null) {
            Router::fullBaseUrl($origin);
        } elseif (!Configure::read('debug')) {
            throw new BadRequestException('Invalid Host header.');
        }

        return $handler->handle($request);
    }
}
