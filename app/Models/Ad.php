<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Ad extends Model
{
    protected $fillable = [
        "title",
        "description",
        "price",
        "user_id",
        "category_id",
        "status",
    ];

    #[Scope]
    protected function active(Builder $query) {
        $query->where("status", "=", "active");
    }

    #[Scope]
    protected function pending(Builder $query) {
        $query->where("status", "=", "pending");
    }

    #[Scope]
    protected function rejected(Builder $query) {
        $query->where("status", "=", "rejected");
    }

    #[Scope]
    protected function byUser(Builder $query, User $user) {
        $query->where("user_id", "=", $user->id);
    }

    #[Scope]
    protected function fromCategory(Builder $query, Category $category) {
        $query->where("category_id", "=", $category->id);
    }

    public function user() : BelongsTo {
        return $this->belongsTo(User::class, "user_id");
    }

    public function category() : BelongsTo {
        return $this->belongsTo(Category::class, "category_id");
    }

    public function image() : MorphOne {
        return $this->morphOne(Image::class, "imageable");
    }
    
    public function reviews() : HasMany {
        return $this->hasMany(Review::class, "ad_id");
    }

    //accessors
    public function getPriceAttribute($value) {
        return "$" . number_format($value, 2);
    }

    public function getUrlAttribute() {
        return asset("storage/" . $this->path);
    }

    //mutators
    public function setDescriptionAttribute($value) {
        $this->attributes["description"] = strip_tags($value);
    }
}
