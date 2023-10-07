<div>
    <div class="d-flex align-items-center">
                
        {{-- Back --}}
        <a href="{{ route(Str::plural($modelName) . '.index', ['page'=>request()->query('page'),'from' =>'crud' ]) }}">
            <button type="button" class="btn"><i class="bi bi-arrow-left"></i></button>
        </a>
        
        {{-- Form Title --}}
        <p class="fs-5 fw-bold mb-0">{{ $messages[ $formType . '_page'] }}</p>

    </div>
    <hr>

    <form wire:submit.prevent="delete">
               
        <div class="mb-3">

            <x-panel-dependent-model :model="$model" />
        
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Delete</button>
        </div>

    </form>
</div>

