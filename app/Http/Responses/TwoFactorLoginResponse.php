<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Actions\ResolvePostLoginDestination;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

final readonly class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    public function __construct(private ResolvePostLoginDestination $resolver) {}

    public function toResponse($request): Response
    {
        $user = $request->user();
        $fallback = $user !== null
            ? $this->resolver->handle($user)
            : route('dashboard', absolute: false);

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended($fallback);
    }
}
