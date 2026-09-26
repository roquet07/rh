<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface text-ink">
        <flux:sidebar sticky collapsible class="w-[272px] border-e border-line bg-surface-raised px-4 py-5">
                <flux:sidebar.header class="lg:hidden">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                        <span aria-hidden="true" class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand text-on-brand text-sm font-bold tracking-tight">TR</span>
                        <span class="flex flex-col">
                            <span class="text-base font-semibold leading-tight text-ink">Talento RH</span>
                            <span class="text-xs leading-tight text-ink-muted">Recursos Humanos</span>
                        </span>
                    </a>
                    <flux:sidebar.collapse class="lg:hidden" />
                </flux:sidebar.header>

                <div class="hidden lg:flex items-center justify-between gap-2">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 px-2 min-w-0 in-data-flux-sidebar-collapsed-desktop:px-0">
                        <span aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand text-on-brand text-sm font-bold tracking-tight">TR</span>
                        <span class="flex flex-col min-w-0 in-data-flux-sidebar-collapsed-desktop:hidden">
                            <span class="text-base font-semibold leading-tight text-ink truncate">Talento RH</span>
                            <span class="text-xs leading-tight text-ink-muted truncate">Recursos Humanos</span>
                        </span>
                    </a>

                    <flux:sidebar.collapse class="in-data-flux-sidebar-collapsed-desktop:hidden" />
                </div>

                <nav aria-label="Principal" class="flex flex-col gap-0.5 mt-4">
                    <div class="text-xs font-medium uppercase tracking-wide text-ink-muted px-3 pt-4 pb-2 in-data-flux-sidebar-collapsed-desktop:hidden">{{ __('General') }}</div>

                    <flux:sidebar.item icon="home" :href="route('dashboard')" wire:navigate :current="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @canany(['departamentos.ver', 'puestos.ver', 'documento-tipos.ver', 'empleados.ver', 'solicitudes.ver', 'contratos.ver', 'comisiones.ver', 'nomina.ver', 'empresa.gestionar', 'usuarios.gestionar', 'roles.gestionar'])
                        <div class="text-xs font-medium uppercase tracking-wide text-ink-muted px-3 pt-4 pb-2 in-data-flux-sidebar-collapsed-desktop:hidden">{{ __('Recursos Humanos') }}</div>

                        @can('empleados.ver')
                            <flux:sidebar.item icon="users" :href="route('empleados.index')" wire:navigate :current="request()->routeIs('empleados.*')">
                                {{ __('Empleados') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('puestos.ver')
                            <flux:sidebar.item icon="briefcase" :href="route('puestos.index')" wire:navigate :current="request()->routeIs('puestos.*')">
                                {{ __('Puestos') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('documento-tipos.ver')
                            <flux:sidebar.item icon="document-duplicate" :href="route('documento-tipos.index')" wire:navigate :current="request()->routeIs('documento-tipos.*')">
                                {{ __('Tipos de documento') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('departamentos.ver')
                            <flux:sidebar.item icon="building-office" :href="route('departamentos.index')" wire:navigate :current="request()->routeIs('departamentos.*')">
                                {{ __('Departamentos') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('solicitudes.ver')
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('solicitudes.index')" wire:navigate :current="request()->routeIs('solicitudes.*')">
                                {{ __('Solicitudes de vacante') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('contratos.ver')
                            <flux:sidebar.item icon="document-text" :href="route('contratos.index')" wire:navigate :current="request()->routeIs('contratos.*')">
                                {{ __('Contratos') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('comisiones.ver')
                            <flux:sidebar.item icon="chart-bar" :href="route('comisiones.index')" wire:navigate :current="request()->routeIs('comisiones.*')">
                                {{ __('Comisiones') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('nomina.ver')
                            <flux:sidebar.item icon="banknotes" :href="route('nomina.periodos')" wire:navigate :current="request()->routeIs('nomina.*')">
                                {{ __('Nómina') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('empresa.gestionar')
                            <flux:sidebar.item icon="cog-6-tooth" :href="route('empresa.config')" wire:navigate :current="request()->routeIs('empresa.*')">
                                {{ __('Config. empresa') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('usuarios.gestionar')
                            <flux:sidebar.item icon="user-group" :href="route('admin.usuarios')" wire:navigate :current="request()->routeIs('admin.usuarios')">
                                {{ __('Usuarios') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('roles.gestionar')
                            <flux:sidebar.item icon="shield-check" :href="route('admin.roles')" wire:navigate :current="request()->routeIs('admin.roles')">
                                {{ __('Roles') }}
                            </flux:sidebar.item>
                        @endcan
                    @endcanany

                    @if (auth()->user()->empleado)
                        <div class="text-xs font-medium uppercase tracking-wide text-ink-muted px-3 pt-4 pb-2 in-data-flux-sidebar-collapsed-desktop:hidden">{{ __('Mi cuenta') }}</div>

                        <flux:sidebar.item icon="identification" :href="route('mi-expediente')" wire:navigate :current="request()->routeIs('mi-expediente')">
                            {{ __('Mi expediente') }}
                        </flux:sidebar.item>
                    @endif
                </nav>

                <flux:spacer />

                <div class="mt-auto flex items-center gap-3 p-3 border border-line rounded-lg in-data-flux-sidebar-collapsed-desktop:flex-col in-data-flux-sidebar-collapsed-desktop:gap-2 in-data-flux-sidebar-collapsed-desktop:p-2">
                    <x-rh.avatar :name="auth()->user()->name" :photo-url="auth()->user()->avatar_path ? route('usuarios.foto', auth()->user()) : null" />

                    <div class="flex flex-col flex-1 min-w-0 in-data-flux-sidebar-collapsed-desktop:hidden">
                        <span class="text-sm font-medium truncate">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-ink-muted truncate">{{ auth()->user()->roles->first()?->name ?? __('Sin rol') }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" aria-label="{{ __('Cerrar sesión') }}" title="{{ __('Cerrar sesión') }}" class="flex h-9 w-9 items-center justify-center rounded-md text-ink-muted hover:bg-surface-sunken hover:text-ink cursor-pointer">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"></path></svg>
                        </button>
                    </form>
                </div>
            </flux:sidebar>

            <flux:header class="h-16 shrink-0 flex items-center justify-between px-4 sm:px-8 bg-surface-raised border-b border-line">
                <div class="flex items-center gap-2 min-w-0">
                    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

                    <nav aria-label="Ruta de navegación" class="hidden sm:flex items-center gap-2 text-sm min-w-0 overflow-hidden">
                        @php($crumbs = \App\Support\Breadcrumbs::forRoute(request()->route()?->getName()))
                        @foreach ($crumbs as $i => $crumb)
                            @if ($i > 0)
                                <span aria-hidden="true" class="text-line-strong">/</span>
                            @endif
                            @if ($i === count($crumbs) - 1)
                                <span aria-current="page" class="text-ink font-medium truncate">{{ $crumb }}</span>
                            @else
                                <span class="text-ink-muted truncate">{{ $crumb }}</span>
                            @endif
                        @endforeach
                    </nav>
                </div>

                <div class="flex items-center gap-2">
                    <label class="relative hidden md:flex items-center w-[220px] lg:w-[280px]">
                        <span aria-hidden="true" class="absolute left-3 flex text-ink-muted">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path></svg>
                        </span>
                        <span class="sr-only">{{ __('Buscar en Talento RH') }}</span>
                        <input type="search" placeholder="{{ __('Buscar colaborador, contrato…') }}" class="h-10 w-full rounded-md border border-line-strong bg-surface pl-10 pr-3 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus">
                    </label>

                    <button type="button" aria-label="{{ __('Notificaciones, sin pendientes') }}" class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-surface-raised text-ink-muted hover:bg-surface-sunken hover:text-ink cursor-pointer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"></path></svg>
                    </button>

                    <x-rh.theme-toggle />
                </div>
            </flux:header>

            {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
