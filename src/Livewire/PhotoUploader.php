<?php

namespace ProdigyPHP\Prodigy\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PhotoUploader extends Component
{
    use WithFileUploads;

    public $photo;

    public HasMedia $block;

    public string $key;

    public string $file_key;

    public ?Media $preview;

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(HasMedia $block, string $array_key)
    {
        $this->block = $block;
        $this->key = $array_key;
    }

    protected $rules = [
        'photo' => 'image|max:4096',
    ];

    public function updatedPhoto()
    {
        $this->save();
        $this->dispatch('fireGlobalRefresh');
        $this->dispatch('refresh')->self();
    }

    public function save()
    {
        Gate::authorize('viewProdigy', auth()->user());
        $this->validate();

        $this->block
            ->addMedia($this->photo->getRealPath())
            ->usingName($this->photo->getClientOriginalName())
            ->withCustomProperties(['key' => $this->key])
            ->toMediaCollection('prodigy');
    }

    public function delete()
    {
        Gate::authorize('viewProdigy', auth()->user());

        $this->block->getMedia('prodigy', ['key' => $this->key])->first()->delete();
        $this->dispatch('fireGlobalRefresh');
        $this->dispatch('refresh')->self();
    }

    public function render()
    {
        $this->preview = $this->block->getMedia('prodigy', ['key' => $this->key])->first();

        return view('prodigy::partials.photo-uploader');
    }
}
