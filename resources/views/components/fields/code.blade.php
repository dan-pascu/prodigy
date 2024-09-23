@props(['key', 'data'])

@php
    // Put a random number on the editor to allow multiple.
    $random_number = rand(1, 999999);
@endphp

<x-prodigy::editor.field-wrapper :width="$data['width'] ?? 100" :key="$key">
    <x-prodigy::editor.label :data="$data" :key="$key" for="block.content.{{$key}}"/>

    <div wire:ignore>
        <textarea wire:model.live="block.content.{{$key}}" id="prodigyCodeEditor-{{ $random_number }}">{!! $block->content[$key] ?? '' !!}</textarea>
    </div>


    <script>
            var editor = CodeMirror.fromTextArea(document.getElementById("prodigyCodeEditor-{{ $random_number }}"), {
                lineNumbers: true,
                viewportMargin: Infinity
            });

            editor.on("change", function (cm, change) {
                @this.
                set("block.content.{{$key}}", cm.getValue());
            })
    </script>
</x-prodigy::editor.field-wrapper>
