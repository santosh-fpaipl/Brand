{{-- 
    $modelName : It is model name used for generating unique id of input field.
    $name : It is field name of table of database
    $view : It's value will be either trash or active. It is used to get the value in trash or active mode.  
    $label :  It ia used as label
--}}

{{-- <x-datatable.buttons.filter-column 
                    :modelName="$modelName"
                    :name="$field['name']"
                    :label="$field['labels']['table']"
                    :view="$activePage" /> --}}

<li>
    <div class="dropdown-item d-flex align-items-center">
        <input 
            class="form-check-input" 
            type="checkbox" id="input{{ $modelName }}{{ $name }}" 
            wire:model="feilds.{{ $name }}.viewable.{{ $view }}">
        <label 
            class="form-check-label px-1 flex-fill"
            for="input{{ $modelName }}{{ $name }}" style="width:max-content;">
            {{ $label }}
        </label>
    </div>
</li>