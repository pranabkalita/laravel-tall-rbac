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

        public Protein $protein;

        #[Session]
        public int $perPage = 10;

        public array $searchableFields = ['title', 'pmids'];

        #[Url]
        public string $search = '';

        #[Url]
        public string $sortField = 'mutations_count';

        #[Url]
        public string $sortDirection = 'desc';

        public function updatingSearch(): void
        {
            $this->resetPage();
        }

        public function mount(Protein $protein): void
        {
            $this->protein = $protein;
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
                'articles' => $this->protein->articles()
                    ->withCount('mutations')
                    ->when($this->search, function ($query, $search): void {
                        $query->whereAny($this->searchableFields, 'LIKE', "%$search%");
                    })
                    ->orderBy($this->sortField, $this->sortDirection)
                    ->paginate($this->perPage)
            ];
        }
    }
?>

<div class="flex h-full w-full flex-1 flex-col md:flex-row md:space-y-0 md:space-x-4">
    <div class="w-full md:w-[70%]">
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
                    <x-table.heading sortable wire:click="sortBy('pmid')" :direction="$sortField === 'pmid' ? $sortDirection : null">{{ __('article.pmid') }}</x-table.heading>
                    <x-table.heading sortable wire:click="sortBy('title')" :direction="$sortField === 'title' ? $sortDirection : null">{{ __('article.title') }}</x-table.heading>
                    <x-table.heading sortable wire:click="sortBy('mutations_count')" :direction="$sortField === 'mutations_count' ? $sortDirection : null">{{ __('article.mutations_count') }}</x-table.heading>
                    <x-table.heading class="text-right">{{ __('global.actions') }}</x-table.heading>
                </x-table.row>
            </x-slot:head>
            <x-slot:body>
                @foreach ($articles as $article)
                <x-table.row wire:key="article-{{ $article->id }}">
                    <x-table.cell>{{ $article->id }}</x-table.cell>
                    <x-table.cell>{{ $article->pmid }}</x-table.cell>
                    <x-table.cell>{!! $article->title !!}</x-table.cell>
                    <x-table.cell>{{ $article->mutations_count }}</x-table.cell>
                    <x-table.cell class="flex justify-end">
                        <x-text-link href="#">Mutations</x-text-link>
                    </x-table.cell>
                </x-table.row>
                @endforeach
            </x-slot:body>
        </x-table>

        <div>
            {{ $articles->links() }}
        </div>
    </div>

    <div class="w-full md:w-[30%]"></div>
</div>