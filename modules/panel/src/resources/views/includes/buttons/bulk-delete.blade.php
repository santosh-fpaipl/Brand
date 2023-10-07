{{-- 
  $style : Class name
  $bulkDisabled : Control enable and disable of button
  $panel['function'] : Function name
--}}
<button type="button" class="btn btn-primary {{ $panel['style'] }} @if ($bulkDisabled) disabled @endif"
    wire:click="{{ $panel['function'] }}">Bulk Delete
</button>