<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker=Factory::create();
        for($i=0; $i<3 ;$i++)
        {
            $user=User::create([
                'username'=>$faker->firstname,
                'role_as'=>'0',
                'email'=>$faker->email,
                'email_verified_at'=>Carbon::now(),
                'password'=>bcrypt('12345678'),
                'c_password'=>bcrypt('12345678'),
            ]);
        }
    }
}
