<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "name"=>$this->name,
            "type"=>$this->type,
            "original"=>URL::to($this->path),
            "resize"=>URL::to($this->output_path),
            "album_id"=>$this->album_id,
            "created_at"=>$this->cretated_at,
            "current_user"=>$request->user()->email
        ];
    }
}
