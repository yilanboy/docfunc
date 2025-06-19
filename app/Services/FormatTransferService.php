<?php

declare(strict_types=1);

namespace App\Services;

class FormatTransferService
{
    /**
     * 將 tag 的 JSON 資料轉成 array
     * ex. [{"id":1,"name":"PHP"},{"id":2,"name":"Laravel"}]
     */
    public function tagsJsonToTagIdsArray(?string $tagsJson = null): array
    {
        // 沒有設定標籤
        if (is_null($tagsJson)) {
            return [];
        }

        $tags = json_decode($tagsJson);

        // 生成由 tag ID 組成的 Array
        return collect($tags)
            ->map(fn ($tag) => $tag->id)
            ->all();
    }
}
