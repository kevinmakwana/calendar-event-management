<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Event\Domain\Seeders\EventSeeder;
use App\User\Domain\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(EventSeeder::class);
    }
}
