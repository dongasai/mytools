<?php

namespace Modules\Demo5\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 文章资源
 *
 * 单个文章的 API 响应格式化
 */
class PostResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'user_id' => $this->user_id,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'comments_count' => $this->comments->count(),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}