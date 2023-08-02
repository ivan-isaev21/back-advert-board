<?php

namespace Tests\Feature\Api\v1\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    private $authUrl = '/api/v1/auth/register';

    /**
     * Method testEmptyPayload
     *
     * @return void
     */
    public function testEmptyPayload(): void
    {
        $response = $this->postJson($this->authUrl);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The contact person field is required. (and 2 more errors)',
            'errors' => [
                'contact_person' => ['The contact person field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ]
        ]);
    }

    /**
     * Method testSuccess
     *
     * @return void
     */
    public function testSuccess(): void
    {
        $payload = [
            'contact_person' => 'test',
            'email' => 'test-email@test.com',
            'password' => 'Aa123456789',
            'password_confirmation' => 'Aa123456789'
        ];

        $response = $this->postJson($this->authUrl, $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => 'Check your email and click on the link to verify.'
        ]);
    }

    /**
     * Method testAlreadyRegistered
     *
     * @return void
     */
    public function testAlreadyRegistered(): void
    {
        $payload = [
            'contact_person' => 'test',
            'email' => 'test-email@test.com',
            'password' => 'Aa123456789',
            'password_confirmation' => 'Aa123456789'
        ];

        $response = $this->postJson($this->authUrl, $payload);
        $response->assertStatus(201);

        $response2 = $this->postJson($this->authUrl, $payload);
        $response2->assertStatus(422);
        $response2->assertJson([
            'message' => 'The email has already been taken.',
            'errors' => [
                'email' => ['The email has already been taken.']
            ]
        ]);
    }
}
