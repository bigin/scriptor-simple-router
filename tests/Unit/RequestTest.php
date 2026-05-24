<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter\Tests\Unit;

use Bigins\ScriptorSimpleRouter\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function test_defaults(): void
    {
        $req = new Request();

        self::assertSame('GET', $req->method);
        self::assertSame('/',   $req->path);
        self::assertSame([],    $req->query);
        self::assertSame([],    $req->body);
        self::assertSame([],    $req->pathParams);
    }

    public function test_with_path_params_returns_new_instance(): void
    {
        $original = new Request('GET', '/api/users/{id}');
        $bound    = $original->withPathParams(['id' => '42']);

        self::assertNotSame($original, $bound);
        self::assertSame([], $original->pathParams);
        self::assertSame(['id' => '42'], $bound->pathParams);
    }

    public function test_accessors_fall_through_to_default(): void
    {
        $req = new Request(query: ['q' => 'php']);

        self::assertSame('php',   $req->get('q'));
        self::assertNull($req->get('missing'));
        self::assertSame('fallback', $req->get('missing', 'fallback'));
    }
}