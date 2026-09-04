<?php

namespace Modules\Demo5\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 评论资源
 *
 * 单个评论的 API 响应格式化
 */
class CommentResource extends JsonResource
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
            'content' => $this->content,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}