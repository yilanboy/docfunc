<?php

namespace App\Models;

use App\Notifications\NewComment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property DatabaseNotificationCollection $unreadNotifications
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'introduction',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function isAuthorOf(Post|Comment $model): bool
    {
        return $this->id === $model->user_id;
    }

    public function notifyNewComment(NewComment $instance): void
    {
        // if the author of the comment is the same as the author of the post, don't notify
        if ($this->id === auth()->id()) {
            return;
        }

        $this->notify($instance);
    }

    public function gravatarUrl(): Attribute
    {
        return new Attribute(
            get: fn($value) => get_gravatar(email: $this->email, size: 512)
        )->shouldCache();
    }

    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class);
    }
}
