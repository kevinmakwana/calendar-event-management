<?php

declare(strict_types=1);

namespace App\Event\Domain\Seeders;

use App\Event\Domain\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed the database with 10 events
        Event::factory()->count(10)->create();
    }
}
