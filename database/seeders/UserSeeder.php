<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'bogdygewald@yahoo.de'],
            [
                'name' => 'Bogdy Gewald',
                'password' => 'supertest',
                'email_verified_at' => now(),
            ]
        );
    }
}
