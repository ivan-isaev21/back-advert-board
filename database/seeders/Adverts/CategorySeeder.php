<?php

namespace Database\Seeders\Adverts;

use App\Models\Adverts\Category;
use App\Models\Adverts\Property;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory(10)->create()->each(function (Category $category) {
            $counts = [0, random_int(3, 7)];
            $category->properties()->saveMany(Property::factory(rand(1, 5))->create(['category_id' => $category->id]));

            $category->children()
                ->saveMany(
                    Category::factory($counts[array_rand($counts)])
                        ->create()
                        ->each(function (Category $category) {
                            $counts = [0, random_int(3, 7)];
                            $category->children()
                                ->saveMany(Category::factory($counts[array_rand($counts)])
                                    ->create());
                        })
                );
        });
    }
}
