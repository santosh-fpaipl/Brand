{{-- 
    $label : It ia used as label
    $model : It ia a model object
    Note:- Here we get a single image (primary image)
--}}
{{-- @if(!empty($label) && $model->getImage('s100') != '[]')
    {{ $label }} :
@endif --}}


@if ($model->getImage('s100') != '[]' && $model->getImage('s100') != null)
    <div class="text-{{ $align }}">
        <div>
            <img src="{{ $model->getImage('s100') }}" class="img-thumbnail" width="50" height="50" />
        </div>
    </div>
@else
    <div class="text-{{ $align }}">
        <div>
            <img src="{{ asset('storage/assests/placeholder.png') }}" class="img-thumbnail" width="30" height="30" />

        </div>
    </div>
@endif