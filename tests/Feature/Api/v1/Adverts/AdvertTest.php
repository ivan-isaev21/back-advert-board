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
     * Method testEmptyCreate
     *
     * @return void
     */
    public function testEmptyCreate(): void
    {
        $payload = [];
        $createUrl = $this->advertsUrl . $this->category->id;
        $response = $this->postJson($createUrl, $payload, $this->headers);
        $response->assertStatus(422);
    }

    /**
     * Method testSuccessCreate
     *
     * @return void
     */
    public function testSuccessCreate(): void
    {
        $payload = [
            'title' => 'Test advert',
            'content' => 'Test content',
            'category_id' => $this->category->id,
            'properties' => [
                1 => 'test'
            ]
        ];

        $createUrl = $this->advertsUrl . $this->category->id;
        $response = $this->postJson($createUrl, $payload, $this->headers);
        $response->assertStatus(201);
    }

    /**
     * Method testEmptyUpdate
     *
     * @return void
     */
    public function testEmptyUpdate(): void
    {
        $payload = [];
        $updateUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id;
        $response = $this->putJson($updateUrl, $payload, $this->headers);
        $response->assertStatus(422);
    }

    /**
     * Method testAnotherUserUpdate
     *
     * @return void
     */
    public function testAnotherUserUpdate(): void
    {
        $this->advert->update(['user_id' => $this->anotherUser->id]);

        $payload = [
            'title' => 'Test advert update',
            'content' => 'Test content  update',
            'category_id' => $this->category->id,
            'properties' => [
                1 => 'test update'
            ]
        ];


        $updateUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id;
        $response = $this->putJson($updateUrl, $payload, $this->headers);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'This advert is not belong to this user.']);
    }

    /**
     * Method testIsNotDraftUpdate
     *
     * @return void
     */
    public function testIsNotDraftUpdate(): void
    {
        $this->advert->update(['status' => Advert::STATUS_ACTIVE]);

        $payload = [
            'title' => 'Test advert update',
            'content' => 'Test content  update',
            'category_id' => $this->category->id,
            'properties' => [
                1 => 'test update'
            ]
        ];

        $updateUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id;
        $response = $this->putJson($updateUrl, $payload, $this->headers);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'This advert is not draft.']);
    }

    /**
     * Method testSuccessUpdate
     *
     * @return void
     */
    public function testSuccessUpdate(): void
    {
        $payload = [
            'title' => 'Test advert update',
            'content' => 'Test content  update',
            'category_id' => $this->category->id,
            'properties' => [
                1 => 'test update'
            ]
        ];

        $updateUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id;
        $response = $this->putJson($updateUrl, $payload, $this->headers);
        $response->assertStatus(202);
    }

    /**
     * Method testAnotherUserSendToModeration
     *
     * @return void
     */
    public function testAnotherUserSendToModeration(): void
    {
        $this->advert->update([
            'user_id' => $this->anotherUser->id
        ]);

        $payload = [];
        $sendToModerationUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id . '/send-to-moderation';
        $response = $this->putJson($sendToModerationUrl, $payload, $this->headers);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'This advert is not belong to this user.']);
    }

    /**
     * Method testAdvertIsNotDraftSendToModeration
     *
     * @return void
     */
    public function testAdvertIsNotDraftSendToModeration(): void
    {
        $this->advert->update([
            'status' => Advert::STATUS_ACTIVE
        ]);

        $payload = [];
        $sendToModerationUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id . '/send-to-moderation';
        $response = $this->putJson($sendToModerationUrl, $payload, $this->headers);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'Advert is not draft.']);
    }


    /**
     * Method testSuccessSendToModeration
     *
     * @return void
     */
    public function testSuccessSendToModeration(): void
    {
        $payload = [];
        $sendToModerationUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id . '/send-to-moderation';
        $response = $this->putJson($sendToModerationUrl, $payload, $this->headers);
        $response->assertStatus(202);
    }

    /**
     * Method testAnotherUserClose
     *
     * @return void
     */
    public function testAnotherUserClose(): void
    {
        $this->advert->update([
            'user_id' => $this->anotherUser->id
        ]);

        $payload = [];
        $sendToModerationUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id . '/close';
        $response = $this->putJson($sendToModerationUrl, $payload, $this->headers);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'This advert is not belong to this user.']);
    }

    /**
     * Method testAdvertIsDraftClose
     *
     * @return void
     */
    public function testAdvertIsDraftClose(): void
    {
        $this->advert->update([
            'status' => Advert::STATUS_DRAFT
        ]);

        $payload = [];
        $sendToModerationUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id . '/close';
        $response = $this->putJson($sendToModerationUrl, $payload, $this->headers);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'This advert is draft.']);
    }

    /**
     * Method testAdvertIsAlreadyClosed
     *
     * @return void
     */
    public function testAdvertIsAlreadyClosed(): void
    {
        $this->advert->update([
            'status' => Advert::STATUS_CLOSED
        ]);

        $payload = [];
        $sendToModerationUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id . '/close';
        $response = $this->putJson($sendToModerationUrl, $payload, $this->headers);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'This advert is already closed.']);
    }

    /**
     * Method testSuccessClosed
     *
     * @return void
     */
    public function testSuccessClosed(): void
    {
        $this->advert->update([
            'status' => Advert::STATUS_ACTIVE
        ]);

        $payload = [];
        $sendToModerationUrl = $this->advertsUrl . $this->category->id . '/' . $this->advert->id . '/close';
        $response = $this->putJson($sendToModerationUrl, $payload, $this->headers);
        $response->assertStatus(202);
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
