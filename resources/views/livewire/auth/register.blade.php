<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

new
#[Layout('components.layouts.auth')]
#[Title('Register')]
class extends Component {
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}
?>

<div class="flex flex-col gap-6">
    <x-auth-header title="{{ __('global.create_an_account') }}" description="{{ __('global.create_an_account_description') }}" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            id="name"
            :label="__('users.name')"
            type="text"
            name="name"
            required
            autofocus
            autocomplete="name"
            placeholder="{{ __('users.your_full_name') }}"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            id="email"
            :label="__('global.email_address')"
            type="email"
            name="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            id="password"
            :label="__('global.password')"
            type="password"
            name="password"
            required
            autocomplete="new-password"
            :placeholder="__('global.password')"
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            id="password_confirmation"
            :label="__('global.confirm_password')"
            type="password"
            name="password_confirmation"
            required
            autocomplete="new-password"
            :placeholder="__('global.confirm_password')"
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('global.create_an_account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>
            {{ __('global.already_have_an_account') }}
        </span>
        <flux:link :href="route('login')" wire:navigate>
            {{ __('global.sign_in') }}
        </flux:link>
    </div>
</div>
