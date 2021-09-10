<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'title', 'body', 'category_id', 'excerpt', 'slug',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 定義與標籤的關聯
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    // 文章排序
    public function scopeWithOrder($query, ?string $order)
    {
        return $query->when($order, function ($query, $order) {
            switch ($order) {
                case 'recent':
                    return $query->orderBy('updated_at', 'desc');
                    break;
                case 'comment':
                    return $query->orderBy('comment_count', 'desc');
                    break;
                default:
                    return $query->latest();
            }
        });
    }

    // 將連結加上文章的 slug
    public function getLinkWithSlugAttribute()
    {
        return route('posts.show', [
            'post' => $this->id,
            'slug' => $this->slug,
        ]);
    }

    // 更新留言數量
    public function updateCommentCount(): void
    {
        // 使用鎖表來更新 Model 中的 comment_count 資料
        $this->comment_count = Comment::where('post_id', $this->id)->lockForUpdate()->count();
        // 更新資料庫資料
        $this->save();
    }

    // 設定 Algolia 匯入的 index 名稱
    public function searchableAs()
    {
        return config('scout.prefix');
    }

    // 調整匯入 Algolia 的 Model 資料
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Applies Scout Extended default transformations:
        $array = $this->transform($array);

        $array['author_name'] = $this->user->name;
        $array['url'] = $this->getLinkWithSlugAttribute();

        return $array;
    }
}
