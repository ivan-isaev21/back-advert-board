<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\Adverts\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    private $categoriesUrl = '/api/v1/adverts/categories';

    /**
     * Method testCategories
     *
     * @return void
     */
    public function testCategories(): void
    {
        $response = $this->get($this->categoriesUrl);
        $response->assertStatus(200);
    }
    
    /**
     * Method testCategory
     *
     * @return void
     */
    public function testCategory(): void
    {
        $category = Category::factory()->create();
        $response = $this->get($this->categoriesUrl . '/' . $category->id);
        $response->assertStatus(200);
    }
}
