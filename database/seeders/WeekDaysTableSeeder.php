<?php

namespace Database\Seeders;

use App\Models\WeekDays;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeekDaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        WeekDays::factory()->count(7)->create();
    }
}
