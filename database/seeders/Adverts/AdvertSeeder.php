<?php

namespace Database\Seeders\Adverts;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\Models\Adverts\Property;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdvertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();

        $users->each(function (User $user) use ($categories) {
            $category = fake()->randomElement($categories);
            Advert::factory(rand(1, 5))->create([
                'category_id' => $category->id,
                'user_id' => $user->id
            ])->each(function (Advert $advert) use ($category) {
                $properties = $category->allProperties();
                foreach ($properties as $property) {
                    if ($property->isRequired()) {
                        Advert::addValue(
                            $advert,
                            $property,
                            $this->getFakePropertyValue($property)
                        );
                    }
                }
            });
        });
    }

    /**
     * Method getFakePropertyValue
     *
     * @param Property $property 
     *
     * @return mixed
     */
    private function getFakePropertyValue(Property $property): mixed
    {
        if ($property->isString()) {
            return fake()->word();
        } elseif ($property->isInteger()) {
            return random_int(1, 100);
        } elseif ($property->isDecimal()) {
            return (float) random_int(1, 100);
        } elseif ($property->isBoolean()) {
            return fake()->boolean();
        } elseif ($property->isSelect()) {
            return fake()->randomElement([1 => 'one',  2 => 'two', 3 => 'three']);
        } elseif ($property->isMultiSelect()) {
            return fake()->randomElements([1 => 'one',  2 => 'two', 3 => 'three']);
        }
    }
}
