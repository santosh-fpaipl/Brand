{{-- 
 $modelName : It is model name used for generating unique id of input field.   
 $name :  It is field name of table of database
 $label :  It ia used as label
 $sortable : it control that sortable section(Asc or Desc) of field will be created or not .    
--}}
<th>
    <div class="d-flex">
        <label for="select{{ $modelName }}{{ $name }}" class="form-label pe-2">{{ $label }}</label>
        @if (!empty($sortable))
            <select id="select{{ $modelName }}{{ $name }}" style="width: fit-content" class="form-select form-select-sm"
                aria-label=".form-select-sm example" wire:model="sortSelect">
                <option value="">Select SortBy</option>
                <option value="{{ $name }}#asc">{{ $name }} Asc</option>
                <option value="{{ $name }}#desc">{{ $name }} Desc</option>
            </select>
        @endif
    </div>
</th>