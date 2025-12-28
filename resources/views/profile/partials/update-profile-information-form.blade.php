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
        <div x-data="{ fileName: '{{ __('No file chosen') }}' }">
            <x-input-label for="profile_image" :value="__('Profile Image')" />
            <div class="mt-2 flex items-center gap-4">
                <img id="profile_image_preview" src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover">
                <div>
                    <div class="flex items-center mt-1">
                        <label for="profile_image" class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Choose file') }}
                        </label>
                        <input id="profile_image" name="profile_image" type="file" class="hidden"
                               @change="fileName = $event.target.files.length > 0 ? $event.target.files[0].name : '{{ __('No file chosen') }}';
                                         if ($event.target.files.length > 0) {
                                             document.getElementById('profile_image_preview').src = window.URL.createObjectURL($event.target.files[0])
                                         }" accept="image/jpeg,image/png">
                        <span class="ms-3 text-sm text-gray-400" x-text="fileName"></span>
                    </div>
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
            <x-input-label for="username">{{ __('Username') }}<span class="text-red-500">*</span></x-input-label>
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autocomplete="username" maxlength="30" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email">{{ __('Email') }}<span class="text-red-500">*</span></x-input-label>
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
