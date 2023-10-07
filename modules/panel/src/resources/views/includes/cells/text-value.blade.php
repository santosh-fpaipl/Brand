{{-- 
   $label : It ia used as label
   $function : It is a function name
   $model :  It ia a model object
   $name : It is field name of table of database
--}}
{{-- @if(!empty($label) && (!empty($value) || !empty($model->$name)))
    {{ $label }} : 
@endif --}}
<div class="text-{{ $align }}">
    <div>
        @if (isset($value))
            {!! $value($model, $name) !!}
        @else
            {{ $model->$name }}
        @endif
    </div>
</div>