<?php

namespace ProdigyPHP\Prodigy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use ProdigyPHP\Prodigy\Models\Block;
use ProdigyPHP\Prodigy\Models\Page;

class Editor extends Component {

    public Page $page;

    public Collection $blocks;
    public Collection $groups;

    public ?Block $editing_block;

    protected $listeners = ['editingBlock'];

    public function mount(Page $page)
    {
        $this->page = $page;
    }

    public function render()
    {
        $this->getBlocks();
        return view('prodigy::editor');
    }

    public function editingBlock($id) {
        $this->editing_block = null; // clear anything else out first.
        $this->editing_block = Block::find($id);
    }

    public function getBlocks()
    {
        $path = resource_path('views/components/blocks');
        $files = collect(File::allFiles($path));
        $directories = collect(File::directories($path));


        $this->groups = $directories->map(function ($directory) {
            $slugged_name = basename($directory); // goes from /components/blocks/group-name to 'group-name'
            return [
                'slug' => $slugged_name,
                'title' => Str::of($slugged_name)->replace('-', ' ')->title()->toString()
            ];
        });

        $this->groups->prepend(
            [
                'slug' => 'blocks',
                'title' => 'General Blocks'
            ]
        );


        /**
         * Remove json schemas, only get PHP files.
         */
        $this->blocks = $files
            ->filter(fn($file) => $file->getExtension() == 'php')
            ->map(function ($file) {
                $basename = $file->getBasename();
                return [
                    'title' => Str::of($basename)->remove('.blade.php')->replace('-', ' ')->title()->toString(),
                    'basename' => $basename,
                    'pathname' => $file->getPathname(),
                    'group' => basename(dirname($file->getPathname())),
                ];
            });

    }

}
