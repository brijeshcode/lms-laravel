<?php

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
    	$this->AddClassTypes();
    	$this->AddServices();
    	$this->AddServiceTypes();
    }

    private function AddClassTypes()
    {
    	\DB::table('class_types')->truncate();
    	ClassType::firstOrCreate([
            'name' => 'Online',
        ]);
        ClassType::firstOrCreate([
            'name' => 'Offline',
        ]);
        ClassType::firstOrCreate([
            'name' => 'Demo',
        ]);
        ClassType::firstOrCreate([
            'name' => 'One on One',
        ]);
        ClassType::firstOrCreate([
            'name' => 'Tutorial',
        ]);
    }

    private function AddServices()
    {
    	\DB::table('services')->truncate();
    	Service::firstOrCreate([
            'name' => 'Dance Form',
        ]);
        Service::firstOrCreate([
            'name' => 'Workout',
        ]);
    }


    private function AddServiceTypes()
    {
    	\DB::table('service_types')->truncate();
    	ServiceType::firstOrCreate([
    		'service_id' => 1,
            'name' => 'Bollywood'
        ]);
        ServiceType::firstOrCreate([
    		'service_id' => 1,
            'name' => 'Workout'
        ]);

        ServiceType::firstOrCreate([
    		'service_id' => 1,
            'name' => 'Contemporary'
        ]);

        ServiceType::firstOrCreate([
    		'service_id' => 1,
            'name' => 'Yoga'
        ]);
        ServiceType::firstOrCreate([
    		'service_id' => 2,
            'name' => 'Salsa'
        ]);


    }
}
