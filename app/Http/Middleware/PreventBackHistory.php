<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PreventBackHistory
{
    // Logged-in pages browser/bfcache mein cache na hon, taake logout ke baad Back par dashboard na dikhe
    public function handle(Request $request, Closure $next): Response
    {
        // Guest check request ke start par (logout ke baad user null ho jata hai)
        $wasAuthenticated = $request->user() !== null;

        $response = $next($request);

        // Guest pages, file/stream responses aur downloads (Content-Disposition) ko na chhedo
        if (! $wasAuthenticated
            || $response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse
            || $response->headers->has('Content-Disposition')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
