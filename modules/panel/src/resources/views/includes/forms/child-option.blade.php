{{-- 
   $separator :  It is a separator that separate the record from it's child record
   $subrecords : It holds the all child records of a record.
   $model : It is model object.
   hasChild() : It check that a record have child records. 
 --}}
<div>
    @php $separator .= '-- '; @endphp
    @foreach ($subrecords as $subrecord)
        <option value="{{ $subrecord->id }}" @if (!empty($model) && $subrecord->id == $model->$name) selected @endif>{{ $separator }}{{ $subrecord->name }}</option>
        @if ($options['withRelation'] && method_exists($subrecord, $options['relation']) && $subrecord->{$options['relation']})

            @include('panel::includes.forms.child-option',[
                'subrecords' => $subrecord->{$options['relation']}, 
                'separator' => $separator, 
                'model' =>empty($model) ? '' : $model,
            ])

        @endif
    @endforeach
</div>