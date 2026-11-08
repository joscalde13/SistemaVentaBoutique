<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pantalones', 'Palazzos', 'Blusas', 'Conjuntos', 'Vestidos', 'Ropa de niña'] as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }
    }
}
