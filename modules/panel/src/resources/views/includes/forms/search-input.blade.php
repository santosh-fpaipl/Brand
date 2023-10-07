{{--
    $modelName :It is a model name used for generating unique id of input fields.
    $function :  It is a function name.
    $placeholder : It contain the placeholder value of input field.
    $style : It is a class name.
    $attribute : It is a array of arttibutes values of input fields. 
    $escape_key : I has function name that will be executed when we press escape key.

--}}
@php
    empty($function) ? dd('Forget to pass function paran in search input') : '';
@endphp

<input 
    id="search{{ $modelName }}"
    type="search" 
    wire:model.lazy="{{ $function }}" 
    placeholder="{{ empty($label) ? 'Search' : $label }}"
    class="form-control {{ empty($style) ? '' : $style }}"
    {{ empty($attribute) ? '' : implode(' ', $attribute)  }}
    wire:keydown.escape="escapeKeyDetect"
>