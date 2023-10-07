{{-- 
    $modelName : It is model name used for generating unique id of input field.
    $model : It is model object.
--}}
<input 
    type="checkbox" 
    class="form-check-input border-dark" 
    id="input-{{ $modelName }}-checkbox{{ $model->id }}"
    wire:model="selectedRecords"
    value="{{ $model->id }}"
>