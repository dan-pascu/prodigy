@props(['key', 'data'])

<x-prodigy::editor.field-wrapper :width="$data['width'] ?? 100" :key="$key">
        <x-prodigy::editor.label :data="$data" :key="$key" for="block.content.{{$key}}" />

    <div class="pro-flex pro-gap-2" x-data="{
        {{$key}}: @entangle("block.content.{$key}").live,
    }">
        <div>
            <p x-text="{{$key}}"></p>
        </div>
        <input type="range"
               class="pro-w-full"
               x-model="{{$key}}"
               min="{{$data['min'] ?? 0}}"
               max="{{$data['max'] ?? 100}}"
               step="{{$data['step'] ?? 1}}"
               value="{{ $block->content[$key] ?? $data['default'] ?? null }}"/>
    </div>
</x-prodigy::editor.field-wrapper>
