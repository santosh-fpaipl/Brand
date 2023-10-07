{{-- 
  $label : It ia used as label
  $value :  It is a function name
--}}

{{-- @if(!empty($label))
    {{ $label }} :
@endif --}}
<div class="text-{{ $align }}">
  <div>
    {{ $value($currentPage, $pageLength, $iteration) }}
  </div>
</div>