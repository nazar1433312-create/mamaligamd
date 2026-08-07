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
            'Ремонт та будівництво' => [
                'Сантехніка', 'Електрика', 'Малярні роботи', 'Плиточні роботи',
                'Ремонт квартир під ключ', 'Монтаж дверей та вікон',
            ],
            'Вантажники та переїзди' => [
                'Вантажники', 'Вантажне таксі', 'Переїзди', 'Розбирання/збирання меблів',
            ],
            'Клінінг' => [
                'Прибирання квартир', 'Прибирання офісів', 'Хімчистка меблів', 'Миття вікон',
            ],
            'Ремонт техніки' => [
                'Ремонт телефонів', 'Ремонт ноутбуків', 'Ремонт побутової техніки', 'Ремонт телевізорів',
            ],
            'IT та фріланс' => [
                'Розробка сайтів', 'Дизайн', 'Копірайтинг', 'SMM та реклама', 'Налаштування ПЗ',
            ],
            'Краса та здоров\'я' => [
                'Перукар', 'Манікюр', 'Масаж', 'Косметолог',
            ],
            'Репетитори та навчання' => [
                'Іноземні мови', 'Шкільні предмети', 'Музика', 'Підготовка до ЗНО/НМТ',
            ],
            'Авто послуги' => [
                'Автосервіс', 'Шиномонтаж', 'Автоелектрик', 'Евакуатор',
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
