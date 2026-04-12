<?php declare(strict_types=1);

test('registration route is not registered', function () {
    expect(Route::has('register'))->toBeFalse();
    expect(Route::has('register.store'))->toBeFalse();
});

test('the registration screen returns 404', function () {
    $this->get('/register')->assertNotFound();
});

test('posting to the registration endpoint returns 404', function () {
    $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});
