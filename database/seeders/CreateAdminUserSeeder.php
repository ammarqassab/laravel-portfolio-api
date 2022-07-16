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
// $user = User::create([
// 'username'=> 'ahmad',
// 'role_as'=> '1',
// 'phone'=>'093454345343',
// 'country'=>'syria',
// 'city'=>'damas',
// 'email'=> 'ahmad99@gmail.com',
// 'password'=> bcrypt('32145678'),
// 'c_password'=> bcrypt('32145678'), ]);

$user = User::create([
    'username'=> 'ammar qassab',
    'role_as'=> '1',
    'phone'=>'+963943435682',
    'country'=>'syria',
    'city'=>'damascus',
    'email'=> 'ammar97@gmail.com',
    'password'=> bcrypt('a1m9m9a7rqa3ss8ab'),
    'c_password'=> bcrypt('a1m9m9a7rqa3ss8ab'), ]);
}
}
