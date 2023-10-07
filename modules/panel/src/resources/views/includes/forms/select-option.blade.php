{{-- 
   $label : It ia used as label.
   $modelName: It is a model name used for generating unique id of input fields.
   $name : It is field name of table of database.
   $style : It is class name.
   $attribute : It is a array of arttibutes values of input fields. 
   $placeholder : It is a placeholder value of input field.
   $records : It holds the all root level records.
   $separator : It is a separator that separate the record from it's child record
   $model : It is model object.
   hasChild() : It check that a record have child records. 
   $message : It is a error message.
--}}
<div class="mb-3">
  @if (!empty($label))  
    <label for="select{{ $modelName }}{{ $name }}" class="form-label">{{ $label }}</label>
  @endif
  <select id="select{{ $modelName }}{{ $name }}" 
    class="form-select {{ empty($style) ? '' : $style }}" 
    aria-label=".form-select example" 
    name="{{ $name }}"
    {{ empty($attribute) ? '' : implode(' ', $attribute)  }}
    @if($show) disabled @endif

  >
      <option selected value="">{{ $placeholder }}</option>
      @foreach ($options['data'] as $record)
          <?php $separator = ''; ?>
          <option value="{{ $record->id }}" @if (!empty($model) && $record->id == $model->$name) selected @endif>{{ $record->name }}</option>
          
          @if ($options['withRelation'] && method_exists($record, $options['relation']) && $record->{$options['relation']})
              @include('panel::includes.forms.child-option',[
                'subrecords' => $record->{$options['relation']}, 
                'separator' => $separator, 
                'model' =>empty($model) ? '' : $model,
              ])
          @endif

      @endforeach
  </select>

  @error($name)
      <span>{{ $message }}</span>
  @enderror

</div>