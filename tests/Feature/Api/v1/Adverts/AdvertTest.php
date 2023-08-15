<?php

namespace Tests\Feature\Api\v1\Adverts;

use App\Models\Adverts\Advert;
use App\Models\Adverts\Category;
use App\Models\Adverts\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdvertTest extends TestCase
{
    private $advertsUrl = '/api/v1/adverts/';
    private $user;
    private $anotherUser;
    private $token;
    private $headers = [];
    private $category;
    private $advert;

    public function setUp(): void
    {
        parent::setUp();

        $this->prepareForTests();
    }

    /**
     * Method testIndex
     *
     * @return void
     */
    public function testIndex(): void
    {
        $response = $this->get($this->advertsUrl);
        $response->assertStatus(200);
    }


    /**
     * Method testNotAvailableToShow
     *
     * @return void
     */
    public function testNotAvailableToShow(): void
    {
        $this->advert->update([
            'user_id' => $this->anotherUser->id,
            'status' => Advert::STATUS_DRAFT
        ]);

        $showUrl = $this->advertsUrl . 'show/' . $this->advert->id;
        $response = $this->getJson($showUrl);
        $response->assertStatus(422);
        $response->assertJson(['error' => 'This advert is not available to show.']);
    }


    /**
     * Method testSuccessShow
     *
     * @return void
     */
    public function testSuccessShow(): void
    {
        $this->advert->update(['status' => Advert::STATUS_ACTIVE]);
        $showUrl = $this->advertsUrl . 'show/' . $this->advert->id;
        $response = $this->getJson($showUrl);
        $response->assertStatus(200);
    }

    /**
     * Method prepareForTests
     *
     * @return void
     */
    private function prepareForTests(): void
    {
        $this->createTestUser();
        $this->createAnotherUser();
        $this->createTestCategory();
        $this->createTestAdvert();
    }

    /**
     * Method createTestUser
     *
     * @return void
     */
    private function createTestUser(): void
    {
        $email = 'test-email@test.com';
        $password = 'Aa123456789';
        $this->user = User::factory()->create([
            'email' => $email,
            'password' => $password,
            'status' => User::STATUS_ACTIVE
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->setAuthHeaders();
    }

    /**
     * Method createAnotherUser
     *
     * @return void
     */
    private function createAnotherUser(): void
    {
        $this->anotherUser = User::factory()->create();
    }

    /**
     * Method setAuthHeaders
     *
     * @return void
     */
    private  function setAuthHeaders(): void
    {
        $this->headers['Authorization'] =  'Bearer ' . $this->token;
    }

    /**
     * Method createTestCategory
     *
     * @return void
     */
    private function createTestCategory()
    {
        $this->category = Category::factory()->create(['name' => 'Category 1']);

        Property::factory()->create(['name' => 'Property 1', 'category_id' => $this->category->id, 'required' => true, 'frontend_type' => 'string']);
        Property::factory()->create(['name' => 'Property 2', 'category_id' => $this->category->id, 'required' => false]);
    }

    /**
     * Method createTestAdvert
     *
     * @return void
     */
    private function createTestAdvert(): void
    {
        $this->advert = Advert::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'status' => Advert::STATUS_DRAFT
        ]);
    }
}
