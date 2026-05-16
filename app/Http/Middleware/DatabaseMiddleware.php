<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DatabaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $mode = env('APP_MODE', 'cloud'); // cloud or local

        if ($mode === 'local') {
            Config::set('database.default', 'sqlite');
        } else {
            Config::set('database.default', 'mysql');
        }

        // Force connection refresh
        DB::purge();
        DB::reconnect();

        return $next($request);
    }
}
