<div class="btn-group">
    @foreach ($buttonsTable as $button)
        @if ($button['show'][$activePage])
            @include('panel::includes.' . $button['type'], [
                'bulkDisabled' => $bulkDisabled, 
                'title' => $button['label'],
                'style' => $button['style'],
                'action' => $button['route'] ? $button['route'] : $button['function'],
                // Extra Params
                'data' => $model, 
                'queryValue' => $currentPage, 
                'confirm' => $button['confirm'],
                'queryName' => "page",
            ])
        @endif
    @endforeach
</div>