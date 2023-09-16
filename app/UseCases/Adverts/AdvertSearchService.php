<?php

namespace App\UseCases\Adverts;

use App\Http\Requests\Adverts\SearchRequest;
use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\Models\User;
use MeiliSearch\Endpoints\Indexes;

class AdvertSearchService
{
    public function search(SearchRequest $request, ?Category $category, ?array $statuses, ?User $user)
    {
        $filters = $this->getFilters(
            $request->properties ?? null,
            $request->location ?? null,
            $request->geo ?? null,
            $category,
            $statuses,
            $user
        );

        $perPage = $request->per_page ? (int)$request->per_page : 12;


        if ($request->search or $filters) {
            $query = Advert::search('', function (Indexes $meiliSearch, $query) use ($request, $filters,  $perPage) {
                $options = [];

                if ($filters) {
                    $options['filter'] = $filters;
                }

                $options['page'] = $request->page ? (int)$request->page : 1;

                $options['hitsPerPage'] = $perPage;

                return $meiliSearch->search(
                    $request->search,
                    $options
                );
            });
        } else {
            $query = Advert::query()->with(['user']);
        }


        // if ($category->id) {
        //     $query->whereIn('category_id', array_merge(
        //         [$category->id],
        //         $category->descendants()->pluck('id')->toArray()
        //     ));
        // }

        // if ($statuses) {
        //     $query->whereIn('status', $statuses);
        // }

        // if ($user) {
        //     $query->where('user_id', $user->id);
        // }



        $adverts = $query->paginate($perPage);

        return $adverts;
    }

    private function getFilters(?array $properties, ?array $location, ?array $geo, ?Category $category, ?array $statuses, ?User $user): string|bool
    {
        $filters = [];
        $stringFilters = '';

        if ($categoryFilters = $this->getCategoryFilters($category)) {
            $filters[] = $categoryFilters;
        }

        if ($statusesFilters = $this->getStatusesFilters($statuses)) {
            $filters[] = $statusesFilters;
        }

        if ($locationFilters = $this->getLocationFilters($location)) {
            $filters[] = $locationFilters;
        }

        if ($geoFilters = $this->getGeoFilters($geo)) {
            $filters[] = $geoFilters;
        }

        if ($propertyFilters = $this->getPropertyFilters($properties)) {
            $filters[] = $propertyFilters;
        }

        if (count($filters) == 0) {
            return false;
        }

        $i = 0;
        foreach ($filters as $filter) {
            $stringFilters .= $filter;

            if ($i < count($filters) - 1) {
                $stringFilters .= ' AND ';
            }

            $i++;
        }

        return $stringFilters;
    }


    private function getCategoryFilters(?Category $category)
    {
        if ($category->id) {
            $categories = array_merge([$category->id], $category->descendants()->pluck('id')->toArray());

            if (count($categories) == 0) {
                return false;
            }

            $text = '[' . implode(',', $categories) . ']';
            $filters = 'category_id IN ' .  $text;
            return $filters;
        }

        return false;
    }

    private function getStatusesFilters(?array $statuses)
    {
        if ($statuses) {
            if (count($statuses) == 0) {
                return false;
            }

            $text = '[' . implode(',', $statuses) . ']';
            $filters = 'status IN ' .  $text;
            return $filters;
        }

        return false;
    }

    private function getUserFilter(?User $user): string
    {
        if ($user) {
            return 'user_id = ' . $user->id;
        }

        return false;
    }


    /**
     * Method getGeoFilters
     *
     * @param ?array $geo
     *
     * @return string|bool
     */
    private function getGeoFilters(?array $geo): string|bool
    {
        if ($geo) {
            return '_geoRadius(' . $geo['latitude'] . ', ' . $geo['longitude'] . ', ' . $geo['radius'] . ')';
        }

        return false;
    }

    /**
     * Method getLocationFilters
     *
     * @param ?array $location
     *
     * @return string|bool
     */
    private function getLocationFilters(?array $location): string|bool
    {
        $filters = [];
        $stringFilters = '';

        if (!$location) {
            return false;
        }

        if (isset($location['country_id'])) {
            $filters[] = 'country_id = ' . $location['country_id'];
        }

        if (isset($location['division_id'])) {
            $filters[] = 'division_id = ' . $location['division_id'];
        }

        if (isset($location['city_id'])) {
            $filters[] = 'city_id = ' . $location['city_id'];
        }

        $i = 0;
        foreach ($filters as $filter) {
            $stringFilters .= $filter;

            if ($i < count($filters) - 1) {
                $stringFilters .= ' AND ';
            }

            $i++;
        }
        return $stringFilters;
    }

    /**
     * Method getPropertyFilters
     *
     * @param ?array $properties 
     *
     * @return string|bool
     */
    private function getPropertyFilters(?array $properties): string|bool
    {
        if ($properties) {
            $i = 0;
            $filters = '';
            foreach ($properties as $propertyId => $rules) {
                if (isset($rules['equals'])) {
                    $filters .= $this->getEqualsFilter($propertyId, $rules['equals']);
                } elseif (isset($rules['range'])) {
                    $filters .= $this->getRangeFilter(
                        $propertyId,
                        $rules['range']['min'] ?? null,
                        $rules['range']['max'] ?? null,
                        $rules['range']['strict'] ?? false
                    );
                } elseif (isset($rules['select'])) {
                    $filters .= $this->getEqualsFilter($propertyId, $rules['select']);
                } elseif (isset($rules['multiselect'])) {
                    $filters .= $this->getInFilter($propertyId, $rules['multiselect']);
                }

                if ($i < count($properties) - 1) {
                    $filters .= ' AND ';
                }
                $i++;
            }
            return $filters;
        }
        return false;
    }

    /**
     * Method getEqualsFilter
     *
     * @param $propertyId 
     * @param $value $value 
     * @return string
     */
    private function getEqualsFilter($propertyId, $value): string
    {
        return 'property_' . $propertyId . ' = ' . $value;
    }

    /**
     * Method getRangeFilter
     *
     * @param $propertyId 
     * @param $min $min 
     * @param $max $max 
     * @param $strict 
     *
     * @return string
     */
    private function getRangeFilter($propertyId, $min, $max, bool $strict): string
    {
        $filters = '';
        $and = '';
        if (isset($min)) {
            $and = ' AND ';
            if (isset($strict) and $strict === true) {
                $filters .= 'property_' . $propertyId . ' > ' . $min;
            } else {
                $filters .= 'property_' . $propertyId . ' >= ' . $min;
            }
        }

        if (isset($max)) {
            if (isset($strict) and $strict === true) {
                $filters .= $and . 'property_' . $propertyId . ' < ' . $max;
            } else {
                $filters .= $and . 'property_' . $propertyId . ' <= ' . $max;
            }
        }

        return $filters;
    }

    /**
     * Method getInFilter
     *
     * @param $propertyId 
     * @param array $values 
     * @return string
     */
    private function getInFilter($propertyId, array $values): string
    {
        $stringValues = '[';
        $i = 0;
        foreach ($values as $value) {
            $stringValues .= $value;
            if ($i != count($values) - 1) {
                $stringValues .= ', ';
            }
            $i++;
        }
        $stringValues .= ']';
        $filters = 'property_' . $propertyId . ' IN ' . $stringValues;

        return $filters;
    }
}
