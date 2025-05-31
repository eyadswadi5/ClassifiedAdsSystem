<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function image() : MorphOne {
        return $this->morphOne(Image::class, "imageable");
    }

    public function ads() : HasMany {
        return $this->hasMany(Ad::class, "user_id");
    }

    public function reviews() : HasMany {
        return $this->hasMany(Review::class, "user_id");
    }

    //scopes
    #[Scope]
    protected function admin(Builder $query) {
        $query->where("role", "admin");
    }

    #[Scope]
    protected function regular(Builder $query) {
        $query->where("role", "user");
    }


    //mutators
    public function setEmailAttribute($value) {
        $this->attributes["email"] = strtolower($value);
    }
    
    public function setPasswordAttribute($value) {
        $this->attributes["password"] = bcrypt($value);
    }

    //accessors
    public function getNameAttribute($value) {
        return ucwords($value);
    }

    public function getRoleLabelAttribute()     {
        return [
            "admin" => "Administrator",
            "user"  => "Regular User",
        ][$this->role] ?? $this->role;
    }
}
