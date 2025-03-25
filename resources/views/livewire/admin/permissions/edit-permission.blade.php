<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Permission;

new
#[Layout('components.layouts.admin')]
#[Title('Edit Permission')]
class extends Component
{
    use LivewireAlert;

    public Permission $permission;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function mount(Permission $permission): void
    {
        $this->authorize('update permissions');

        $this->permission = $permission;
        $this->name = $permission->name;
    }

    public function savePermission(): void
    {
        $this->authorize('update permissions');

        $this->validate();

        $this->permission->update([
            'name' => $this->name,
        ]);

        $this->flash('success', __('permissions.permission_updated'));

        $this->redirect(route('admin.permissions.index'), true);

    }
}
?>

<section class="w-full">
    <x-page-heading>
        <x-slot:title>
            {{ __('permissions.edit_permission') }}
        </x-slot:title>
        <x-slot:subtitle>
            {{ __('permissions.edit_permission_description') }}
        </x-slot:subtitle>
    </x-page-heading>

    <x-form wire:submit="savePermission" class="space-y-6">
        <flux:input wire:model.live="name" label="{{ __('permissions.name') }}" />
        <flux:button type="submit" icon="save" variant="primary">
            {{ __('permissions.update_permission') }}
        </flux:button>
    </x-form>

</section>
