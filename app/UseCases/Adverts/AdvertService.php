<?php

namespace App\UseCases\Adverts;

use App\Events\Adverts\Closed;
use App\Events\Adverts\Created;
use App\Events\Adverts\Deleted;
use App\Events\Adverts\SendToModeration;
use App\Events\Adverts\Updated;
use App\Http\Requests\Adverts\CreateRequest;
use App\Http\Requests\Adverts\UpdateRequest;
use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\Models\Adverts\Image;
use App\Models\Geo\City;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;

class AdvertService
{
    private $dispatcher;
    private $imageService;

    public function __construct(Dispatcher $dispatcher, ImageService $imageService)
    {
        $this->dispatcher = $dispatcher;
        $this->imageService = $imageService;
    }

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

            $this->createImages($request, $advert);
            $this->createPropertyValues($request, $category, $advert);
            $this->dispatcher->dispatch(new Created($advert));

            return $advert;
        });

        return $advertCreated;
    }

    public function update(UpdateRequest $request, Category $category, Advert $advert): Advert
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

            $this->updateImages($request, $advert);
            $this->updatePropertyValues($request, $category, $advert);
            $this->dispatcher->dispatch(new Updated($advert));

            return $advert;
        });

        return $advertUpdated;
    }

    public function delete(Advert $advert): void
    {
        DB::transaction(function () use ($advert) {
            $this->clearImages($advert, []);
            $advert->clearAllPropertyValues();
            $advert->delete();
            $this->dispatcher->dispatch(new Deleted($advert));
        });
    }

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

    private function updateImages(CreateRequest|UpdateRequest $request, Advert $advert): void
    {
        $this->clearImages($advert, $request->images_to_exclude ?? []);
        $this->createImages($request, $advert);
    }

    private function createImages(CreateRequest|UpdateRequest $request, Advert $advert): void
    {
        if (isset($request->images)) {
            foreach ($request->images as $image) {
                $this->imageService->create($advert, $image['file'], $image['index']);
            }
        }
    }

    private function clearImages(Advert $advert, array $imagesToExclude): void
    {
        $advert->images()->each(function (Image $image) use ($imagesToExclude) {
            if (!in_array($image->index, $imagesToExclude)) {
                $this->imageService->delete($image);
            }
        });
    }

    private function updatePropertyValues(CreateRequest|UpdateRequest  $request, Category $category, Advert $advert): void
    {
        $advert->clearAllPropertyValues();
        $this->createPropertyValues($request, $category, $advert);
    }

    private function createPropertyValues(CreateRequest|UpdateRequest $request, Category $category, Advert $advert): void
    {
        foreach ($category->allProperties() as $property) {
            $value = $request->properties[$property->id] ?? null;

            if ($value !== null) {
                Advert::addValue($advert, $property, $value);
            }
        }
    }

    private function getGeo(CreateRequest|UpdateRequest $request): array
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
