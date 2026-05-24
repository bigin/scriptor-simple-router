<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Bigins\ScriptorSimpleRouter\Request;
use Bigins\ScriptorSimpleRouter\Response;
use Bigins\ScriptorSimpleRouter\Router;

$r = Router::instance();

$r->get('/api/users/{id}', function (Request $req): Response {
    return Response::json(['id' => (int) $req->param('id'), 'name' => 'Ada']);
});

$r->post('/echo', function (Request $req): Response {
    return Response::json(['you-sent' => $req->body])->status(201);
});

$cases = [
    new Request('GET', '/api/users/42'),
    new Request('POST', '/echo', body: ['hello' => 'world']),
    new Request('GET', '/nope'),
];

foreach ($cases as $req) {
    $resp = $r->dispatch($req);
    if ($resp === null) {
        printf("%-6s %-22s -> no match\n", $req->method, $req->path);
        continue;
    }
    printf(
        "%-6s %-22s -> %d %s\n",
        $req->method,
        $req->path,
        $resp->status,
        $resp->body,
    );
}