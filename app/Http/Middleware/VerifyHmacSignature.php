<?php

namespace App\Http\Middleware;

use App\Services\HmacService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacSignature
{
    public function __construct(protected HmacService $hmacService) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Internal-Signature');
        $secret = config('services.internal.secret', env('INTERNAL_SERVICE_SECRET'));

        if (! $signature || ! $this->hmacService->verifySignature($request->getContent(), $signature, (string) $secret)) {
            return response()->json([
                'error' => 'Invalid internal signature',
                'message' => 'This endpoint requires a valid HMAC-SHA256 signature.',
            ], 401);
        }

        return $next($request);
    }
}
