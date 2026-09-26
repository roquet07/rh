<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface text-ink">
        <div class="flex w-full min-h-screen">
            <div class="w-full lg:w-[640px] shrink-0 min-h-screen p-8 lg:px-12 lg:py-8 flex flex-col bg-surface">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3">
                        <span aria-hidden="true" class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand text-on-brand text-sm font-bold tracking-tight">TR</span>
                        <span class="flex flex-col">
                            <span class="text-base font-semibold leading-tight text-ink">Talento RH</span>
                            <span class="text-xs leading-tight text-ink-muted">Recursos Humanos</span>
                        </span>
                    </a>

                    <x-rh.theme-toggle />
                </div>

                <main class="flex-1 flex items-center justify-center">
                    <div class="w-full max-w-[400px] flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </main>

                <p class="flex justify-between text-xs text-ink-muted">
                    <span>© {{ now()->year }} Talento RH</span>
                    <span class="flex gap-4">
                        <a href="#" class="text-brand hover:text-brand-hover hover:underline">{{ __('Aviso de privacidad') }}</a>
                        <a href="#" class="text-brand hover:text-brand-hover hover:underline">{{ __('Ayuda') }}</a>
                    </span>
                </p>
            </div>

            <aside class="hidden lg:flex flex-1 min-w-0 flex-col justify-center gap-8 p-16 bg-brand-soft border-l border-line">
                <div class="flex flex-col gap-3 max-w-[520px]">
                    <span class="text-xs font-medium uppercase tracking-wide text-brand">{{ $eyebrow ?? __('Recursos Humanos') }}</span>
                    <p class="m-0 text-4xl font-bold tracking-tight leading-tight">{{ $headline ?? __('El expediente de tu gente, en un solo lugar.') }}</p>
                    <p class="m-0 text-base text-ink-muted">{{ $tagline ?? __('Empleados, contratos, comisiones y nómina conectados, con RFC, CURP y NSS en cada expediente.') }}</p>
                </div>

                <div aria-hidden="true" class="bg-surface-raised border border-line rounded-xl shadow-lg max-w-[520px] overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5">
                        <span class="text-base font-semibold">{{ __('Empleados') }}</span>
                        <span class="inline-flex items-center gap-2 h-8 px-3 rounded-md text-[13px] font-medium bg-brand text-on-brand">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                            {{ __('Nuevo empleado') }}
                        </span>
                    </div>

                    @foreach ([['ED', 'Empleado Demo', 'Ejecutivo de Ventas', 'activo'], ['DO', 'Daniela Ortiz Vega', 'Reclutadora', 'periodo_prueba'], ['LR', 'Lucía Ramírez Torres', 'Contadora', 'baja']] as [$ini, $nombre, $puesto, $estado])
                        <div class="flex items-center gap-3 px-4 py-3 border-t border-line">
                            <span aria-hidden="true" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-warm text-on-warm text-xs font-semibold">{{ $ini }}</span>
                            <div class="flex-1 flex flex-col">
                                <span class="text-sm font-medium">{{ $nombre }}</span>
                                <span class="text-xs text-ink-muted">{{ $puesto }}</span>
                            </div>
                            <x-rh.badge :estado="$estado" />
                        </div>
                    @endforeach
                </div>

                <ul class="list-none m-0 p-0 grid grid-cols-2 gap-4 max-w-[520px]">
                    <li class="flex items-center gap-3 text-sm font-medium">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-raised border border-line text-brand"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path></svg></span>
                        {{ __('Expedientes y puestos') }}
                    </li>
                    <li class="flex items-center gap-3 text-sm font-medium">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-raised border border-line text-brand"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M9 12.75h6M9 15.75h6"></path></svg></span>
                        {{ __('Contratos y vacantes') }}
                    </li>
                    <li class="flex items-center gap-3 text-sm font-medium">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-raised border border-line text-brand"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.25 6.75h19.5v10.5H2.25zM14.25 12a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM5.25 9.75v4.5M18.75 9.75v4.5"></path></svg></span>
                        {{ __('Comisiones y nómina') }}
                    </li>
                    <li class="flex items-center gap-3 text-sm font-medium">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-raised border border-line text-brand"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"></path></svg></span>
                        {{ __('Modo claro y oscuro') }}
                    </li>
                </ul>
            </aside>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
