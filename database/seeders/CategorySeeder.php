<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Income
            ['name' => 'Gaji',         'type' => 'income',  'icon' => 'briefcase',    'color' => '#10b981'],
            ['name' => 'Freelance',    'type' => 'income',  'icon' => 'computer',     'color' => '#3b82f6'],
            ['name' => 'Investasi',    'type' => 'income',  'icon' => 'chart-bar',    'color' => '#8b5cf6'],
            ['name' => 'Bonus',        'type' => 'income',  'icon' => 'gift',         'color' => '#f59e0b'],
            ['name' => 'Lainnya',      'type' => 'income',  'icon' => 'plus-circle',  'color' => '#6b7280'],

            // Expense
            ['name' => 'Makanan',      'type' => 'expense', 'icon' => 'cake',         'color' => '#ef4444'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'truck',        'color' => '#f97316'],
            ['name' => 'Belanja',      'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#ec4899'],
            ['name' => 'Tagihan',      'type' => 'expense', 'icon' => 'receipt',      'color' => '#f59e0b'],
            ['name' => 'Kesehatan',    'type' => 'expense', 'icon' => 'heart',        'color' => '#14b8a6'],
            ['name' => 'Hiburan',      'type' => 'expense', 'icon' => 'film',         'color' => '#a855f7'],
            ['name' => 'Pendidikan',   'type' => 'expense', 'icon' => 'academic-cap', 'color' => '#06b6d4'],
            ['name' => 'Lainnya',      'type' => 'expense', 'icon' => 'ellipsis',     'color' => '#6b7280'],
        ];

        foreach ($categories as $cat) {
            Category::create([...$cat, 'is_system' => true]);
        }
    }
}
