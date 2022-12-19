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
        //add  the week days into the database
        for ($i = 0; $i < 7; $i++) {
            WeekDays::Create(
                ["name" => date('D', ($i + 3) * 60 * 60 * 24)]
            );
        }
        /**
         * Sun
         * Mon
         * Tue
         * Wed
         * Thu
         * Fri
         * Sat
         *
         **/
    }
}
