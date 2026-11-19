<?php

namespace App\Http\Middleware;

use Closure;

class EnsureBusinessFeatureEnabled
{
    public function handle($request, Closure $next, $feature)
    {
        $features = $request->session()->get('business_type_features');
        if (! is_null($features) && ! data_get($features, $feature.'.is_enabled', true)) {
            abort(404);
        }

        return $next($request);
    }
}
