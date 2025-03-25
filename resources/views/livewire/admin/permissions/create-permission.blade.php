<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Permission;

new
#[Layout('components.layouts.admin')]
#[Title('Create Permission')]
class extends Component
{
    use LivewireAlert;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function createPermission(): void
    {
        $this->authorize('create permissions');

        $this->validate();

        Permission::create([
            'name' => $this->name,
        ]);

        $this->flash('success', __('permissions.permission_created'));

        $this->redirect(route('admin.permissions.index'), true);

    }
}
?>

<section class="w-full">
    <x-page-heading>
        <x-slot:title>
            {{__('permissions.create_permission')}}
        </x-slot:title>
        <x-slot:subtitle>
            {{__('permissions.create_permission_description')}}
        </x-slot:subtitle>
    </x-page-heading>

    <x-form wire:submit="createPermission" class="space-y-6">
        <flux:input wire:model.live="name" label="{{ __('permissions.name') }}" />
        <flux:button type="submit" icon="save" variant="primary">
            {{ __('permissions.create_permission') }}
        </flux:button>
    </x-form>

</section>
