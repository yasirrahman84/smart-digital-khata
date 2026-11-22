<x-error-page
    code="500"
    title="Something went wrong"
    message="The shop hit an unexpected problem while loading this page. Try again in a moment. Nothing was lost from your khata."
>
    <a class="primary" href="{{ url('/') }}">Go to dashboard</a>
</x-error-page>
