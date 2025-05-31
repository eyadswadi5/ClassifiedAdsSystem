<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        "name",
        "slug"
    ];

    public function ads() : HasMany {
        return $this->hasMany(Ad::class, "category_id");
    }

    //scopes
    #[Scope]
    protected function popular(Builder $query) {
        $query->withCount("ads")->orderBy("ads_count", "desc");
    }

    //accessors
    public function getNameAttribute($value) {
        return ucwords($value);
    }

    //mutators
    public function setNameAttribute($value) {
        $this->attributes["name"] = $value;
        $this->attributes["slug"] = Str::slug($value);
    }
}
