<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->product->name,
            'price' => $this->product->price,
            'quantity' => $this->quantity,
        ];
    }
}
