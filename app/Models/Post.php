<?php

namespace App\Models;

use App\Enums\PostOrderOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

/**
 * @property string $link_with_slug 帶有 slug 的文章連結，set by linkWithSlug()
 * @property string $tags_json json 格式的標籤列表, set by tagsJson()
 * @property User $user
 * @property Collection<int, Tag> $tags
 *
 * @method int increment(string $column, float|int $amount = 1, array $extra = []) 將該欄位值加 1
 * @method int decrement(string $column, float|int $amount = 1, array $extra = []) 將該欄位值減 1
 */
class Post extends Model implements Feedable
{
    use HasFactory;
    use MassPrunable;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'is_private',
        'user_id',
        'category_id',
        'excerpt',
        'slug',
        'preview_url',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    /**
     * Set the ordering of the post
     */
    public function scopeWithOrder(Builder $query, ?string $order): void
    {
        $query->withCount('comments')
            ->when($order, function ($query, $order) {
                return match ($order) {
                    PostOrderOptions::RECENT->value => $query->orderBy('updated_at', 'desc'),
                    PostOrderOptions::COMMENT->value => $query->orderBy('comments_count', 'desc'),
                    default => $query->latest(),
                };
            });
    }

    /**
     * Set the prune rule of the post data
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonth());
    }

    /**
     * Use laravel mutator to set the slug attribute.
     */
    public function linkWithSlug(): Attribute
    {
        return new Attribute(
            get: fn ($value) => route('posts.show', [
                'id' => $this->id,
                'slug' => $this->slug,
            ])
        );
    }

    public function tagsJson(): Attribute
    {
        // 生成包含 tag ID 與 tag name 的 json 字串
        // [{"id":"2","value":"C#"},{"id":"5","value":"Dart"}]
        return new Attribute(
            get: fn ($value) => $this->tags
                ->map(fn (Tag $tag) => ['id' => $tag->id, 'value' => $tag->name])
                ->toJson()
        );
    }

    /**
     * Get the index name for the model.
     */
    public function searchableAs()
    {
        return config('scout.prefix');
    }

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id((string) $this->id)
            ->title($this->title)
            ->summary($this->excerpt)
            ->updated($this->updated_at)
            ->link($this->link_with_slug)
            ->authorName(config('app.name'));
    }

    public static function getFeedItems(): Collection
    {
        return Post::where('is_private', false)
            ->latest()
            ->take(10)
            ->get();
    }
}
