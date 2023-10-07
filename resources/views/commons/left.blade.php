<div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark " style="width: 280px; min-height:100vh;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Stock Managment</span>
    </a>
    <hr>


    <ul class="nav nav-pills flex-column mb-auto">



        <li>
            <a href="{{ route('dashboard') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('dashboard') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Dashboard
            </a>
        </li>
        
        <li>
            <a href="{{ route('fabriccategories.index') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('fabriccategory') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Fabric Categories
            </a>
        </li>

        <li>
            <a href="{{ route('fabrics.index') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('fabric') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Fabrics
            </a>
        </li>

        <li>
            <a href="{{ route('customers.index') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('customer') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Customers
            </a>
        </li>

        <li>
            <a href="{{ route('suppliers.index') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('supplier') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Suppliers
            </a>
        </li>

        <li>
            <a href="{{ route('purchases.index') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('purchase') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Purchases
            </a>
        </li>

        <li>
            <a href="{{ route('sales.index') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('sale') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Sales
            </a>
        </li>

        <li>
            <a href="{{ route('stocks.index') }}"
                class="nav-link d-flex align-items-center text-white {{ checkCurrentRoute('stock') }}">
                <i class="bi bi-chevron-right me-2"></i>
                Stocks
            </a>
        </li>

        


        

        {{-- <li>
            <a href="#" class="nav-link d-flex align-items-center text-white">
                <i class="bi bi-chevron-right me-2"></i>
                Orders
            </a>
        </li>
        <li>
            <a href="#" class="nav-link d-flex align-items-center text-white">
                <i class="bi bi-chevron-right me-2"></i>
                <span class="">Products</span>
            </a>
        </li>
        <li>
            <a href="#" class="nav-link d-flex align-items-center text-white">
                <i class="bi bi-chevron-right me-2"></i>
                Customers
            </a>
        </li> --}}
    </ul>

</div>