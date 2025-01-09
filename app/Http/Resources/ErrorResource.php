<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'code' => $this->resource['code'] ?? 400,
            'status' => 'error',
            'errors' => $this->resource['errors'] ?? null,
            'message' => $this->resource['message'] ?? 'An error occurred',
        ];
    }
}
