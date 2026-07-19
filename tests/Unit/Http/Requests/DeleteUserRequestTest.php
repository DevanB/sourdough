<?php

declare(strict_types=1);

use App\Http\Requests\DeleteUserRequest;
use Illuminate\Support\Facades\Validator;

it('skips the shared team check when no user is authenticated', function (): void {
    $request = new DeleteUserRequest;
    $validator = Validator::make([], []);

    foreach ($request->after() as $callback) {
        $callback($validator);
    }

    expect($validator->errors()->isEmpty())->toBeTrue();
});
