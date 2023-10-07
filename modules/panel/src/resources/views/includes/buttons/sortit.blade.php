{{-- 
 $modelName : It is model name used for generating unique id of input field.   
 $name :  It is field name of table of database
 $label :  It ia used as label
 $sortable : it control that sortable section(Asc or Desc) of field will be created or not .    
--}}
<th>
    <div class="d-flex">
        {{-- <div class="fw-bold {{ empty($sortable) ? 'text-center w-100' : '' }}">{{ $label }}</div> --}}
        <div class="fw-bold w-100 text-{{ $align }}">
            {{ $label }}
            @if (!empty($sortable))
                @if (Str::beforeLast($sortSelect, '#') == $name)
                    @if (Str::afterLast($sortSelect, '#') == 'asc')
                        <button 
                            type="button" 
                            class="ms-2 btn py-0 px-1 border-0 btn-primary" 
                            wire:click="toggleSort('{{ $name . '#desc' }}')">
                            <i class="bi bi-sort-down"></i>
                        </button>
                    @endif
                    @if (Str::afterLast($sortSelect, '#') == 'desc')
                        <button 
                            type="button"
                            class="ms-2 btn py-0 px-1 border-0 btn-primary" 
                            wire:click="toggleSort('{{ $name . '#asc' }}')">
                            <i class="bi bi-sort-up"></i>
                        </button>
                    @endif
                @else
                    <button 
                        type="button" 
                        class="ms-2 btn py-0 px-1 border-0 text-muted" 
                        wire:click="toggleSort('{{ $name . '#asc' }}')">
                        <i class="bi bi-justify"></i>
                    </button>
                @endif
            @endif
        </div>
    </div>
</th>