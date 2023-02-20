<div class="pro-px-4">
    @foreach($groups as $group)
        @foreach($group['folders'] as $folder)
            <div class="pro-w-full pro-pb-4" x-data="{ expanded: true }">
                <div @click="expanded = ! expanded" class=" px-2 pb-2 pro-bg-gray-200">
                    <svg :class="{ 'pro--rotate-90': !expanded }" xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 20 20"
                         fill="currentColor"
                         class="pro-w-5 pro-h-5 pro-inline-block pro-mr-1 pro-transform pro-transition">
                        <path fill-rule="evenodd"
                              d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span class=" pro-py-1 pro-text-sm pro-font-medium pro-inline-block hover:pro-cursor-default">{{ $group['title'] }} - {{ $folder['title'] }}</span>
                </div>

                <div x-show="expanded" class="pro-flex pro-flex-wrap pro-pt-1">

                    <div class="pro-grid pro-grid-cols-2 pro-w-full">
                        @foreach ($folder['blocks'] as $block)
                            <div
                                    draggable="true"
                                    ondragstart="event.dataTransfer.setData('text/plain', '{{ $block['key'] }}');"
                                    class="pro-text-sm hover:pro-bg-white pro-flex-grow hover:pro-border-gray-100 hover:pro-shadow-sm pro-rounded-sm pro-transition pro-cursor-grab pro-px-4 pro-py-3">
                                 {{ $block['title'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
</div>