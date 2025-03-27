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


        // ARTICLES
        public int $selectedArticle = 0;

        #[Session]
        public int $perPage = 10;

        public array $searchableFields = ['title', 'pmid'];

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

        public function updatingPerPage(): void
        {
            $this->resetPage();
        }

        // MUTATIONS
        public array $mutationsSearchableFields = ['name'];

        #[Url]
        public string $mutationsSearch = '';

        #[Url]
        public string $mutationsSortField = 'name';

        #[Url]
        public string $mutationsSortDirection = 'asc';

        // PDBS
        #[Session]
        public int $pdbsPerPage = 10;

        public array $pdbsSearchableFields = ['pdb_id'];

        #[Url]
        public string $pdbsSearch = '';

        #[Url]
        public string $pdbsSortField = 'pdb_id';

        #[Url]
        public string $pdbsSortDirection = 'asc';

        public function updatingPdbsSearch(): void
        {
            $this->resetPage('pdbsPage');
        }

        public function updatingPdbsPerPage(): void
        {
            $this->resetPage('pdbsPage');
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

        public function mutationsSortBy($field)
        {
            if ($this->mutationsSortField === $field) {
                $this->mutationsSortDirection = $this->mutationsSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->mutationsSortDirection = 'asc';
            }

            $this->mutationsSortField = $field;
        }

        public function pdbsSortBy($field)
        {
            if ($this->pdbsSortField === $field) {
                $this->pdbsSortDirection = $this->pdbsSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->pdbsSortDirection = 'asc';
            }

            $this->pdbsSortField = $field;
        }

        public function selectArticle(int $id): void
        {
            $this->mutationsSearch = '';
            $this->selectedArticle = $id;
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
                    ->paginate($this->perPage),
                'mutations' => $this->protein->mutations()
                    ->when($this->selectedArticle, function ($query, $id): void {
                        $query->where('article_id', $id);
                    })
                    ->when($this->mutationsSearch, function ($query, $search): void {
                        $query->whereAny($this->mutationsSearchableFields, 'LIKE', "%$search%");
                    })
                    ->orderBy($this->mutationsSortField, $this->mutationsSortDirection)
                    ->orderBy('name')
                    ->get()
                    ->unique('name'),
                'pdbs' => $this->protein->pdbs()
                    ->when($this->pdbsSearch, function ($query, $search): void {
                        $query->whereAny($this->pdbsSearchableFields, 'LIKE', "%$search%");
                    })
                    ->orderBy($this->pdbsSortField, $this->pdbsSortDirection)
                    ->paginate($this->pdbsPerPage, pageName: 'pdbsPage'),
                'all_pdb_ids' => $this->protein->pdbs()->pluck('pdb_id')->toArray()
            ];
        }
    }
?>

