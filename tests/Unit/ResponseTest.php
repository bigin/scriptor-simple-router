<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter\Tests\Unit;

use Bigins\ScriptorSimpleRouter\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function test_json_factory_sets_content_type_and_body(): void
    {
        $r = Response::json(['hello' => 'world']);

        self::assertSame(200, $r->status);
        self::assertSame('application/json', $r->headers['Content-Type']);
        self::assertSame('{"hello":"world"}', $r->body);
    }

    public function test_redirect_factory(): void
    {
        $r = Response::redirect('/elsewhere', 301);

        self::assertSame(301,         $r->status);
        self::assertSame('/elsewhere', $r->headers['Location']);
    }

    public function test_status_and_header_return_self_for_chaining(): void
    {
        $r = Response::text('OK')->status(201)->header('X-Total-Count', '42');

        self::assertSame(201,  $r->status);
        self::assertSame('42', $r->headers['X-Total-Count']);
    }
}