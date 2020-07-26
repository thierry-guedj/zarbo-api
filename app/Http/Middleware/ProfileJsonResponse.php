<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;

class ProfileJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Check if debugbar is enabled
        if(! app()->bound('debugbar') || ! app('debugbar')->isEnabled())
        {
            return $response;
        }

        // Profile the json response
        if($response instanceof JsonResponse && $request->has('_debug'))
        {
            /* For all debug information
                $response->setData(array_merge($response->getData(true), [
                'debugbar' => app('debugbar')->getData(true)
            ])); */
            $response->setData(array_merge([
                'debugbar' => Arr::only(app('debugbar')->getData(), 'queries')
            ],
            $response->getData(true)));
        }

        return $response;
    }
}
