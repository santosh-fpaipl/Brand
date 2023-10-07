{{-- 
   $label : It ia used as label 
   $model :  It ia a model object
   $name : It is field name of table of database
--}}
{{-- @if(!empty($label) && !empty($model->$name))
    {{ $label }} :
@endif --}}
@if (!empty($model->$name))    
    <div class="text-{{ $align }}">
        <div>
            {{ date_format($model->$name, 'M d, Y') }}
        </div>
    </div>
@endif