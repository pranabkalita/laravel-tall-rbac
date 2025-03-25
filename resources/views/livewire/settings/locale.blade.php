<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.app')]
#[Title('Locale')]
class extends Component
{
    public string $locale = '';

    public function mount(): void
    {
        $this->locale = auth()->user()->locale;
    }

    public function updateLocale(): void
    {
        $this->validate([
            'locale' => 'required|string|in:en,da',
        ]);

        auth()->user()->update([
            'locale' => $this->locale,
        ]);

        $this->dispatch('locale-updated', name: auth()->user()->name);
    }

    public function with(): array
    {
        return [
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
        <x-slot:title>{{ __('settings.title') }}</x-slot:title>
        <x-slot:subtitle>{{ __('settings.subtitle') }}</x-slot:subtitle>
    </x-page-heading>

    <x-settings.layout :heading="__('users.locale')" :subheading="__('users.locale_description')">
        <form wire:submit="updateLocale" class="my-6 w-full space-y-6">
            <flux:select wire:model="locale" placeholder="{{ __('users.select_locale') }}" name="locale">
                @foreach($locales as $key => $locale)
                    <flux:select.option value="{{ $key }}">{{ $locale }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('global.save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="locale-updated">
                    {{ __('global.saved') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>

</section>
