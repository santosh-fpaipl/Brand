{{-- 
    $label : It ia used as label
    $model : It ia a model object
    Note:- Here we get a multiple images (secondary images)
--}}
{{-- @if (!empty($label) && count($model->getImage('s400', 'secondary', true)))
    {{ $label }} :
@endif --}}
<div class="d-flex">
    @foreach ($model->getImage('s400', 'secondary', true) as $mediaItemId => $mediaItemUrl)
        <div class="text-{{ $align }}">
            <div>
                <img src="{{ $mediaItemUrl }}" class="img-thumbnail" width="50" height="50" />
            </div>
        </div>
    @endforeach
</div>