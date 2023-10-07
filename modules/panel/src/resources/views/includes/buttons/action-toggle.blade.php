{{-- 
    $action : Function name
    $bulkDisabled : Control enable and disable of button
    $style : Class name
    $title : It is a label
--}}



<button 
    type="button" 
    class="btn btn-outline-dark {{ $style }}"
    data-bs-toggle="collapse" data-bs-target="#collapse{{ $data->id  }}" aria-expanded="false" aria-controls="collapse{{ $data->id  }}"
>
    {{ $title }}
</button>