{{-- 
 It is used as a confirm modal.  
--}}
<div wire:ignore.self class="modal fade" id="confirmModal" tabindex="-1" 
aria-labelledby="confirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body d-flex flex-column">
                <div class="d-flex justify-content-center p-4">
                    <i class="bi bi-exclamation-triangle text-warning display-1"></i>
                </div>
                <div class="fw-bold mb-3 text-center">Do you want to proceed this request?</div>
                <div class="btn-group">
                    <button type="button" class="flex-fill btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    {{-- <a 
                        class="flex-fill btn btn-danger" 
                        href="{{ App\Helpers\GlobalSession::confirmDialog() }}"
                    > --}}
                        {{-- Confirm Action
                    </a> --}}
                    <button type="button" class="btn btn-danger" wire:click="confirmDialog()">Confirm Action</button>
                </div>
            </div>
        </div>
    </div>
</div>