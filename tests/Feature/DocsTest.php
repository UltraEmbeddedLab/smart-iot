<?php declare(strict_types=1);

use App\Models\User;

it('redirects guests away from the docs', function (): void {
    $this->get(route('docs.show'))->assertRedirect(route('login'));
});

it('renders the default documentation page for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('docs.show'))
        ->assertOk()
        ->assertSee('Introduction')
        ->assertSee('Smart IoT');
});

it('resolves a specific documentation slug', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('docs.show', ['slug' => 'introduction']))
        ->assertOk()
        ->assertSee('Introduction');
});

it('returns 404 for an unknown documentation slug', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('docs.show', ['slug' => 'does-not-exist']))
        ->assertNotFound();
});

it('serves the raw markdown source', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('docs.raw', ['slug' => 'introduction']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    expect($response->getContent())->toContain('# Introduction');
});

it('rejects directory traversal attempts in the raw route', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/docs/..%2F..%2Fsecret/raw')
        ->assertNotFound();
});
