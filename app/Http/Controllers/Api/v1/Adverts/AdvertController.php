<?php

namespace App\Http\Controllers\Api\v1\Adverts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Adverts\SearchRequest;
use App\Http\Resources\Adverts\AdvertResource;
use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\Models\User;
use App\UseCases\Adverts\AdvertSearchService;
use Exception;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class AdvertController extends Controller
{
    private $searchService;

    public function __construct(AdvertSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Method index
     *
     * @param SearchRequest $request 
     * @param ?Category $category 
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(SearchRequest $request, ?Category $category): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $statuses = [Advert::STATUS_ACTIVE];
        $user = null;
        $adverts = $this->searchService->search($request, $category, $statuses, $user);

        return AdvertResource::collection($adverts);
    }

    /**
     * Method userAdverts
     *
     * @param SearchRequest $request 
     * @param User $user 
     * @param ?Category $category 
     * @return Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function userAdverts(SearchRequest $request, User $user, ?Category $category): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $statuses = [Advert::STATUS_ACTIVE, Advert::STATUS_CLOSED];
        $adverts = $this->searchService->search($request, $category, $statuses, $user);
        return AdvertResource::collection($adverts);
    }

    /**
     * Method show
     *
     * @param Request $request 
     * @param Advert $advert 
     *
     * @return App\Http\Resources\Adverts\AdvertResource
     */
    public function show(Request $request, Advert $advert): \App\Http\Resources\Adverts\AdvertResource | Response
    {
        $authUser = $request->user();

        if (!$advert->isAvailableToShow($authUser)) {
            return response('This advert is not found', Response::HTTP_NOT_FOUND);
        }

        return new AdvertResource($advert);
    }
}
