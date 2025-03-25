<?php

use App\Models\Protein;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

new
    #[Layout('components.layouts.app')]
    #[Title('Proteins')]
    class extends Component
    {
        use LivewireAlert;
        use WithPagination;

        #[Session]
        public int $perPage = 10;

        public array $searchableFields = ['name'];

        #[Url]
        public string $search = '';

        #[Url]
        public string $sortField = 'name';

        #[Url]
        public string $sortDirection = 'asc';


        public function updatingSearch(): void
        {
            $this->resetPage();
        }

        public function sortBy($field)
        {
            if ($this->sortField === $field) {
                $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortDirection = 'asc';
            }

            $this->sortField = $field;
        }

        public function with(): array
        {
            return [
                'proteins' => Protein::select('proteins.*')
                    ->selectRaw('(
                        SELECT COUNT(DISTINCT m.name)
                        FROM mutations AS m
                        LEFT JOIN articles AS a ON a.id = m.article_id
                        WHERE a.protein_id = proteins.id
                    ) as mutations_count')
                    ->withCount('articles')
                    ->when($this->search, function ($query, $search): void {
                        $query->whereAny($this->searchableFields, 'LIKE', "%$search%");
                    })
                    ->orderBy($this->sortField, $this->sortDirection)
                    ->paginate($this->perPage)
                    ->withQueryString()
            ];
        }
    }
?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl min-h-[40rem]">
    <div class="flex items-center justify-between w-full mb-6 gap-2">
        <flux:input wire:model.live="search" placeholder="{{ __('global.search_here') }}" class="!w-auto" />

        <flux:spacer />

        <flux:select wire:model.live="perPage" class="!w-auto">
            <flux:select.option value="10">{{ __('global.10_per_page') }}</flux:select.option>
            <flux:select.option value="25">{{ __('global.25_per_page') }}</flux:select.option>
            <flux:select.option value="50">{{ __('global.50_per_page') }}</flux:select.option>
            <flux:select.option value="100">{{ __('global.100_per_page') }}</flux:select.option>
        </flux:select>
    </div>

    <x-table>
        <x-slot:head>
            <x-table.row>
                <x-table.heading>{{ __('global.id') }}</x-table.heading>
                <x-table.heading sortable wire:click="sortBy('name')" :direction="$sortField === 'name' ? $sortDirection : null">{{ __('protein.name') }}</x-table.heading>
                <x-table.heading sortable wire:click="sortBy('articles_count')" :direction="$sortField === 'articles_count' ? $sortDirection : null">{{ __('protein.pmids') }}</x-table.heading>
                <x-table.heading sortable wire:click="sortBy('mutations_count')" :direction="$sortField === 'mutations_count' ? $sortDirection : null">{{ __('protein.mutations') }}</x-table.heading>
                <x-table.heading class="text-right">{{ __('global.actions') }}</x-table.heading>
            </x-table.row>
        </x-slot:head>
        <x-slot:body>
            @foreach ($proteins as $protein)
            <x-table.row wire:key="protein-{{ $protein->id }}">
                <x-table.cell>{{ $protein->id }}</x-table.cell>
                <x-table.cell>
                    <x-text-link href="#">{{ $protein->name }}</x-text-link>
                </x-table.cell>
                <x-table.cell>{{ $protein->articles_count }}</x-table.cell>
                <x-table.cell>{{ $protein->mutations_count }}</x-table.cell>
                <x-table.cell class="flex justify-end">
                    <x-text-link href="#">Mutations List</x-text-link>
                </x-table.cell>
            </x-table.row>
            @endforeach
        </x-slot:body>
    </x-table>

    <div>
        {{ $proteins->links() }}
    </div>
</div>