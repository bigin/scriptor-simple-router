<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter;

final readonly class Request
{
    /**
     * @param string                $method     Uppercase HTTP method.
     * @param string                $path       URL path, leading slash, no query string.
     * @param array<string, string> $query      Parsed `$_GET`.
     * @param array<string, mixed>  $body       Parsed body params (form-encoded or JSON-decoded).
     * @param array<string, string> $pathParams Captured from the route pattern by Router::match().
     * @param array<string, string> $headers    Lowercase-key header map.
     * @param string                $rawBody    Untouched request body. Empty for GET.
     */
    public function __construct(
        public string $method     = 'GET',
        public string $path       = '/',
        public array  $query      = [],
        public array  $body       = [],
        public array  $pathParams = [],
        public array  $headers    = [],
        public string $rawBody    = '',
    ) {}

    /**
     * Return a new instance with the given path-parameter map.
     * Used by Router::dispatch() after a successful match.
     */
    public function withPathParams(array $pathParams): self
    {
        return new self(
            method:     $this->method,
            path:       $this->path,
            query:      $this->query,
            body:       $this->body,
            pathParams: $pathParams,
            headers:    $this->headers,
            rawBody:    $this->rawBody,
        );
    }

    public function param(string $name, ?string $default = null): ?string
    {
        return $this->pathParams[$name] ?? $default;
    }

    public function input(string $name, mixed $default = null): mixed
    {
        return $this->body[$name] ?? $default;
    }

    public function get(string $name, ?string $default = null): ?string
    {
        return $this->query[$name] ?? $default;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * Build a Request from PHP's superglobals. Called once per
     * request from chapter 5's `_ext.php` hook.
     */
    public static function fromGlobals(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $path   = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), \PHP_URL_PATH);

        // Header map: getallheaders() exists under Apache/FPM but
        // not on every SAPI. The HTTP_* fallback keeps the Request
        // testable without binding to a specific server.
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = (string) $value;
            }
        }

        $rawBody = (string) file_get_contents('php://input');
        $contentType = strtolower($headers['content-type'] ?? '');
        $body = match (true) {
            str_contains($contentType, 'application/json') && $rawBody !== '' =>
                (array) (json_decode($rawBody, true) ?? []),
            default => $_POST,
        };

        return new self(
            method:  $method,
            path:    $path,
            query:   $_GET,
            body:    $body,
            headers: $headers,
            rawBody: $rawBody,
        );
    }
}