<div class="flex w-full flex-col lg:flex-row gap-4">
    <!-- Main Content -->
    <div class="flex flex-col flex-1 gap-10">
        <!-- PMIDS -->
        <div class="bg-gray-50 p-2">
            <div class="flex items-center justify-between w-full mb-6 gap-2">
                <h1 class="mb-4 text-xl font-extrabold text-gray-900 dark:text-white md:text-3xl lg:text-3xl">{{ $protein->name }} <span class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">PMIDs</span></h1>

                <!-- <flux:spacer /> -->

                @if($selectedArticle)
                <div role="alert" class="relative flex w-[50%] md:w-[30%] rounded-md bg-slate-800 p-3 text-sm text-white">
                    <b>Selected: </b> &nbsp; {{ $selectedArticle }}
                    <button
                        wire:click="selectArticle(0)"
                        class="absolute top-1.5 right-1.5 flex h-8 w-8 items-center justify-center rounded-md text-white transition-all hover:bg-white/10 active:bg-white/10"
                        type="button">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            class="h-5 w-5"
                            strokeWidth="2">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                @endif
            </div>


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
                    @foreach ($articles as $index => $article)
                    <x-table.row wire:key="article-{{ $article->id }}">
                        <x-table.cell>{{ $articles->firstItem() + $index }}</x-table.cell>
                        <x-table.cell>{{ $article->pmid }}</x-table.cell>
                        <x-table.cell>{!! $article->title !!}</x-table.cell>
                        <x-table.cell>{{ $article->mutations_count }}</x-table.cell>
                        <x-table.cell class="flex justify-end">
                            <x-text-link class="cursor-pointer" wire:click.prevent="selectArticle({{ $article->id }})">Mutations</x-text-link>
                        </x-table.cell>
                    </x-table.row>
                    @endforeach
                </x-slot:body>
            </x-table>

            <div>
                {{ $articles->links() }}
            </div>
        </div>

        <div class="bg-gray-50 p-2">
            <div class="flex items-center justify-between w-full mb-6 gap-2">
                <h1 class="mb-4 text-3xl font-extrabold text-gray-900 dark:text-white md:text-3xl lg:text-3xl">{{ $protein->name }} <span class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">PDB Ids</span></h1>

                <div role="alert" class="relative flex w-[50%] md:w-[30%] rounded-md bg-slate-800 p-3 text-sm text-white">
                    <a href="https://molstar.org/viewer/?pdb={{ implode(',', $all_pdb_ids) }}&snapshot-url-type=molj&pixel-scale=4" target="_blank">Merge 3D Structures</a>
                </div>

            </div>

            <div class="flex items-center justify-between w-full mb-6 gap-2">
                <flux:input wire:model.live="pdbsSearch" placeholder="{{ __('global.search_here') }}" class="!w-auto" />

                <flux:spacer />

                <flux:select wire:model.live="pdbsPerPage" class="!w-auto">
                    <flux:select.option value="10">{{ __('global.10_per_page') }}</flux:select.option>
                    <flux:select.option value="25">{{ __('global.25_per_page') }}</flux:select.option>
                    <flux:select.option value="50">{{ __('global.50_per_page') }}</flux:select.option>
                    <flux:select.option value="100">{{ __('global.100_per_page') }}</flux:select.option>
                    https://www.rcsb.org/structure/4YJL
                </flux:select>
            </div>
            <x-table>
                <x-slot:head>
                    <x-table.row>
                        <x-table.heading>{{ __('global.id') }}</x-table.heading>
                        <x-table.heading sortable wire:click="pdbsSortBy('pdb_id')" :direction="$pdbsSortField === 'pdb_id' ? $pdbsSortDirection : null">{{ __('pdb.pdb_ids') }}</x-table.heading>
                        <x-table.heading class="text-right">{{ __('global.actions') }}</x-table.heading>
                    </x-table.row>
                </x-slot:head>
                <x-slot:body>
                    @foreach ($pdbs as $index => $pdb)
                    <x-table.row wire:key="pdb-{{ $pdb->id }}">
                        <x-table.cell>{{ $pdbs->firstItem() + $index }}</x-table.cell>
                        <x-table.cell>
                            <a href="https://www.rcsb.org/structure/{{ $pdb->pdb_id }}" target="_blank" class="underline text-sm decoration-neutral-400 underline-offset-2 duration-300 ease-out hover:decoration-neutral-700 text-neutral-900 dark:text-neutral-200 dark:hover:decoration-neutral-100">{{ $pdb->pdb_id }}</a>

                        </x-table.cell>
                        <x-table.cell class="flex justify-end">
                            <a class="underline text-sm decoration-neutral-400 underline-offset-2 duration-300 ease-out hover:decoration-neutral-700 text-neutral-900 dark:text-neutral-200 dark:hover:decoration-neutral-100" href="https://molstar.org/viewer/?pdb={{ $pdb->pdb_id }}" target="_blank">3D Structure Visualization</a>
                        </x-table.cell>
                    </x-table.row>
                    @endforeach
                </x-slot:body>
            </x-table>

            <div>
                {{ $pdbs->links(data: ['scrollTo' => false]) }}
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="w-full lg:w-64 bg-gray-50 p-2">
        <h1 class="mb-4 text-3xl font-extrabold text-gray-900 dark:text-white md:text-3xl lg:text-3xl"><span class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">Mutations</span></h1>

        <div class="flex items-center justify-between w-full mb-6 gap-2">
            <flux:input wire:model.live="mutationsSearch" placeholder="{{ __('global.search_here') }}" class="w-full" />
        </div>

        <x-table>
            <x-slot:head>
                <x-table.row>
                    <x-table.heading>{{ __('global.id') }}</x-table.heading>
                    <x-table.heading sortable wire:click="mutationsSortBy('name')" :direction="$mutationsSortField === 'name' ? $mutationsSortDirection : null">{{ __('mutation.name') }}</x-table.heading>
                </x-table.row>
            </x-slot:head>
            <x-slot:body>
                @foreach ($mutations as $index => $mutation)
                <x-table.row wire:key="mutation-{{ $mutation->id }}">
                    <x-table.cell>{{ $index + 1 }}</x-table.cell>
                    <x-table.cell>{{ $mutation->name }}</x-table.cell>
                </x-table.row>
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
</div>