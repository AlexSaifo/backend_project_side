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
                'name' => 'Medical consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Professional consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Psychological consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Family consultings'
            ]
        );

        Consultings::Create(
            [
                'name' => 'Business / management consultings'
            ]
        );

    }
}
