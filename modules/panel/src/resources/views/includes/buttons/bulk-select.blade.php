{{-- 
   $selectPage: It's value will be boolean and it show that bulk select of a page is checked.
   $selectAll : It's value will be boolean and it show that bulk select of All Page is checked.
   $modelName : It is model name used for generating unique id of input field.
--}}
@php
    $anyOptionSelected = false;
    foreach ($labels['options'] as $option) {
        if (${$option['binding']}) {
            $anyOptionSelected = true;
        }
    }
@endphp

<div class="dropdown">
    <button class="btn border-0 px-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        @if ($anyOptionSelected)
            <i class="bi bi-check-square"></i>
        @else
            <i class="bi bi-square"></i>
        @endif
    </button>
    <ul class="dropdown-menu">
        <li>
            <h6 class="dropdown-header">{{ $labels['heading'] }}</h6>
        </li>
        @foreach($labels['options'] as $option)
            <li>
                <div class="dropdown-item">
                    <div class="form-check">
                        <input class="form-check-input border-dark rounded-0" type="checkbox" wire:model="{{ $option['binding'] }}"
                            id="input{{ $modelName }}{{ Str::slug($option['name']) }}">
                        <label class="form-check-label ps-1" for="input{{ $modelName }}{{ Str::slug($option['name']) }}">
                            {{ $option['name'] }}
                        </label>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>