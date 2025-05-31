<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            Gate::authorize("viewAny", Category::class);
            $categories = Category::popular()->get();
            return $this->success([
                "categories" => $categories
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to get categories", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (AuthorizationException $e) {
            return $this->error("Can't perform this action.", 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        try {
            $category = Category::create($request->validated());
            return $this->success([
                "category" => $category
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to store category", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
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
            Gate::authorize("view", Category::class);
            $category = Category::withCount("ads")->findOrFail($id);
            return $this->success([
                "category" => $category
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to get category", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("category not found", 404);
        } catch (AuthorizationException $e) {
            return $this->error("Can't perform this action.", 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, int $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->name = $request->validated("name");
            $category->save();

            return $this->success([
                "category" => $category
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to update category", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("category not found", 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            Gate::authorize("delete", Category::class);
            $category = Category::findOrFail($id);
            $category->delete();

            return $this->success([
                "category" => $category
            ]);
        } catch (QueryException $e) {
            return $this->error("unable to delete category", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->error("category not found", 404);
        } catch (AuthorizationException $e) {
            return $this->error("Can't perform this action.", 401);
        }
    }
}
