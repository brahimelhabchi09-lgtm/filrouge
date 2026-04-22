<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Model\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Infrastructure', 'description' => 'Building, equipment, and facility issues'],
            ['name' => 'Pedagogy', 'description' => 'Academic and teaching-related concerns'],
            ['name' => 'Services', 'description' => 'Campus services and facilities'],
            ['name' => 'HR', 'description' => 'Human Resources and administration'],
            ['name' => 'Food', 'description' => 'Food services and dining issues'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
