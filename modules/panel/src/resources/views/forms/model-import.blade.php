@extends('layouts.master')

@section('content')

    <div class="d-flex align-items-center">
        <a class="btn me-1" href="{{ route(Str::plural($modelName) . '.index') }}">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="fs-5 text-capitalize">{{ $message['import_page'] }}</span>
    </div>

    <hr>

    <form 
        class=""
        method="post"
        enctype="multipart/form-data" 
        action="{{ route('import-data.process', request()->route('model')) }}" 
    >
        @csrf

        <div class="mb-3">
            <label 
                for="input{{ $modelName }}import" 
                class="form-label">
                Data File (.xlsx)
            </label>
            <input 
                required
                type="file" 
                name="import_file" 
                class="form-control" 
                id="input{{ $modelName }}import" 
            >
            @if (isset($errors))
                @error('import_file')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            @endif
        </div>

        <div class="mb-3">
            <label 
                for="inputBatchImportDescription" 
                class="form-label">
                Batch Log Description
            </label>
            <input 
                required 
                type="text" 
                class="form-control" 
                name="description" 
                id="inputBatchImportDescription"
                placeholder="Enter description for batch import"
            >
        </div>

        <button type="submit" class="btn btn-dark px-4">Import Data</button>

    </form>

    @if (!empty($batchActivities))
        <div class="overflow-scroll" style="max-height:500px; max-width: 100%;">

            <div class="d-flex justify-content-between px-2">
                <p class="fw-bold fs-5 mb-2">Bulk Import Report </p>

                <caption>
                    Row Count
                    @php
                        if (is_array(json_decode($batchActivities->first()->properties, true))) {
                            echo json_decode($batchActivities->first()->properties, true)['total_rows'];
                        }
                    @endphp
                </caption>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr class="table-dark">
                        <th style="min-width: 50px">#</th>
                        <th style="width: 50px" class="text-center">Status</th>
                        <th style="min-width: 20vw">Message</th>
                        <th>Row Content</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($batchActivities as $activity)
                        @php
                            $properties = json_decode($activity->properties, true);
                        @endphp

                        @if (!empty($properties['status']))
                            {{-- Render Global Error --}}
                            <tr>
                                <td></td>
                                <td class="text-center">{{ $properties['status'] }}</td>
                                <td>{{ $properties['message'] }}</td>
                                <td></td>
                            </tr>
                        @else
                            {{-- Render each row error --}}
                            @foreach ($properties['rows'] as $property)
                                <tr>
                                    <td class="ps-3">{{ $property['record_no'] }}</td>
                                    <td class="text-center"><i
                                            class="bi {{ $property['status'] == 'success' ? 'bi-check-circle text-success' : 'bi-x-circle text-danger' }}"></i>
                                    </td>
                                    <td style="min-width: 20vw">{{ $property['message'] }}</td>
                                    <td>{{ json_encode(Arr::except($property, ['record_no', 'total_record', 'status', 'message'])['columns']) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection