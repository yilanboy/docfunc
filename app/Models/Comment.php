<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property Post $post
 * @property User $user
 * @property Comment $parent
 * @property Collection<int, Comment> $children
 * @property object{level: int, parent_count: int} $hierarchy
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'body', 'parent_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }

    public function hierarchy(): Attribute
    {
        $query = <<<'SQL'
        WITH RECURSIVE CommentHierarchy AS (
            SELECT
                id,
                parent_id,
                1 AS level_count
            FROM
                comments
            WHERE
                id = :id

            UNION ALL

            SELECT
                c.id,
                c.parent_id,
                ch.level_count + 1
            FROM
                comments c
            INNER JOIN
                CommentHierarchy ch ON c.id = ch.parent_id
        )
        SELECT
            MAX(level_count) AS level,
            COUNT(*) - 1 AS parent_count
        FROM
            CommentHierarchy
        SQL;

        return new Attribute(
            get: fn ($value) => Arr::first(
                DB::select($query, ['id' => $this->id])
            )
        )->shouldCache();
    }
}
