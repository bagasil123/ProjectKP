<!DOCTYPE html>
<html lang="en">
@include('frontend.layouts.partials.head')

<body>
    @include('frontend.layouts.partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('frontend.layouts.partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>