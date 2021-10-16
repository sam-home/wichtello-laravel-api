<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @return User|null
     */
    protected function getAuthenticatedUser() {
        return auth()->user();
    }

    /**
     * @param $data
     * @param int $status
     * @return JsonResponse
     */
    public function json($data, $status = 200)
    {
        return response()->json($data, $status, [], JSON_PRETTY_PRINT);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function success($data = [])
    {
        return $this->json(array_merge($data, ['success' => true]), 200);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function error($data = [])
    {
        return $this->json(array_merge($data, ['success' => false]), 200);
    }
}
