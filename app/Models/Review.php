<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        "user_id",
        "ad_id",
        "comment",
        "rating",
    ];

    public function user() : BelongsTo {
        return $this->belongsTo(User::class, "user_id");
    }

    public function ad() : BelongsTo {
        return $this->belongsTo(Ad::class, "ad_id");
    }


    //scopes
    #[Scope]
    protected function positive(Builder $query) {
        $query->where('rating', '>=', 4);
    }

    #[Scope]
    protected function recent(Builder $query) {
        $query->where('created_at', '>=', now()->subDays(7));
    }

    //accessors
    public function getStarRatingAttribute() {
        return str_repeat("⭐", $this->rating);
    }
}
