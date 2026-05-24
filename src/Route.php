<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter;

final readonly class Route
{
    /**
     * @param string             $method     HTTP method, uppercase.
     * @param string             $pattern    Raw pattern, e.g. '/api/users/{id}'.
     * @param mixed              $handler    Closure, FQCN string, or [class, method].
     *                                       Stored as-is; chapter 4 resolves it.
     * @param string             $regex      Compiled regex, ready for preg_match.
     * @param list<string>       $paramNames Path-parameter names in order of appearance.
     */
    public function __construct(
        public string $method,
        public string $pattern,
        public mixed  $handler,
        public string $regex,
        public array  $paramNames,
    ) {}
}