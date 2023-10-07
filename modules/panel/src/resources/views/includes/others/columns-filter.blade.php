{{-- 
    $modelName : It is model name used for generating unique id of input field.
    $field['name'] : It is field name of table of database
    $activePage : It's value will be either trash or active. It is used to get the value in trash or active mode.  
    $field['labels']['table'] :  It ia used as label
--}}
<div class="ms-auto d-flex">
    <div class="dropdown">
        <button class="btn text-muted border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            {{ $features['column_filter']['label'] }}
        </button>
        <ul class="dropdown-menu">
          @foreach ($fields as $field)
            @if ($field['filterable'][$activePage])
                
                    <li>
                        <div class="dropdown-item d-flex align-items-center">
                            <input 
                                class="form-check-input" 
                                type="checkbox" id="input{{ $modelName }}{{$field['name'] }}" 
                                wire:model="fields.{{ $field['name'] }}.viewable.{{ $activePage }}">
                            <label 
                                class="form-check-label px-1 flex-fill"
                                for="input{{ $modelName }}{{ $field['name'] }}" style="width:max-content;">
                                {{$field['labels']['table'] }}
                            </label>
                        </div>
                    </li>
            @endif
        @endforeach
        </ul>
    </div>
</div>