# scriptor-simple-router

A tiny URL router that runs ahead of [Scriptor](https://scriptor-cms.dev)'s
page resolver. Lets you declare JSON endpoints, webhook receivers,
and any other non-page-shaped URLs from a routes file, without
faking Pages.

## Installation

`composer require` from a VCS repository pointing at this GitHub
repo (no Packagist publication required):

\`\`\`json
{
    "repositories": [
        {"type": "vcs", "url": "https://github.com/<you>/scriptor-simple-router"}
    ]
}
\`\`\`

\`\`\`bash
composer require bigins/scriptor-simple-router:^0.1
\`\`\`

The plugin auto-registers via Composer's `installed.json`
(Scriptor reads `type: scriptor-plugin` packages at boot). One
extra line in your theme's `_ext.php` activates the router:

\`\`\`php
if (\\Bigins\\ScriptorSimpleRouter\\Router::handle()) return;
\`\`\`

## Usage

Declare routes in a `routes.php` next to your theme's `_ext.php`:

\`\`\`php
use Bigins\\ScriptorSimpleRouter\\Request;
use Bigins\\ScriptorSimpleRouter\\Response;
use Bigins\\ScriptorSimpleRouter\\Router;

$router = Router::instance();

$router->get('/api/users/{id}', fn(Request $req) => Response::json([
    'id' => (int) $req->param('id'),
]));

$router->post('/webhook/stripe', StripeWebhookController::class);
\`\`\`

Handler shapes: Closure, controller class string (uses
`__invoke`), or `[Class, method]` array.

## What's not in scope

- Middleware, route groups, per-route DI.
- Auto-magical integration with every theme: routing requires one
  line in `_ext.php`.

Wrap [FastRoute](https://github.com/nikic/FastRoute) or
[league/route](https://route.thephpleague.com/) inside a Scriptor
plugin if you need richer features. The Scriptor Cookbook has the
recipe.

## Tutorial

This plugin is the artefact built across Build a Module in the
Scriptor Developer Guide. The tutorial walks through every line
of the source from scratch.

## License

MIT.