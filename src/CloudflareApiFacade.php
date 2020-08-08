<?php

namespace LCloss\CloudflareApi;

use Illuminate\Support\Facades\Facade;

class CloudflareApiFacade extends Facade {
    protected static function getFacadeAccessor() {
        return 'CloudflareAPI';
    }
}