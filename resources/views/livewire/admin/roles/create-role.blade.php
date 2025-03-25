<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new
#[Layout('components.layouts.admin')]
#[Title('Create Role')]
class extends Component
{
    use LivewireAlert;

    #[Validate('required|string|max:255')]
    public string $name = '';

    /** @var array<mixed> */
    #[Validate('array|min:1')]
    public array $selectedPermissions = [];

    public function mount(): void
    {
        $this->authorize('create roles');
    }

    public function createRole(): void
    {
        $this->authorize('create roles');

        $this->validate();

        $role = Role::create([
            'name' => $this->name,
        ]);

        $permissions = collect($this->selectedPermissions)->map(fn ($permission): int =>
            // convert string to int
        (int) $permission)->toArray();

        $role->syncPermissions($permissions);

        $this->flash('success', __('roles.role_created'));

        $this->redirect(route('admin.roles.index'), true);

    }

    public function with()
    {
        return [
            'permissions' => Permission::all(),
        ];
    }
}
?>

<section class="w-full">
    <x-page-heading>
        <x-slot:title>
            {{ __('roles.create_role') }}
        </x-slot:title>
        <x-slot:subtitle>
            {{ __('roles.create_role_description') }}
        </x-slot:subtitle>

    </x-page-heading>

    <x-form wire:submit="createRole" class="space-y-6">
        <flux:input wire:model.live="name" label="{{ __('roles.name') }}"/>

        <flux:checkbox.group wire:model.live="selectedPermissions" label="{{ __('roles.permissions') }}" description="{{ __('roles.permissions_description') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @foreach($permissions as $permission)
                <flux:checkbox label="{{$permission->name}}" value="{{$permission->id}}"/>
            @endforeach
        </flux:checkbox.group>

        <flux:button type="submit" icon="save" variant="primary">
            {{ __('roles.create_role') }}
        </flux:button>
    </x-form>
</section>
