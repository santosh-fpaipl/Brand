{{-- 
    $label : It ia used as label.
    $model : It is model object.
    $attribute : It is a array of arttibutes values of input fields.  
    $modelName : It is a model name used for generating unique id of input fields.
    $name : It is field name of table of database.
    $type : It is a type's value of input field like text, file etc.
    $style : It is class name.
    $message : It is a error message.
--}}
@php
    if(!empty($model)){
        if (($key = array_search('required', $attribute)) !== false) {
            unset($attribute[$key]);
        }
    }
@endphp 

<div class="mb-3">
    @if (!empty($label))  
        <label for="file{{ $modelName }}{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <input 
        id="file{{ $modelName }}{{ $name }}" 
        type="{{ empty($type) ? 'file' : $type }}" 
        class="form-control {{ empty($style) ? '' : $style }}" 
        name="{{ in_array('multiple',$attribute) ? $name.'[]' : $name }}" 
        {{ empty($attribute) ? '' : implode(' ', $attribute)  }}
        @if($show) disabled @endif
    >
    @error(in_array('multiple',$attribute) ? $name.'.*' : $name)
        <span class="input_val_error">{{ $message }}</span>
    @enderror

</div>

@if(!empty($model))
    @if(in_array('multiple',$attribute))
        @if (count($model->getImage('s400', 'secondary', true)))
            <div class="mb-3">
                <label for="file{{ $modelName }}ImageDeleteLabel" class="form-label">{{ $messages['image_delete'] }}</label>
            </div>
        @endif

        <div class="mb-3 d-flex">
            @foreach ($model->getImage('s400', 'secondary', true) as $mediaItemId => $mediaItemUrl)
                <div class="form-check">
                    <input id="input{{ $modelName }}Image{{ $mediaItemId }}" type="checkbox" class="form-check-input"
                        name="delete_images[]" value="{{ $mediaItemId }}" />
                    <label class="form-check-label" for="input{{ $modelName }}Image{{ $mediaItemId }}">
                        <img src="{{ $mediaItemUrl }}" class="img-thumbnail" width="50" height="50" />
                    </label>
                </div>
            @endforeach
        </div>
    @else
        <div class="mb-3">
            @if (!empty($model->getImage('s100', 'primary')))
                <img src="{{ $model->getImage('s100', 'primary') }}" class="img-thumbnail" width="50"
                    height="50" />
            @endif
        </div>
    @endif
@endif