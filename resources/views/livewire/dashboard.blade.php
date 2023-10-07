<div id="aaa">
    <a id="dddd" href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
        {{-- <svg class="bi pe-none me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg> --}}
        <span class="fs-4">Dashboard</span>
     </a>
     <hr>
      Dashboard
    <form wire:submit="save">
        <input type="text" id="title" wire:model.blur="title">
        {{$title}}
        <button type="submit">Save</button>
        <div id="sss" wire:loading> 
            Saving post...
        </div>
    </form>
     <hr>
</div>