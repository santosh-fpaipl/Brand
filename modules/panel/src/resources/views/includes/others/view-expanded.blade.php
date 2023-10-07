<tr id="collapse{{ $model->id }}" class="accordion-collapse collapse "
    data-bs-parent="#expandCollapse">
    <td colspan="{{ $visiblefields }}">
        <div class="d-flex flex-column">
            @foreach ($fields as $field)
                @if ($field['expandable'][$activePage])
                    <div class="d-flex">
                        <div class="text-capitalize px-2 pb-2 border-bottom" 
                            style="min-width: 200px; background-color: #ececec">
                            {{ $field['labels']['table'] }}
                        </div>
                        <div class="px-2 pb-2 border-bottom w-100">
                            @include('panel::includes.' .$field['tbody']['view'], [
                                'currentPage' => $currentPage, 
                                'pageLength' => $pageLength, 
                                'iteration'=> $loop->parent->iteration,
                                'model' => $model, 
                                'name' => $field['name'],
                                'value' => $field['tbody']['value'] ?: null,
                                'align' => $field['tbody']['align']?:'start',
                            ])
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </td>
</tr>