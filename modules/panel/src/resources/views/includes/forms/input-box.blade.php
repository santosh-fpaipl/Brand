{{-- 
    $label : It ia used as label.
    $modelName : It is a model name used for generating unique id of input fields.
    $name : It is field name of table of database.
    $type : It is a type's value of input field like text, file etc.
    $style : It is class name.
    $attribute : It is a array of arttibutes values of input fields. 
    $placeholder : It is a placeholder value of input field.
    $message : It is a error message.
--}}
<div class="mb-3">

  @if (!empty($label))  
    <label for="input{{ $modelName }}{{ $name }}" class="form-label">{{ $label }}</label>
  @endif
  
  <input 
    name="{{ $name }}" 
    @if($show) disabled @endif
    id="input{{ $modelName }}{{ $name }}"
    type="{{ empty($type) ? 'text' : $type }}" 
    class="form-control {{ empty($style) ? '' : $style }}" 
    {{ empty($attribute) ? '' : implode(' ', $attribute)  }}
    value="{{ empty($model) ? old($name) : $model->$name }}" 
    placeholder="{{ empty($placeholder) ? '' : $placeholder }}" 
  >

  @error($name)
      <span class="input_val_error">{{ $message }}</span>
  @enderror

</div>