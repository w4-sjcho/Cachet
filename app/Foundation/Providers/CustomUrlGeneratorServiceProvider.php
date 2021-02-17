<?php

namespace CachetHQ\Cachet\Foundation\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CustomUrlGenerator extends UrlGenerator {

    // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Routing/UrlGenerator.php#L232
    public function asset($path, $secure = null) {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        // Once we get the root URL, we will check to see if it contains an index.php
        // file in the paths. If it does, we will remove it since it is not needed
        // for asset paths, but only for routes to endpoints in the application.
        $assetRoot = config('app.asset_url');
        $root = $assetRoot
                    ? $assetRoot
                    : $this->getRootUrl($this->getScheme($secure));

        return $this->removeIndex($root).'/'.trim($path, '/');
    }
}

// https://stackoverflow.com/a/65362285
class CustomUrlGeneratorServiceProvider extends ServiceProvider {

    public function register() {
        $this->app->singleton('url', function($app) {
            $routes = $app->router->getRoutes();
            return new CustomUrlGenerator(
                $routes,
                $app->rebinding('request', $this->requestRebinder()));
        });
    }

    protected function requestRebinder() {
        return function($app, $request) {
            $app->url->setRequest($request);
        };
    }
}
