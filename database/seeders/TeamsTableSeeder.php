<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('teams')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('teams')->insert([
                [
                    'name' => 'Beşiktaş',
                    'strength' => '80',
                    'fan_power' => '100'
                ],
                [
                    'name' => 'Galatasaray',
                    'strength' => '85',
                    'fan_power' => '95'
                ],
                [
                    'name' => 'Fenerbahçe',
                    'strength' => '90',
                    'fan_power' => '90'
                ],
                [
                    'name' => 'Trabzonspor',
                    'strength' => '75',
                    'fan_power' => '85'
                ],
                [
                    'name' => 'Trabzonspor2',
                    'strength' => '75',
                    'fan_power' => '85'
                ],
                [
                    'name' => 'Trabzonspor3',
                    'strength' => '75',
                    'fan_power' => '85'
                ],
                [
                    'name' => 'Trabzonspor4',
                    'strength' => '75',
                    'fan_power' => '85'
                ],
                [
                    'name' => 'Trabzonspor5',
                    'strength' => '75',
                    'fan_power' => '85'
                ]
            ]);
        }

}
