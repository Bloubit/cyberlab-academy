<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && $request->isMethod('POST')) {
            \App\Models\UserActivityLog::log(
                $request->route()->getActionMethod() ?? $request->method(),
                null,
                [
                    'url' => $request->url(),
                    'method' => $request->method(),
                ]
            );
        }

        return $response;
    }
}
