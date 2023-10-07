{{-- 
    $action : Function name
    $bulkDisabled : Control enable and disable of button (this component access $bulkDisabled directly from livewire class in which it has been called upon)
    $style : Class name
    $title : It is a label
--}}


@if (!empty($confirm) && $confirm)

    @php
        if (!empty($queryName) && !empty($queryValue) && !empty($data)) {
            $route = route($action, [$data, $queryName => $queryValue]);
        } else {
            $route = route($action);
        }
    @endphp
    
    <button 
        type="button" 
        wire:click="setRoute('{{ $route }}')" 
        class="btn btn-outline-dark {{ $style }}" 
        data-bs-toggle="modal" data-bs-target="#confirmModal">
        {{ $title }}
    </button>    
@else
    <button 
        type="button" 
        wire:click="{{ $action }}"
        class="btn btn-outline-dark {{ $style }} 
        @if (($action == 'deleteSelectedRecord' || $action == 'restoreSelectedRecords') && $bulkDisabled) disabled @endif"
    >
    {{ $title }}
    </button>
@endif

