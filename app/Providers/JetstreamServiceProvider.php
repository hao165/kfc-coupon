<?php

namespace App\Providers;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteTeamMember;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Jetstream::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::role('admin', 'Administrator', [
            'create',           //新增-優惠券
            'collect',          //收藏-優惠券
            'update',           //修改-優惠券
            'delete',           //刪除-優惠券
            'verify',           //審核-優惠券
            'crawler',          //控制爬蟲系統
            'comment:create',   //新增留言
            'user:edit',        //修改個人資料
        ])->description('Administrator users can perform any action.');

        Jetstream::role('editor', 'Editor', [
            'create',           //新增-優惠券
            'collect',          //收藏-優惠券
            'comment:create',   //新增留言
            'user:edit',        //修改個人資料
        ])->description('Editor users have the ability to read, create, and update.');

        Jetstream::role('black', '黑名單', [
            'collect'           //收藏-優惠券
        ])->description('被禁止任何新增修改的行為');
    }
}
