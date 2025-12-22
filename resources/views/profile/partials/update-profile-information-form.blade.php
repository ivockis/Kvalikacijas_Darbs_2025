<section>
    <header>
        <h2 class="text-lg font-medium text-gray-200">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Image Section -->
        <div>
            <x-input-label for="profile_image" :value="__('Profile Image')" />
            <div class="mt-2 flex items-center gap-4">
                <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover">
                <div>
                    <input id="profile_image" name="profile_image" type="file" class="block w-full text-sm text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700
                        hover:file:bg-indigo-100"
                        onchange="document.getElementById('profile_image_preview').src = window.URL.createObjectURL(this.files[0])">
                    <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                    
                    @if ($user->profile_image_id)
                        <div class="mt-2 flex items-center">
                            <input type="checkbox" name="remove_profile_image" id="remove_profile_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="remove_profile_image" class="ml-2 text-sm text-gray-400">{{ __('Remove current image') }}</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name">{{ __('Name') }}<span class="text-red-500">*</span></x-input-label>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="surname">{{ __('Surname') }}<span class="text-red-500">*</span></x-input-label>
            <x-text-input id="surname" name="surname" type="text" class="mt-1 block w-full" :value="old('surname', $user->surname)" required autocomplete="surname" maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('surname')" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autocomplete="username" maxlength="30" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-300">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-400 hover:text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
