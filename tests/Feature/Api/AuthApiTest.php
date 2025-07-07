<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user can register', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'iPhone 15',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
            ],
            'token',
        ])
        ->assertJson([
            'message' => 'User registered successfully.',
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => 'iPhone 15',
    ]);
});

test('user cannot register with invalid data', function () {
    $response = $this->postJson('/api/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'different',
        'device_name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password', 'device_name']);
});

test('user cannot register with existing email', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'iPhone 15',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
        'device_name' => 'iPhone 15',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
            ],
            'token',
        ])
        ->assertJson([
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => 'iPhone 15',
    ]);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
        'device_name' => 'iPhone 15',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot login with nonexistent email', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
        'device_name' => 'iPhone 15',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can get their information', function () {
    $user = User::factory()->create();
    $token = $user->createToken('iPhone 15')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
            ],
        ])
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

test('user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('iPhone 15')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully.',
        ]);

    // Verify the token was deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'token' => hash('sha256', $token),
    ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});
