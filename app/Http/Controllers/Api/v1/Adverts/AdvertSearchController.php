<?php

namespace App\Http\Controllers\Api\v1\Adverts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Adverts\SearchRequest;
use App\Http\Resources\Adverts\AdvertResource;
use App\Models\Adverts\Category;
use App\UseCases\Adverts\AdvertSearchService;

class AdvertSearchController extends Controller
{
    private $service;

    public function __construct(AdvertSearchService $service)
    {
        $this->service = $service;
    }

    public function search(SearchRequest $request, ?Category $category)
    {
        $adverts = $this->service->search($request, $category);

        return AdvertResource::collection($adverts);
    }
}
