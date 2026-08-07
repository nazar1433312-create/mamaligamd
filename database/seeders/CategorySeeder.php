<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Reparații și construcții' => [
                'Instalații sanitare', 'Electricitate', 'Lucrări de vopsire', 'Lucrări de faianță',
                'Renovare apartamente la cheie', 'Montaj uși și ferestre',
            ],
            'Hamali și mutări' => [
                'Hamali', 'Taxi marfă', 'Mutări', 'Demontare/montare mobilă',
            ],
            'Curățenie' => [
                'Curățenie apartamente', 'Curățenie birouri', 'Curățenie chimică mobilă', 'Spălat geamuri',
            ],
            'Reparații tehnică' => [
                'Reparații telefoane', 'Reparații laptopuri', 'Reparații electrocasnice', 'Reparații televizoare',
            ],
            'IT și freelance' => [
                'Dezvoltare site-uri', 'Design', 'Copywriting', 'SMM și publicitate', 'Configurare software',
            ],
            'Frumusețe și sănătate' => [
                'Frizer', 'Manichiură', 'Masaj', 'Cosmetolog',
            ],
            'Meditații și educație' => [
                'Limbi străine', 'Materii școlare', 'Muzică', 'Pregătire BAC',
            ],
            'Servicii auto' => [
                'Service auto', 'Vulcanizare', 'Electrician auto', 'Tractare auto',
            ],
        ];

        $order = 0;

        foreach ($tree as $parentName => $children) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName, 'sort_order' => $order++]
            );

            $childOrder = 0;

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($parentName).'-'.Str::slug($childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'sort_order' => $childOrder++,
                    ]
                );
            }
        }
    }
}
