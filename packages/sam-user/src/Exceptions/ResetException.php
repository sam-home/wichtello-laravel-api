<?php

namespace Sam\User\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ResetException extends Exception {
    public function report(): bool
    {
        return true;
    }

    /**
     * @return Response
     */
    public function render(): Response
    {
        return response([
            'error' => 'Der Link zum Ändern des Passworts ist ungültig'
        ]);
    }
}
