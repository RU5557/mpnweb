{{-- Wrapper untuk kompatibilitas --}}
<x-app-layout :title="$title ?? 'MPN-Info'">
    @yield('content')
    {{ $slot ?? '' }}
</x-app-layout>
