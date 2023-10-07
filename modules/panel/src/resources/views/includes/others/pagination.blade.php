<div class="d-flex justify-content-between">

    {{-- Per page count --}}
    <div class="mb-3 d-flex align-items-center">
        <small>{{ $features['pagination']['label']['pre'] }}</small>
        <select id="select{{ $modelName }}RecordPerPage" class="form-select form-select-sm mx-2 border-0"
            wire:model="pageLength" style="width: fit-content">
            @foreach ($features['pagination']['attributes']['options'] as $option)
                <option value="{{ $option['value'] }}">{{ $option['name'] }}</option>
            @endforeach
            {{-- @if ($pageLength <= $features['pagination']['attributes']['max'])
                <option value="all">All records</option>
            @else
                <option selected value="{{ $pageLength }}">All records</option>
            @endif --}}
        </select>
        <small>{{ $features['pagination']['label']['post'] }}</small>
    </div>

    {{-- links --}}
    @if ($pageLength <= $features['pagination']['attributes']['max'])
        {{ $data->links() }}
    @endif

</div>