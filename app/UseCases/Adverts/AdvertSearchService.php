<?php

namespace App\UseCases\Adverts;

use App\Http\Requests\Adverts\SearchRequest;
use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use MeiliSearch\Endpoints\Indexes;

class AdvertSearchService
{
    public function search(SearchRequest $request, ?Category $category)
    {
        if ($request->search) {
            $query = Advert::search('', function (Indexes $meiliSearch, $query, $options) use ($request) {
                return $meiliSearch->search(
                    $request->search,
                    [
                        'filter' => 'property_values.15 = "one"'
                        //'facets' => ['category_id']
                    ]
                );
            });
        } else {
            $query = Advert::query();
        }

        // $adverts = Advert::search('', function (Indexes $meiliSearch, $query, $options) {
        //     return $meiliSearch->search(
        //         null,
        //         [
        //             //'filter' => 'property_values.25 = 1'
        //             //'facets' => ['category_id']
        //         ]
        //     );
        // })->get();

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($category->id) {
            $query->whereIn('category_id', array_merge(
                [$category->id],
                $category->descendants()->pluck('id')->toArray()
            ));
        }

        if ($request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        $adverts = $query->get();

        return $adverts;
    }
}
