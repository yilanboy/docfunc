<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $link_with_name 帶有 name slug 的分類連結，set by linkWithName()
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name', 'icon', 'description', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * 將連結加上分類名稱
     *
     * @return Attribute<string, never>
     */
    protected function linkWithName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => route('categories.show', [
                'id'   => $this->id,
                'name' => $this->name,
            ])
        );
    }
}
