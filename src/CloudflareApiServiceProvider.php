<?php

namespace LCloss\CloudflareApi;

use Illuminate\Support\ServiceProvider;

class CloudflareApiServiceProvider extends ServiceProvider {
    public function boot() {

    }

    public function register() {
        $this->app->bind('CloudflareAPI', function ($app) {
            return new CloudflareAPI();
        });
    }
}