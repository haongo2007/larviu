<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CatalogCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'active' => $this->is_active,
            'order' => $this->order,
            'created_at' => $this->order,
            'creator' => $this->Creator->name,
            'name_parent' => $this->Child,
        ];
    }
}
