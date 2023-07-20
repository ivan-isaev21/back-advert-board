<?php

namespace Database\Seeders\Adverts;

use App\Models\Adverts\Category;
use App\Models\Adverts\Property;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach ($this->getCategories() as $categoryTest) {
            $category = Category::factory()->create([
                'name' => $categoryTest['name'],
                'slug' => Str::slug($categoryTest['name'])
            ]);

            foreach ($categoryTest['properties'] as $categoryProperty) {
                $category->properties()->save(Property::factory()->create([
                    'category_id' => $category->id,
                    'name' => $categoryProperty['name'],
                    'slug' => Str::slug($categoryProperty['name']),
                    'frontend_type' => $categoryProperty['frontend_type'],
                    'variants' => $categoryProperty['variants']
                ]));
            }

            foreach ($categoryTest['subcategories']  as $subcategoryTest) {

                $subcategory = Category::factory()->create([
                    'name' => $subcategoryTest['name'],
                    'slug' => Str::slug($subcategoryTest['name']),
                    'parent_id' => $category->id
                ]);

                foreach ($subcategoryTest['properties'] as $subcategoryProperty) {
                    $subcategory->properties()->save(Property::factory()->create([
                        'category_id' => $subcategory->id,
                        'name' => $subcategoryProperty['name'],
                        'slug' => Str::slug($subcategoryProperty['name']),
                        'frontend_type' => $subcategoryProperty['frontend_type'],
                        'variants' => $subcategoryProperty['variants']
                    ]));
                }

                foreach ($subcategoryTest['subcategories']  as $subsubcategoryTest) {

                    $subsubcategory = Category::factory()->create([
                        'name' => $subsubcategoryTest['name'],
                        'slug' => Str::slug($subsubcategoryTest['name']),
                        'parent_id' => $subcategory->id
                    ]);

                    foreach ($subsubcategoryTest['properties'] as $subsubcategoryProperty) {
                        $subsubcategory->properties()->save(Property::factory()->create([
                            'category_id' => $subsubcategory->id,
                            'name' => $subsubcategoryProperty['name'],
                            'slug' => Str::slug($subsubcategoryProperty['name']),
                            'frontend_type' => $subsubcategoryProperty['frontend_type'],
                            'variants' => $subsubcategoryProperty['variants']
                        ]));
                    }
                }
            }
        }

        // Category::factory(10)->create()->each(function (Category $category) {
        //     $counts = [0, random_int(3, 7)];
        //     $category->properties()->saveMany(Property::factory(rand(1, 5))->create(['category_id' => $category->id]));

        //     $category->children()
        //         ->saveMany(
        //             Category::factory($counts[array_rand($counts)])
        //                 ->create()
        //                 ->each(function (Category $category) {
        //                     $counts = [0, random_int(3, 7)];
        //                     $category->children()
        //                         ->saveMany(Category::factory($counts[array_rand($counts)])
        //                             ->create());
        //                 })
        //         );
        // });
    }

    public function getCategories()
    {
        $categories = [
            [
                'name' => 'Дитячий світ',
                'properties' => [
                    ['name' => 'Вік', 'frontend_type' => 'integer', 'variants' => null],
                    ['name' => 'Стать', 'frontend_type' => 'radio', 'variants' => ['Хлопчик', 'Дівчинка']]
                ],
                'subcategories' => [
                    [
                        'name' => 'Іграшки',
                        'properties' => [
                            ['name' => 'Матеріал', 'frontend_type' => 'select', 'variants' => ['пластик', 'дерево', 'текстиль']],
                            ['name' => 'Розмір', 'frontend_type' => 'text', 'variants' => null],
                        ],
                        'subcategories' => [
                            [
                                'name' => 'Ляльки',
                                'properties' => [
                                    ['name' => 'Розмір', 'frontend_type' => 'decimal', 'variants' => null],
                                    ['name' => 'Колір волосся', 'frontend_type' => 'text', 'variants' => null]
                                ],
                                'subcategories' => []
                            ],
                            [
                                'name' => 'Машинки',
                                'properties' => [
                                    ['name' => 'Колір', 'frontend_type' => 'text', 'variants' => null],
                                    ['name' => 'Матеріал', 'frontend_type' => 'select', 'variants' => ['метал', 'пластик']],
                                ],
                                'subcategories' => []
                            ]
                        ]
                    ],
                    [
                        'name' => 'Одяг для дітей',
                        'properties' => [
                            ['name' => 'Розмір', 'frontend_type' => 'select', 'variants' => ['XS', 'S', 'M', 'L', 'XL']],
                            ['name' => 'Колір', 'frontend_type' => 'text', 'variants' => null],
                        ],
                        'subcategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Нерухомість',
                'properties' => [
                    ['name' => 'Тип нерухомості', 'frontend_type' => 'radio', 'variants' => ['Квартира', 'Будинок', 'Земельна ділянка']],
                    ['name' => 'Розташування', 'frontend_type' => 'text', 'variants' => null]
                ],
                'subcategories' => [
                    [
                        'name' => 'Продаж',
                        'properties' => [
                            ['name' => 'Ціна', 'frontend_type' => 'decimal', 'variants' => null],
                            ['name' => 'Кількість кімнат', 'frontend_type' => 'integer', 'variants' => null],
                        ],
                        'subcategories' => []
                    ],
                    [
                        'name' => 'Оренда',
                        'properties' => [
                            ['name' => 'Термін оренди (місяці)', 'frontend_type' => 'integer', 'variants' => null],
                            ['name' => 'Мебльована', 'frontend_type' => 'boolean', 'variants' => null],
                        ],
                        'subcategories' => []
                    ]
                ]
            ],
            [
                'name' => 'Авто',
                'properties' => [
                    ['name' => 'Марка авто', 'frontend_type' => 'text', 'variants' => null],
                    ['name' => 'Тип пального', 'frontend_type' => 'select', 'variants' => ['Бензин', 'Дизель', 'Електрика']],
                ],
                'subcategories' => [
                    [
                        'name' => 'Легкові авто',
                        'properties' => [
                            ['name' => 'Рік випуску', 'frontend_type' => 'integer', 'variants' => null],
                            ['name' => 'Кількість дверей', 'frontend_type' => 'integer', 'variants' => null],
                        ],
                        'subcategories' => []
                    ],
                    [
                        'name' => 'Вантажівки',
                        'properties' => [
                            ['name' => 'Грузопідйомність (тонн)', 'frontend_type' => 'decimal', 'variants' => null],
                            ['name' => 'Тип кузова', 'frontend_type' => 'text', 'variants' => null],
                        ],
                        'subcategories' => []
                    ]
                ]
            ],
            // Продолжите заполнение оставшихся категорий и подкатегорий по аналогии
        ];

        return $categories;
    }
}
