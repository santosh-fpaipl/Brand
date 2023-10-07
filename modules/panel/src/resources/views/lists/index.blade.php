@extends('layouts.master')

@section('content')

    <div>
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
            <span class="fs-4">{{ $messages['list_page'] }}</span>
        </a>

        <hr>

        {{-- @livewire($modelName . '.' .$modelName.'-list',key(Str::plural($modelName, 2) . '-list-' . now())) --}}

        <livewire:datatables 
            :model="$model"
            :datatableClass="$datatable"
            :key="Str::plural($modelName, 2) . '-list-' . now()"
        />

    </div>
    
@endsection