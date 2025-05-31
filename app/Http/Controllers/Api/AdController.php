<?php

namespace App\Http\Controllers\Api;

use App\Enums\AdStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ad\AdStoreRequest;
use App\Http\Requests\Ad\AdUpdateRequest;
use App\Jobs\SendMailJob;
use App\Models\Ad;
use App\Models\Image;
use App\Services\MediaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $ads = Cache::remember("active_ads", 3600, function () {
                return Ad::active()
                        ->withCount("reviews")
                        ->with("reviews")
                        ->with("image")
                        ->with("category")
                        ->with("user")
                        ->paginate(10)
                        ->get();
            });
            return $this->success([
                "ads" => $ads
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to get ads", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdStoreRequest $request)
    {
        try {
            $image = new Image([
                "path" => "images/" . $request->file("image")->hashName()
            ]);
            MediaService::uploadImage($request->file("image"));

            DB::beginTransaction();
            // status is pending by default
            $ad = Ad::create($request->safe()->except(["image"]));
            $ad->image()->save($image);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            MediaService::removeImage("images/" . $request->file("image")->hashName());
            return $this->error("unable to store ad", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (FileException $e) {
            return $this->error("unable to store ad", 500, [
                "errors" => [
                    "file-error" => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $ad = Cache::remember("ad_" . $id, 1800, function () use ($id) {
                return Ad::withCount("reviews")
                        ->with("reviews")
                        ->with("image")
                        ->with("category")
                        ->with("user")
                        ->findOrFail($id);
            });
            return $this->success([
                "ad" => $ad
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to get ad", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        }  catch (ModelNotFoundException $e) {
            return $this->error("Ad not found", 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdUpdateRequest $request, int $id)
    {
        try {
            $ad = Ad::findOrFail($id);
            $ad->update($request->safe()->except(["image"]));

            if ($request->hasFile("image")) {
                $oldImage = $ad->image;
                $filename = $request->file("image")->hashName();
                $image = new Image([
                    "path" => "images/" . $filename,
                ]);
                MediaService::uploadImage($request->file("image"));
                MediaService::removeImage($oldImage->path);

                DB::beginTransaction();
                $ad->image()->save($image);
                $oldImage->delete();
                DB::commit();
            }
            if ($ad->isDirty()) 
                Cache::forget("ad_" . $id);

            return $this->success([
                "ad" => $ad
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("ad not found", 404);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error("unable to update ad", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (FileException $e) {
            DB::rollBack();
            return $this->error("unable to update ad image", 500, [
                "errors" => [
                    "file-error" => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            Gate::authorize("delete", Ad::class);
            $ad = Ad::findOrFail($id);
            MediaService::removeImage($ad->image->path);
            $ad->image()->delete();
            $ad->delete();
            Cache::forget("ad_" . $id);
            return $this->success(["ad" => $ad]);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error("unable to delete ad", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("ad not found", 404);
        } catch (FileException $e) {
            DB::rollBack();
            return $this->error("unable to delete ad image", 500, [
                "errors" => [
                    "file-error" => $e->getMessage()
                ]
            ]);
        } catch (AuthorizationException $e) {
            return $this->error("Can't perform this action.", 401);
        }
    }

    public function updateStatus(Request $request, int $id) {
        $validated = $request->validate([
            "status" => ["required", "string", Rule::enum(AdStatusEnum::class)]
        ]);

        try {
            Gate::authorize("updateStatus", Ad::class);
            $ad = Ad::findOrFail($id);
            $ad->update($validated);
            Cache::forget("ad_" . $id);

            if ($validated["status"] == "active") {
                Cache::forget("active_ads");
                SendMailJob::dispatch($ad->user);
            }

            if ($validated["status"] == "rejected") {
                Cache::forget("active_ads");
                SendMailJob::dispatch($ad->user, "email-rejection");
            }

            return $this->success([
                "ad" => $ad
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to update ad status", 500, [
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
}
