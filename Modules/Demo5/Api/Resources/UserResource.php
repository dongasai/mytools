<?php

namespace Modules\Demo5\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 用户资源
 *
 * 单个用户的 API 响应格式化
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'last_login_at' => $this->last_login_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'posts_count' => $this->posts->count(),
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}