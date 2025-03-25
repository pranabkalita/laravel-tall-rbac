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
#[Title('Edit Role')]
class extends Component
{
    use LivewireAlert;

    public Role $role;

    #[Validate('required|string|max:255')]
    public string $name = '';

    /** @var array<mixed> */
    #[Validate('array|min:1')]
    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        $this->authorize('update roles');

        $this->role = $role;

        $this->name = $role->name;

        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

    }

    public function editRole(): void
    {
        $this->authorize('update roles');

        $this->validate();

        $this->role->update([
            'name' => $this->name,
        ]);

        // convert string to int
        $permissions = collect($this->selectedPermissions)->map(fn ($permission): int => (int) $permission)->toArray();

        $this->role->syncPermissions($permissions);

        $this->flash('success', __('roles.role_updated'));

        $this->redirect(route('admin.roles.index'), true);
    }

    public function with(): array
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
            {{ __('roles.edit_role') }}
        </x-slot:title>
        <x-slot:subtitle>
            {{ __('roles.edit_role_description') }}
        </x-slot:subtitle>

    </x-page-heading>

    <x-form wire:submit="editRole" class="space-y-6">
        <flux:input wire:model.live="name" label="{{ __('roles.name') }}"/>

        <flux:checkbox.group wire:model.live="selectedPermissions" label="{{ __('roles.permissions') }}" description="{{ __('roles.permissions_description') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @foreach($permissions as $permission)
                <flux:checkbox label="{{$permission->name}}" value="{{$permission->id}}"/>
            @endforeach
        </flux:checkbox.group>

        <flux:button type="submit" icon="save" variant="primary">
            {{ __('roles.update_role') }}
        </flux:button>
    </x-form>
</section>
