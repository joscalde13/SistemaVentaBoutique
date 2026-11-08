<x-layouts.auth.simple :title="$title ?? (request()->routeIs('register') ? __('Crear cuenta') : __('Iniciar sesión'))">
    {{ $slot }}
</x-layouts.auth.simple>
