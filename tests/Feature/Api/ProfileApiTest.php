<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can retrieve their profile', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'company' => 'Tech Corp',
        'job_title' => 'Software Engineer',
        'country' => 'USA',
        'city' => 'New York',
        'socials' => [
            ['title' => 'LinkedIn', 'url' => 'https://linkedin.com/in/johndoe'],
            ['title' => 'Twitter', 'url' => 'https://twitter.com/johndoe'],
        ],
    ]);

    $token = $user->createToken('Test Device')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'initials',
                'phone',
                'company',
                'job_title',
                'country',
                'city',
                'socials',
            ],
        ])
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'company' => 'Tech Corp',
                'job_title' => 'Software Engineer',
                'country' => 'USA',
                'city' => 'New York',
                'socials' => [
                    ['title' => 'LinkedIn', 'url' => 'https://linkedin.com/in/johndoe'],
                    ['title' => 'Twitter', 'url' => 'https://twitter.com/johndoe'],
                ],
            ],
        ]);
});

test('unauthenticated user cannot retrieve profile', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});

test('authenticated user can update their profile', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $token = $user->createToken('Test Device')->plainTextToken;

    $updateData = [
        'name' => 'John Smith',
        'email' => 'john.smith@example.com',
        'phone' => '+1234567890',
        'company' => 'New Tech Corp',
        'job_title' => 'Senior Software Engineer',
        'country' => 'Canada',
        'city' => 'Toronto',
        'socials' => [
            ['title' => 'LinkedIn', 'url' => 'https://linkedin.com/in/johnsmith'],
            ['title' => 'GitHub', 'url' => 'https://github.com/johnsmith'],
        ],
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', $updateData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'phone',
            'company',
            'job_title',
            'country',
            'city',
            'socials',
        ])
        ->assertJson([
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '+1234567890',
            'company' => 'New Tech Corp',
            'job_title' => 'Senior Software Engineer',
            'country' => 'Canada',
            'city' => 'Toronto',
            'socials' => [
                ['title' => 'LinkedIn', 'url' => 'https://linkedin.com/in/johnsmith'],
                ['title' => 'GitHub', 'url' => 'https://github.com/johnsmith'],
            ],
        ]);

    // Verify the database was updated
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'John Smith',
        'email' => 'john.smith@example.com',
        'phone' => '+1234567890',
        'company' => 'New Tech Corp',
        'job_title' => 'Senior Software Engineer',
        'country' => 'Canada',
        'city' => 'Toronto',
    ]);

    // Refresh the user to check socials
    $user->refresh();
    expect($user->socials)->toBe([
        ['title' => 'LinkedIn', 'url' => 'https://linkedin.com/in/johnsmith'],
        ['title' => 'GitHub', 'url' => 'https://github.com/johnsmith'],
    ]);
});

test('authenticated user can partially update their profile', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'company' => 'Tech Corp',
    ]);

    $token = $user->createToken('Test Device')->plainTextToken;

    $updateData = [
        'name' => 'John Smith',
        'email' => 'john@example.com',
        'job_title' => 'Senior Developer',
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'company' => 'Tech Corp',
            'job_title' => 'Senior Developer',
        ]);
});

test('profile update validates required fields', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Device')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', [
        'name' => '', // Required field is empty
        'email' => 'invalid-email', // Invalid email format
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email']);
});

test('profile update prevents duplicate email', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create(['email' => 'user@example.com']);

    $token = $user->createToken('Test Device')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', [
        'name' => 'Test User',
        'email' => 'existing@example.com', // Already exists
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('profile update allows same email for same user', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);
    $token = $user->createToken('Test Device')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', [
        'name' => 'Updated Name',
        'email' => 'user@example.com', // Same email
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'name' => 'Updated Name',
            'email' => 'user@example.com',
        ]);
});

test('profile update validates social links format', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Device')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'socials' => [
            ['title' => 'LinkedIn', 'url' => 'invalid-url'], // Invalid URL
            ['title' => '', 'url' => 'https://github.com/user'], // Empty title
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['socials.0.url']);
});

test('profile update accepts valid social links', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Device')->plainTextToken;

    $socialData = [
        ['title' => 'LinkedIn', 'url' => 'https://linkedin.com/in/user'],
        ['title' => 'Twitter', 'url' => 'https://twitter.com/user'],
        ['title' => 'Website', 'url' => 'https://www.example.com'],
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'socials' => $socialData,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'socials' => $socialData,
        ]);
});

test('profile update with null optional fields', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'company' => 'Tech Corp',
    ]);

    $token = $user->createToken('Test Device')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => null,
        'company' => null,
        'job_title' => null,
        'country' => null,
        'city' => null,
        'socials' => null,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => null,
            'company' => null,
            'job_title' => null,
            'country' => null,
            'city' => null,
            'socials' => null,
        ]);
});

test('unauthenticated user cannot update profile', function () {
    $response = $this->putJson('/api/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(401);
});
