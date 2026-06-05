<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RequestIdMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $requestId = $request->header(
            'X-Request-Id',
            (string) Str::uuid()
        );

        $request->attributes->set(
            'request_id',
            $requestId
        );

        $response = $next($request);

        Log::info('Request completed', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
        ]);

        $response->headers->set(
            'X-Request-Id',
            $requestId
        );

        return $response;
    }
}
