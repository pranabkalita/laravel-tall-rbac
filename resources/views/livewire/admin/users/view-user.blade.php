<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\User;

new
    #[Layout('components.layouts.admin')]
    #[Title('View User')]
    class extends Component
    {
        public User $user;

        public function mount(User $user): void
        {
            $this->authorize('view users');

            $this->user = $user;
        }
    }
?>

<section class="w-full">
    <x-page-heading>
        <x-slot:title>View User</x-slot:title>
        <x-slot:subtitle>Viewing user {{ $user->name }}</x-slot:subtitle>
    </x-page-heading>
</section>