<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter\Tests\Unit;

use Bigins\ScriptorSimpleRouter\Request;
use Bigins\ScriptorSimpleRouter\Response;
use Bigins\ScriptorSimpleRouter\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        // Router::instance() is a singleton; reset between tests so
        // routes registered in one case don't leak into another.
        // Reflection is the smallest hammer for that.
        $ref = new \ReflectionClass(Router::class);
        $prop = $ref->getProperty('instance');
        $prop->setValue(null, null);

        $this->router = Router::instance();
    }

    public function test_match_returns_route_and_params(): void
    {
        $this->router->get('/api/users/{id}', 'noop');

        $hit = $this->router->match('/api/users/42', 'GET');

        self::assertNotNull($hit);
        self::assertSame(['id' => '42'], $hit['params']);
    }

    public function test_match_filters_by_method(): void
    {
        $this->router->get('/api/users/{id}', 'noop');

        self::assertNull($this->router->match('/api/users/42', 'POST'));
    }

    public function test_dispatch_invokes_closure_handler_with_request(): void
    {
        $this->router->get('/api/users/{id}', static function (Request $req): Response {
            return Response::json(['id' => (int) $req->param('id')]);
        });

        $response = $this->router->dispatch(new Request('GET', '/api/users/42'));

        self::assertNotNull($response);
        self::assertSame(200, $response->status);
        self::assertSame('{"id":42}', $response->body);
    }

    public function test_dispatch_returns_null_on_no_match(): void
    {
        self::assertNull($this->router->dispatch(new Request('GET', '/nope')));
    }
}