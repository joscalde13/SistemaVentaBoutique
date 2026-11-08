<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    @if (request()->routeIs('login', 'register'))
        <body class="min-h-screen bg-stone-50 antialiased dark:bg-zinc-950">
            <main class="flex min-h-screen flex-col items-center justify-center px-5 py-10">
                <section class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white p-7 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">
                    <a href="{{ route('home') }}" class="mb-7 flex justify-center" wire:navigate aria-label="{{ __('Inicio') }}">
                        <span class="flex size-10 items-center justify-center overflow-hidden rounded-lg">
                        <img src="{{ asset('img/logo.jpg') }}" alt="" class="size-full object-cover">
                        </span>
                    </a>

                    {{ $slot }}
                </section>
            </main>
            @fluxScripts
        </body>
    @else
        <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
            <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
                <div class="flex w-full max-w-sm flex-col gap-2">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                        <span>
                            <img src="{{ asset('img/logo.jpg') }}" alt="Logo" class="h-12">
                        </span>
                        <span class="sr-only">{{ config('app.name', 'Sistema Venta Boutique') }}</span>
                    </a>
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            @fluxScripts
        </body>
    @endif
</html>
