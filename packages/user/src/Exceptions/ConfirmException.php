<?php

namespace User\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ConfirmException extends Exception {
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
            'error' => 'Der Aktivierungslink ist ungültig'
        ]);
    }
}
