{{-- 
   It show the success or error messages. 
--}}
<div class="position-fixed top-0 end-0 m-4" style="z-index:100">
    @if ($showAlert)
    <div class="alert py-1 alert-{{ $alertClass }}" role="alert">
        <div class="d-flex align-items-center justify-content-between">
            <span>{{ $alertText }}</span>
            <button class="btn" wire:click="closeToast">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    @endif
</div>