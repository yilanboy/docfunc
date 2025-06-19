<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id from tags model
 * @property string $name from tags model
 */
class TagResource extends JsonResource
{
    // Transform the resource collection into an array
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->name,
        ];
    }
}
