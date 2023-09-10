<?php

namespace App\Http\Controllers\Api\v1\Adverts;

use App\Http\Controllers\Controller;
use App\Http\Resources\Adverts\CategoryResource;
use App\Models\Adverts\Category;
use App\UseCases\Adverts\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Method index
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $categories = $this->service->getCategoriesTree();
        return CategoryResource::collection($categories);
    }

    public function show(Request $request, Category $category)
    {
        return new CategoryResource($this->service->getCategoryTree($category));
    }

    public function findByPath(Request $request, $path)
    {
        return new CategoryResource($path);
    }
}
