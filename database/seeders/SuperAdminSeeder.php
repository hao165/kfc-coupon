<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Team;

class SuperAdminSeeder extends Seeder 
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userPassword="480918";
        DB::transaction(function () use ($userPassword) {
            return tap(User::create([
                'name' => 'iZO',
                'email' => 'admin@izo.tw',
                'password' => Hash::make($userPassword),
                'current_team_id' => 1,
            ]), function (User $user) {
                $this->createTeam($user, 'SuperAdmin');
                $this->createTeam($user, 'Member');
            });
        });
    }

    /**
     * Create a personal team for the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createTeam(User $user, $teamName)
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => $teamName,
            'personal_team' => false,
        ]));
    }
}
