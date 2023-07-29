<?php

namespace App\Http\Controllers\Api\v1\Adverts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Adverts\CreateRequest;
use App\Http\Requests\Adverts\SearchRequest;
use App\Http\Resources\Adverts\AdvertResource;
use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\UseCases\Adverts\AdvertSearchService;
use App\UseCases\Adverts\AdvertService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdvertController extends Controller
{
    private $service;
    private $searchService;

    public function __construct(AdvertService $service, AdvertSearchService $searchService)
    {
        $this->service = $service;
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
     * Method myAdverts
     *
     * @param SearchRequest $request 
     * @param ?Category $category 
     *
     * @return Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function myAdverts(SearchRequest $request, ?Category $category): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $statuses = $request->statuses ?? null;
        $user = $request->user();
        $adverts = $this->searchService->search($request, $category, $statuses, $user);

        return AdvertResource::collection($adverts);
    }

    /**
     * Method create
     *
     * @param CreateRequest $request 
     * @param Category $category 
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request, Category $category): \Illuminate\Http\Response
    {
        $user = $request->user();
        $advertCreated = $this->service->create($request, $category, $user);

        return response(new AdvertResource($advertCreated), Response::HTTP_CREATED);
    }

    /**
     * Method update
     *
     * @param CreateRequest $request 
     * @param Category $category 
     * @param Advert $advert 
     *
     * @return \Illuminate\Http\Response
     */
    public function update(CreateRequest $request, Category $category, Advert $advert): \Illuminate\Http\Response
    {
        $user = $request->user();

        if ($advert->user_id != $user->id) {
            throw new DomainException('This advert is not belong to this user.');
        }

        if (!$advert->isDraft()) {
            throw new DomainException('This advert is not draft.');
        }

        $advertUpdated = $this->service->update($request, $category, $advert);
        return response(new AdvertResource($advertUpdated), Response::HTTP_ACCEPTED);
    }

    /**
     * Method sendToModeration
     *
     * @param Request $request 
     * @param Category $category 
     * @param Advert $advert 
     *
     * @return \Illuminate\Http\Response
     */
    public function sendToModeration(Request $request, Category $category, Advert $advert): \Illuminate\Http\Response
    {
        $user = $request->user();

        if ($advert->user_id != $user->id) {
            throw new DomainException('This advert is not belong to this user.');
        }

        $this->service->sendToModeration($advert);

        return response(null, Response::HTTP_ACCEPTED);
    }

    /**
     * Method close
     *
     * @param Request $request 
     * @param Category $category 
     * @param Advert $advert 
     *
     * @return \Illuminate\Http\Response
     */
    public function close(Request $request, Category $category, Advert $advert): \Illuminate\Http\Response
    {
        $user = $request->user();

        if ($advert->user_id != $user->id) {
            throw new DomainException('This advert is not belong to this user.');
        }

        if ($advert->isDraft()) {
            throw new DomainException('This advert is draft.');
        }

        if ($advert->isClosed()) {
            throw new DomainException('This advert is already closed.');
        }

        $this->service->close($advert);

        return response(null, Response::HTTP_ACCEPTED);
    }

    /**
     * Method destroy
     *
     * @param Request $request 
     * @param Category $category
     * @param Advert $advert
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Category $category, Advert $advert): \Illuminate\Http\Response
    {
        $user = $request->user();

        if ($advert->user_id != $user->id) {
            throw new DomainException('This advert is not belong to this user.');
        }

        if (!$advert->isDraft() or !$advert->isClosed()) {
            throw new DomainException('This advert is not draft or closed.');
        }

        $this->service->delete($advert);
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
