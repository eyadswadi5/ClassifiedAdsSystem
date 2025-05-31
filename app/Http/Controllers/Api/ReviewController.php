<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReviewStoreRequest;
use App\Http\Requests\Review\ReviewUpdateRequest;
use App\Models\Ad;
use App\Models\Review;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $ad_id)
    {
        try {
            Gate::authorize("viewAny", Review::class);
            $ad = Cache::remember("ad_" . $ad_id . "_reviews", 600, function () use ($ad_id) {
                return Ad::withCount("reviews")->with("reviews")->findOrFail($ad_id);
            });
            return $this->success([
                "ad" => $ad
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to get reviews", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("ad not found", 404);
        } catch (AuthorizationException $e) {
            return $this->error("Can't perform this action.", 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewStoreRequest $request, int $ad_id)
    {
        try {
            $ad = Ad::findOrFail($ad_id);
            $ad->reviews()->create($request->validated());
            Cache::forget("ad_" . $ad_id . "_reviews");
            return $this->success();
        } catch (QueryException $e) {
            return $this->error("unable to store review", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("ad not found", 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            Gate::authorize("view", Review::class);
            $review = Review::with("user")->findOrFail($id);
            return $this->success([
                "review" => $review
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to get review", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("review not found", 404);
        } catch (AuthorizationException $e) {
            return $this->error("Can't perform this action.", 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewUpdateRequest $request, int $id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->update($request->validated());
            Cache::forget("ad_" . $review->ad_id . "_reviews");

            return $this->success([
                "review" => $review
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to update review", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("review not found", 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            Gate::authorize("delete", Review::class);
            $review = Review::findOrFail($id);
            $review->delete();
            Cache::forget("ad_" . $review->ad_id . "_reviews");

            return $this->success([
                "review" => $review
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to delete review", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("review not found", 404);
        } catch (AuthorizationException $e) {
            return $this->error("Can't perform this action.", 401);
        }
    }
}
