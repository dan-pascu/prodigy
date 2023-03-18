<?php

namespace ProdigyPHP\Prodigy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ProdigyPHP\Prodigy\Actions\DeleteBlockChildrenAction;
use ProdigyPHP\Prodigy\Database\Factories\BlockFactory;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Block extends Model implements HasMedia {

    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'prodigy_blocks';

    protected $guarded = [];

    protected $casts = [
        'content' => 'collection',
        'meta' => 'collection'
    ];

    /**
     * Get the parent models (block or page).
     */
    public function prodigy_links(): MorphTo
    {
        return $this->morphTo();
    }

    public function parentBlock(): Block
    {
        return $this->morphedByMany(Block::class, 'prodigy_links')->first();
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'prodigy_links');
    }

    public function children(): MorphToMany
    {
        return $this->morphToMany(Block::class, 'prodigy_links')->withPivot('column', 'order', 'id')->orderByPivot('order');
    }

    public function getIsRepeaterAttribute()
    {
        return str($this->key)->contains('prodigy::blocks.basic.repeater');
    }

    public function getTitleAttribute()
    {
        return Str::of($this->key)->afterLast('.')->replace('-', ' ')->title();
    }


    protected static function newFactory(): BlockFactory
    {
        return new BlockFactory();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('prodigy');
    }

    /**
     * We are recursively iterating through all the children to
     * get our children moved over to the newParent. If it's
     * global, we just attach it. Otherwise, we need to manually
     * create all the new blocks.
     */
    public function deepCopy(Page|Block $newParent, Block &$oldBlock)
    {
        $order = $oldBlock->pivot->order ?? 1;
        $column = $oldBlock->pivot->column ?? null;

        if ($oldBlock->is_global) {
            $newParent->children()->attach($oldBlock->id, ['order' => $order, 'column' => $column]);
            return;
        }

        $newBlock = $oldBlock->replicate();
        $newBlock->save();

        if($oldBlock->hasMedia('prodigy')) {
            $this->syncMedia($oldBlock, $newBlock);
        }

        $newParent->children()->attach($newBlock->id, ['order' => $order, 'column' => $column]);

        foreach ($oldBlock->children()->get() as $child) {
            static::deepCopy($newBlock, $child);
        }

    }

    public function syncMedia(Block $oldBlock, Block $newBlock) : Media
    {
        $media = $oldBlock->getFirstMedia('prodigy');

        return $media->copy($newBlock, 'prodigy');
    }


    protected static function booted(): void
    {
        static::deleting(function (Block $block) {
            (new DeleteBlockChildrenAction())->execute($block);
        });
    }


}
