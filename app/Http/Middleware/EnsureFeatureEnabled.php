<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureFeatureEnabled
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(match ($feature) {
            'teams' => Features::teams(),
            default => false,
        }, 404);

        return $next($request);
    }
}
