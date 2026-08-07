<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Chișinău' => 'Municipiul Chișinău',
            'Bălți' => 'Municipiul Bălți',
            'Tiraspol' => 'Transnistria',
            'Bender (Tighina)' => 'Transnistria',
            'Cahul' => 'Raionul Cahul',
            'Ungheni' => 'Raionul Ungheni',
            'Soroca' => 'Raionul Soroca',
            'Orhei' => 'Raionul Orhei',
            'Comrat' => 'UTA Găgăuzia',
            'Edineț' => 'Raionul Edineț',
            'Strășeni' => 'Raionul Strășeni',
            'Hîncești' => 'Raionul Hîncești',
            'Căușeni' => 'Raionul Căușeni',
            'Drochia' => 'Raionul Drochia',
            'Florești' => 'Raionul Florești',
            'Rîbnița' => 'Transnistria',
        ];

        foreach ($cities as $name => $region) {
            City::firstOrCreate(['name' => $name], ['region' => $region]);
        }
    }
}
