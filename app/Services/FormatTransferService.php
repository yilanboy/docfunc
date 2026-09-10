<?php

declare(strict_types=1);

namespace App\Services;

class FormatTransferService
{
    /**
     * 將 tag 的 JSON 資料轉成 array
     * ex. [{"id":1,"name":"PHP"},{"id":2,"name":"Laravel"}]
     *
     * @return array<int, int|string>
     */
    public function tagsJsonToTagIdsArray(?string $tagsJson = null): array
    {
        // 沒有設定標籤
        if (is_null($tagsJson)) {
            return [];
        }

        /** @var array<int, object{id: int|string}>|null $tags */
        $tags = json_decode($tagsJson);

        if (! is_array($tags)) {
            return [];
        }

        // 生成由 tag ID 組成的 Array
        return collect($tags)
            ->map(fn (object $tag): int|string => $tag->id)
            ->all();
    }
}
