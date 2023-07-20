<?php

namespace App\Http\Controllers\Api\v1\Adverts;

use App\Http\Controllers\Controller;
use App\Http\Resources\Adverts\AdvertResource;
use App\Http\Resources\Adverts\CategoryResource;
use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\Models\Adverts\Property;
use App\UseCases\Adverts\CategoryService;
use Illuminate\Http\Request;
use MeiliSearch\Endpoints\Indexes;

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

    // public function index()
    // {
    //     $adverts = Advert::search('', function (Indexes $meiliSearch, $query, $options) {
    //         return $meiliSearch->search(
    //             'Dr.',
    //             [
    //                 'filter' => 'property_values.25 = 1'
    //             ]
    //         );
    //     })->get();

    //     return $adverts;
    // }

    // public function index()
    // {
    //     $adverts = Advert::with(['category'])->get();
    //     return AdvertResource::collection($adverts);
    // }
    public function show(Request $request, Category $category)
    {
        return new CategoryResource($this->service->getCategoryTree($category));
    }
}
