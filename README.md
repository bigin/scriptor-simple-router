# scriptor-simple-router

A tiny URL router that runs ahead of [Scriptor](https://scriptor-cms.dev)'s
page resolver. Lets you declare JSON endpoints, webhook receivers,
and any other non-page-shaped URLs from a routes file, without
faking Pages.

## Installation

This package is not on Packagist, so tell Composer where to find it
with a one-time `repositories` entry, then require it:

```bash
composer config repositories.scriptor-simple-router \
  vcs https://github.com/bigin/scriptor-simple-router
composer require bigins/scriptor-simple-router:^0.1
```

The first command adds a VCS repository to your `composer.json`;
without it `composer require` reports *"Could not find a version of
package …"*. Scriptor ships a clean `composer.json` with no plugin
repositories declared, so this step is required when installing into
Scriptor too.

In Docker, supply the repo URL and the package spec through the
`SCRIPTOR_PLUGIN_REPOS` and `SCRIPTOR_PLUGINS` build args instead
(see Scriptor's install docs).

The plugin auto-registers via Composer's `installed.json`
(Scriptor reads `type: scriptor-plugin` packages at boot). One
extra line in your theme's `_ext.php` activates the router:

```php
if (\Bigins\ScriptorSimpleRouter\Router::handle()) return;
```

## Usage

Declare routes in a `routes.php` next to your theme's `_ext.php`:

```php
use Bigins\ScriptorSimpleRouter\Request;
use Bigins\ScriptorSimpleRouter\Response;
use Bigins\ScriptorSimpleRouter\Router;

$router = Router::instance();

$router->get('/api/users/{id}', fn(Request $req) => Response::json([
    'id' => (int) $req->param('id'),
]));

$router->post('/webhook/stripe', StripeWebhookController::class);
```

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