<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <a href="/" class="showLoading"><img src="{{ asset('icon.png') }}" width="80px"></a>
        </x-slot>

        <x-jet-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @if(config('app.env')=='local')
            @csrf
            <div>
                <x-jet-label for="email" value="{{ __('Email') }}" />
                <x-jet-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="mt-4">
                <x-jet-label for="password" value="{{ __('Password') }}" />
                <x-jet-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4 mb-4">
                @if (Route::has('password.request'))
                    <div class="float-start">
                        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    </div><br>
                @endif
                <x-jet-button class="fw-normal fs-6 text-white text-center rounded-3 ml-2">
                    信箱登入
                </x-jet-button>
            </div>
            @endif

            <div class="text-center">
                @php
                $providers = [
                    'facebook' => [
                        'bgColor' => '#1877f2',
                        'icon' => 'bi bi-facebook',
                        'svg' => ''
                    ],
                    'line' => [
                        'bgColor' => '#33AA33',
                        'icon' => '',
                        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-line" style="display:inline-block;"
                                    viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M8 0c4.411 0 8 2.912 8 6.492 0 1.433-.555 2.723-1.715 3.994-1.678 1.932-5.431 4.285-6.285 4.645-.83.35-.734-.197-.696-.413l.003-.018.114-.685c.027-.204.055-.521-.026-.723-.09-.223-.444-.339-.704-.395C2.846 12.39 0 9.701 0 6.492 0 2.912 3.59 0 8 0zM5.022 7.686H3.497V4.918a.156.156 0 0 0-.155-.156H2.78a.156.156 0 0 0-.156.156v3.486c0 .041.017.08.044.107v.001l.002.002.002.002a.154.154 0 0 0 .108.043h2.242c.086 0 .155-.07.155-.156v-.56a.156.156 0 0 0-.155-.157zm.791-2.924h.562c.086 0 .155.07.155.156v3.486c0 .086-.07.155-.155.155h-.562a.156.156 0 0 1-.156-.155V4.918c0-.086.07-.156.156-.156zm3.863 0h.56c.087 0 .157.07.157.156v3.486c0 .086-.07.155-.156.155h-.561a.155.155 0 0 1-.04-.005h-.002a.168.168 0 0 1-.011-.004l-.005-.002-.007-.003a.066.066 0 0 1-.008-.004L9.6 8.54l-.01-.006-.001-.001a.154.154 0 0 1-.04-.039l-1.6-2.16v2.07a.155.155 0 0 1-.155.156h-.561a.156.156 0 0 1-.156-.155V4.918c0-.086.07-.156.156-.156H7.8l.005.001h.006l.003.001h.006l.01.003h.002l.002.001.01.003.005.002a.09.09 0 0 1 .009.004l.003.001.002.001a.113.113 0 0 1 .013.008l.003.002.005.003v.001c.002 0 .003.002.004.003a.092.092 0 0 1 .008.006l.003.003a.17.17 0 0 1 .023.026L9.52 6.99V4.918c0-.086.07-.156.156-.156zm3.815.717c0 .086-.07.156-.155.156H11.81v.59h1.525c.086 0 .155.07.155.155v.561c0 .086-.07.156-.155.156H11.81v.59h1.525c.086 0 .155.07.155.155v.561c0 .086-.07.156-.155.156h-2.242a.155.155 0 0 1-.11-.045l-.002-.003a.155.155 0 0 1-.044-.107V4.918c0-.042.017-.08.043-.107l.003-.003.001-.001a.155.155 0 0 1 .109-.045h2.242c.086 0 .155.07.155.156v.561z" />
                                  </svg>',
                    ],
                ];
                @endphp
                @foreach($providers as $provider => $params)
                <a class="fs-6 text-white text-center rounded-3 p-2 m-2"
                    href="{{ route('social.login', ['provider' => $provider]) }}"
                    style="background-color: {{ $params['bgColor'] }};">
                    使用 <i class="{{ $params['icon'] }}"></i>{!! $params['svg'] !!} 登入
                </a>
                @endforeach
            </div>
        </form>
    </x-jet-authentication-card>
</x-guest-layout>
