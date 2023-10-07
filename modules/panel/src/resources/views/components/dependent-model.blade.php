{{-- @if($dependentRecordCount) --}}
    <div class="card">
        <div class="card-header">
            This record has  {{ $dependentRecordCount }} dependent records.
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                    <th scope="col">Model</th>
                    <th scope="col">Records</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($dependentRecords as $key => $value)
                        <tr>
                            <td>{{ ucwords($key)  }}</td>
                            <td>{{ $value }}</td>
                        </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
{{-- @endif --}}