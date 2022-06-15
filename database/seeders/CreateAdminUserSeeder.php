<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateAdminUserSeeder extends Seeder
{
/**
* Run the database seeds.
*
* @return void
*/
public function run()
{
    $user = User::create([
'username'=> 'ahmad99',
'role_as'=> '1',
'phone'=>'093454345345',
'country'=>'syria',
'city'=>'damas',
'email'=> 'ahmad@gmail.com',
'password'=> bcrypt('123456'),
'c_password'=> bcrypt('123456'),

           ]);
}
}
