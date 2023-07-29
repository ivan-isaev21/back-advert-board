<?php

namespace App\UseCases\Adverts;

use App\Events\Adverts\Closed;
use App\Events\Adverts\Created;
use App\Events\Adverts\Deleted;
use App\Events\Adverts\SendToModeration;
use App\Events\Adverts\Updated;
use App\Http\Requests\Adverts\CreateRequest;
use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\Models\Geo\City;
use App\Models\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\DB;

class AdvertService
{
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Method create
     *
     * @param CreateRequest $request 
     * @param Category $category 
     * @param User $user 
     * @return Advert
     */
    public function create(CreateRequest $request, Category $category,  User $user): Advert
    {
        $geo = $this->getGeo($request);

        $advertCreated = DB::transaction(function () use ($request, $category, $user, $geo) {

            $advert = new Advert();
            $advert->category_id = $category->id;
            $advert->user_id = $user->id;
            $advert->country_id = $request->location['country_id'] ?? null;
            $advert->division_id = $request->location['division_id'] ?? null;
            $advert->city_id = $request->location['city_id'] ?? null;
            $advert->latitude = $geo['latitude'];
            $advert->longitude = $geo['longitude'];
            $advert->title = $request->title;
            $advert->content = $request->content;
            $advert->status = Advert::STATUS_DRAFT;
            $advert->save();

            $this->createPropertyValues($request, $category, $advert);
            $this->dispatcher->dispatch(new Created($advert));

            return $advert;
        });

        return $advertCreated;
    }

    /**
     * Method update
     *
     * @param CreateRequest $request 
     * @param Category $category 
     * @param Advert $advert 
     *
     * @return Advert
     */
    public function update(CreateRequest $request, Category $category, Advert $advert): Advert
    {
        $geo = $this->getGeo($request);

        $advertUpdated = DB::transaction(function () use ($request, $category, $advert, $geo) {
            $advert->category_id = $category->id;
            $advert->country_id = $request->location['country_id'] ?? null;
            $advert->division_id = $request->location['division_id'] ?? null;
            $advert->city_id = $request->location['city_id'] ?? null;
            $advert->latitude = $geo['latitude'];
            $advert->longitude = $geo['longitude'];
            $advert->title = $request->title;
            $advert->content = $request->content;
            $advert->save();

            $advert->clearAllPropertyValues();
            $this->createPropertyValues($request, $category, $advert);
            $this->dispatcher->dispatch(new Updated($advert));

            return $advert;
        });

        return $advertUpdated;
    }

    /**
     * Method delete
     *
     * @param Advert $advert 
     *
     * @return void
     */
    public function delete(Advert $advert): void
    {
        DB::transaction(function () use ($advert) {
            $advert->clearAllPropertyValues();
            $advert->delete();
            $this->dispatcher->dispatch(new Deleted($advert));
        });
    }

    /**
     * Method sendToModeration
     *
     * @param Advert $advert 
     *
     * @return void
     */
    public function sendToModeration(Advert $advert): void
    {
        DB::transaction(function () use ($advert) {
            $advert->sendToModeration();
            $this->dispatcher->dispatch(new SendToModeration($advert));
        });
    }

    public function close(Advert $advert): void
    {
        DB::transaction(function () use ($advert) {
            $advert->close();
            $this->dispatcher->dispatch(new Closed($advert));
        });
    }

    /**
     * Method createPropertyValues
     *
     * @param CreateRequest $request 
     * @param Category $category 
     * @param Advert $advert 
     * @return void
     */
    private function createPropertyValues(CreateRequest $request, Category $category, Advert $advert): void
    {
        foreach ($category->allProperties() as $property) {
            $value = $request->properties[$property->id] ?? null;

            if ($value !== null) {
                Advert::addValue($advert, $property, $value);
            }
        }
    }

    /**
     * Method getGeo
     *
     * @param CreateRequest $request 
     *
     * @return array
     */
    private function getGeo(CreateRequest $request): array
    {
        $latitude = null;
        $longitude = null;

        if (isset($request->geo)) {
            $latitude = $request->geo['latitude'];
            $longitude = $request->geo['longitude'];
        } elseif (isset($request->location['city_id'])) {
            $city = City::findOrFail($request->location['city_id']);
            $latitude = $city->latitude;
            $longitude = $city->longitude;
        }

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }
}
