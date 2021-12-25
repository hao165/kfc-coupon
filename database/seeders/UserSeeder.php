<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Team;

class UserSeeder extends Seeder 
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('users')->insert([
        //     'name' => 'iZO',
        //     'email' => 'admin@izo.tw',
        //     'password' => Hash::make('480918'),
        //     'current_team_id' => 1,
        // ]);
        
        $userPassword="480918";
        DB::transaction(function () use ($userPassword) {
            return tap(User::create([
                'name' => '機器人小肯',
                'email' => 'izotw6@gmail.com',
                'password' => Hash::make($userPassword),
            ]), function (User $user) {
                $team = Team::find(2); //member
                $user->teams()->attach($team, ['role' => 'editor']);
                $user->switchTeam($team);
            });
        });
    }

}
