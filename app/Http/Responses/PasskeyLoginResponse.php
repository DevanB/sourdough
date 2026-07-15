<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Actions\ResolvePostLoginDestination;
use Illuminate\Http\JsonResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

final readonly class PasskeyLoginResponse implements PasskeyLoginResponseContract
{
    public function __construct(private ResolvePostLoginDestination $resolver) {}

    public function toResponse($request): Response
    {
        $user = $request->user();
        $fallback = $user !== null
            ? $this->resolver->handle($user)
            : route('dashboard', absolute: false);

        $redirect = redirect()->intended($fallback);

        if ($request->wantsJson()) {
            return new JsonResponse([
                'redirect' => $redirect->getTargetUrl(),
            ], 200);
        }

        return $redirect;
    }
}
