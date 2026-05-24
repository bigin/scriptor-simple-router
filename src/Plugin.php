<?php

declare(strict_types=1);

namespace Bigins\ScriptorSimpleRouter;

use Scriptor\Boot\Plugin\Plugin as ScriptorPlugin;
use Scriptor\Boot\Plugin\PluginContext;

final class Plugin implements ScriptorPlugin
{
    public function name(): string
    {
        return 'bigins/scriptor-simple-router';
    }

    public function version(): string
    {
        return '0.0.1';
    }

    public function register(PluginContext $context): void
    {
        // Phase 2 leaves register() empty on purpose. Chapter 3
        // adds the Router singleton; chapter 5 hooks _ext.php.
        error_log('[scriptor-simple-router] plugin registered (no-op)');
    }
}