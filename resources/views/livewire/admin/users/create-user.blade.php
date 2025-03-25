<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Models\User;

new
#[Layout('components.layouts.admin')]
#[Title('Create User')]
class extends Component
{
    use LivewireAlert;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|max:2')]
    public string $locale = 'en';

    /** @var array<mixed> */
    #[Validate('nullable|array')]
    public array $selectedRoles = [];

    public function mount(): void
    {
        $this->authorize('create users');
    }

    public function createUser(): void
    {
        $this->validate();

        $user = User::query()->create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make(Str::random(16)),
            'locale' => $this->locale,
        ]);

        if ($this->selectedRoles !== []) {
            /** @var User $user */
            // Convert the userRoles to integers
            $userRoles = Arr::map($this->selectedRoles, fn ($role): int => (int) $role);

            // Sync the user roles
            $user->syncRoles($userRoles);
        }

        $this->flash('success', __('users.user_created'));

        $this->redirect(route('admin.users.index'), true);

    }

    public function with(): array
    {
        return [
            'roles' => Role::all(),
            'locales' => [
                'en' => 'English',
                'da' => 'Danish',
            ],
        ];
    }
}
?>

<section class="w-full">
    <x-page-heading>
        <x-slot:title>{{ __('users.create_user') }}</x-slot:title>
        <x-slot:subtitle>
            {{ __('users.create_user_description') }}
        </x-slot:subtitle>
    </x-page-heading>

    <x-form wire:submit="createUser" class="space-y-6">

        <flux:input wire:model.live="name" label="Name" />
        <flux:input wire:model.live="email" label="E-mail" />

        <flux:select wire:model="locale" label="{{ __('users.select_locale') }}" placeholder="{{ __('users.select_locale') }}" name="locale">
            @foreach($locales as $key => $locale)
                <flux:select.option value="{{ $key }}">{{ $locale }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:checkbox.group wire:model.live="selectedRoles" label="{{ __('users.roles') }}" description="{{ __('users.roles_description') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @foreach($roles as $role)
                <flux:checkbox label="{{$role->name}}" value="{{$role->id}}"/>
            @endforeach
        </flux:checkbox.group>

        <flux:button type="submit" icon="save" variant="primary">
            {{ __('users.create_user') }}
        </flux:button>
    </x-form>

</section>
