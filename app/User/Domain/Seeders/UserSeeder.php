<?php

declare(strict_types=1);

namespace App\User\Domain\Seeders;

use App\User\Domain\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create();
    }
}
