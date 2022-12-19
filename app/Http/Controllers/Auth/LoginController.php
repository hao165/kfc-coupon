<?php

namespace App\Http\Controllers\Auth;

use App\Models\Team;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Socialite;
use Exception;

class LoginController extends Controller
{
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Redirect to authentication page based on $provider.
     *
     * @param string $provider
     * @return Response
     */
    public function redirectToProvider(string $provider)
    {
        try {
            $scopes = config("services.$provider.scopes") ?? [];
            if (count($scopes) === 0) {
                return Socialite::driver($provider)->redirect();
            } else {
                return Socialite::driver($provider)->scopes($scopes)->redirect();
            }
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Obtain the user information from $provider
     *
     * @param string $provider
     * @return Response
     */
    public function handleProviderCallback(string $provider)
    {
        try {
            $data = Socialite::driver($provider)->user();

            if (!$data->email) {
                throw new \Exception("Mail is null");
            }

            return $this->handleSocialUser($provider, $data);
        } catch (\Exception $e) {
            return redirect('login')->withErrors(['authentication_deny' => '[' . ucfirst($provider) . '] 授權錯誤： 您可以嘗試移除應用程式並重新登入.']);
        }
    }

    /**
     * Handles the user's information and creates/updates
     * the record accordingly.
     *
     * @param string $provider
     * @param object $data
     * @return Response
     */
    public function handleSocialUser(string $provider, object $data)
    {
        // 1-1. 查找 provider id
        $user = User::where([
            "social->{$provider}->id" => $data->id,
        ])->first();

        // 1-2. 查找 email
        if (!$user) {
            $user = User::where([
                'email' => $data->email,
            ])->first();
        }

        // 2-1. 不存在 建立新帳號
        if (!$user) {
            return $this->createUserWithSocialData($provider, $data);
        }

        // 2-2. 存在 儲存新資訊
        if ((!$user->hasVerifiedEmail()) && ($data->email)) {
            $user->email = $data->email;
            $user->markEmailAsVerified();
        }
        $social = $user->social;
        $social[$provider] = [
            'id' => $data->id,
            'token' => $data->token
        ];
        $user->social = $social;
        $user->profile_photo_path = $data->getAvatar();
        $user->save();

        // 3. 登入
        return $this->socialLogin($user);
    }

    /**
     * Create user
     *
     * @param string $provider
     * @param object $data
     * @return Response
     */
    public function createUserWithSocialData(string $provider, object $data)
    {
        try {
            // 以信箱為帳號，禁止空白
            if (!$data->email) {
                throw new \Exception("Mail is null");
            }

            $user = new User;
            $user->email = $data->email;
            $user->name = $data->name;
            $user->social = [
                $provider => [
                    'id' => $data->id,
                    'token' => $data->token,
                ],
            ];
            $user->profile_photo_path = $data->getAvatar();
            // markEmailAsVerified() contains save() behavior
            $user->markEmailAsVerified();

            // 成為會員後 自動加入team，並切換成當前team
            $team = Team::find(2); // member
            $user->teams()->attach($team, ['role' => 'editor']);
            $user->switchTeam($team);

            $user->save();

            // 登入
            return $this->socialLogin($user);
        } catch (Exception $e) {
            return redirect('login')->withErrors(['authentication_deny' => '[' . ucfirst($provider) . '] 授權錯誤： 您可以嘗試移除應用程式並重新登入！']);
        }
    }

    /**
     * Log the user in
     *
     * @param User $user
     * @return Response
     */
    public function socialLogin(User $user)
    {
        auth()->loginUsingId($user->id);

        return redirect($this->redirectTo);
    }
}
