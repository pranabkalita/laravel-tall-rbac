<?php

use App\Models\User;
use Illuminate\Support\Arr;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportRedirects\HandlesRedirects;
use Spatie\Permission\Models\Role;

new
#[Layout('components.layouts.admin')]
#[Title('Edit User')]
class extends Component
{
    use HandlesRedirects;
    use LivewireAlert;

    public User $user;

    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';

    #[Validate(['required', 'string', 'email', 'max:255'])]
    public string $email = '';

    #[Validate('required|string|max:2')]
    public string $locale = 'en';

    /** @var array <int,string> */
    public array $userRoles = [];

    public function mount(User $user): void
    {
        $this->authorize('update users');

        $this->user = $user;
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->locale = $this->user->locale ?? 'en';

        // get user roles
        $this->userRoles = $this->user->roles->pluck('id')->toArray();
    }

    public function updateUser(): void
    {
        $this->authorize('update users');

        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        // Convert the userRoles to integers
        $userRoles = Arr::map($this->userRoles, fn ($role): int => (int) $role);

        // Sync the user roles
        $this->user->syncRoles($userRoles);

        $this->flash('success', __('users.user_updated'));

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
        <x-slot:title>
            {{ __('users.edit_user') }}
        </x-slot:title>
        <x-slot:subtitle>
            {{ __('users.edit_user_description') }}
        </x-slot:subtitle>
    </x-page-heading>

    <x-form wire:submit="updateUser" class="space-y-6">
        <flux:input wire:model.live="name" label="{{ __('users.name') }}"/>

        <flux:input wire:model.live="email" label="{{ __('users.email') }}"/>

        <flux:select wire:model="locale" label="{{ __('users.select_locale') }}" placeholder="{{ __('users.select_locale') }}" name="locale">
            @foreach($locales as $key => $locale)
                <flux:select.option value="{{ $key }}">{{ $locale }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:checkbox.group wire:model.live="userRoles" label="{{ __('users.roles') }}" description="{{ __('users.roles_description') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @foreach($roles as $role)
                <flux:checkbox label="{{$role->name}}" value="{{$role->id}}"/>
            @endforeach
        </flux:checkbox.group>

        <flux:button type="submit" icon="save" variant="primary">
            {{ __('users.update_user') }}
        </flux:button>
    </x-form>

</section>
