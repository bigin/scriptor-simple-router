<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter;

final class Response
{
    /**
     * @param int                   $status  HTTP status code.
     * @param array<string, string> $headers Header map. Names are
     *                                       case-insensitive on the wire;
     *                                       this class stores them verbatim.
     * @param string                $body    Response body.
     */
    public function __construct(
        public int    $status  = 200,
        public array  $headers = [],
        public string $body    = '',
    ) {}

    public static function json(mixed $data, int $status = 200): self
    {
        return new self(
            status:  $status,
            headers: ['Content-Type' => 'application/json'],
            body:    (string) json_encode($data, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE),
        );
    }

    public static function text(string $text, int $status = 200): self
    {
        return new self(
            status:  $status,
            headers: ['Content-Type' => 'text/plain; charset=utf-8'],
            body:    $text,
        );
    }

    public static function html(string $html, int $status = 200): self
    {
        return new self(
            status:  $status,
            headers: ['Content-Type' => 'text/html; charset=utf-8'],
            body:    $html,
        );
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self(
            status:  $status,
            headers: ['Location' => $location],
            body:    '',
        );
    }

    public function status(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Emit the response: status line, headers, body. Called once
     * per request from Router::handle() in chapter 5.
     */
    public function send(): void
    {
        if (! headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value, true);
            }
        }
        echo $this->body;
    }
}