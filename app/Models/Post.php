<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostOrderOptions;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
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
 * @method int increment(string $column, float|int $amount = 1, array<string, mixed> $extra = []) 將該欄位值加 1
 * @method int decrement(string $column, float|int $amount = 1, array<string, mixed> $extra = []) 將該欄位值減 1
 */
class Post extends Model implements Feedable
{
    /** @use HasFactory<PostFactory> */
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
        'cover_image_url',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    protected $appends = ['link_with_slug'];

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    /**
     * Set the ordering of the post
     *
     * @param  Builder<Post>  $query
     */
    #[Scope]
    protected function withOrder(Builder $query, ?string $order): void
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
     * Set the prune rule of the post-data
     *
     * @return Builder<Post>
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subMonth());
    }

    /**
     * Use laravel mutator to set the slug attribute.
     *
     * @return Attribute<string, never>
     */
    protected function linkWithSlug(): Attribute
    {
        return new Attribute(
            get: fn ($value) => route('posts.show', [
                'id'   => $this->id,
                'slug' => $this->slug,
            ])
        );
    }

    /**
     * @return Attribute<string, never>
     */
    protected function tagsJson(): Attribute
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
    public function searchableAs(): string
    {
        return (string) config('scout.prefix');
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

    /**
     * @return Collection<int, Post>
     */
    public static function getFeedItems(): Collection
    {
        return Post::where('is_private', false)
            ->latest()
            ->take(10)
            ->get();
    }
}
