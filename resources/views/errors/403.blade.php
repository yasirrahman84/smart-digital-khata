<x-error-page
    code="403"
    title="You cannot open this page"
    message="Your account does not have access to this record. If you think this is a mistake, sign in with the right account."
>
    <a class="primary" href="{{ url('/') }}">Go to dashboard</a>
    <a class="secondary" href="{{ route('login') }}">Switch account</a>
</x-error-page>
