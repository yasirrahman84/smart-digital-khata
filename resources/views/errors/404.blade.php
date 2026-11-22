<x-error-page
    code="404"
    title="Page not found"
    message="The page you are looking for does not exist, or it may have been moved. Check the address and try again."
>
    <a class="primary" href="{{ url('/') }}">Go to dashboard</a>
</x-error-page>
