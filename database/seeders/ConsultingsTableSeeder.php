<?php

namespace Database\Seeders;

use App\Models\Consultings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConsultingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Consultings::Create(
            [
                'name' => 'Medical Consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Professional Consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Psychological Consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Family Consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Business / management Consultings'
            ]
        );

    }
}
