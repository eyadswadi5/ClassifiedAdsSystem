<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = [
        "path"
    ];

    public function imageable() : MorphTo {
        return $this->morphTo();
    }

    //scopes
    #[Scope]
    protected function forAd(Builder $query) {
        $query->where("imageable_type", "App\Models\Ad");
    }

    #[Scope]
    protected function forUser(Builder $query) {
        $query->where("imageable_type", "App\Models\User");
    }
}
