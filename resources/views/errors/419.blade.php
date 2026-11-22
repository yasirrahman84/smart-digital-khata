<x-error-page
    code="419"
    title="Your session expired"
    message="For your security the page timed out. Sign in again and repeat what you were doing."
>
    <a class="primary" href="{{ route('login') }}">Sign in again</a>
</x-error-page>
