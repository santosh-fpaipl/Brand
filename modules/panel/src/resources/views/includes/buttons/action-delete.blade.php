{{-- 
   $queryName : It is the "page"(hard code) key for pagination 
   $queryValue : It is current page value in pagination
   $action : route name
   $data : It is a model
   $style : Class name
   $confirm : Control the bootstrape modal will show or not
   $title : It is a label
--}}

<form class="btn btn-outline-dark align-items-top" action="{{ route($action, [$data]) }}" method="post">
    @csrf
    @method('DELETE')

    <button 
        type="submit" 
        class="btn h-100 p-0 {{ $style }}">
        {{ $title }}
    </button>
</form>
