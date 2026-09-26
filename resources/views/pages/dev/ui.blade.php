<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-surface text-ink p-8 flex flex-col gap-10">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-semibold tracking-tight">Componentes Talento RH</h1>
        <x-rh.theme-toggle />
    </div>

    <section class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold">Botones</h2>
        <div class="flex gap-3">
            <x-rh.button variant="primary">Primario</x-rh.button>
            <x-rh.button variant="secondary">Secundario</x-rh.button>
            <x-rh.button variant="ghost">Ghost</x-rh.button>
            <x-rh.button variant="danger">Danger</x-rh.button>
            <x-rh.button variant="primary" disabled>Deshabilitado</x-rh.button>
        </div>
    </section>

    <section class="flex flex-col gap-4 max-w-lg">
        <h2 class="text-2xl font-semibold">Campos</h2>
        <x-rh.text-field label="Nombre completo" name="nombre" required placeholder="Ej. Ana López" />
        <x-rh.text-field label="RFC" name="rfc" mono required hint="13 caracteres con homoclave." error="Ingresa un RFC válido." />
        <x-rh.text-field label="Salario diario" name="salario" type="number" prefix="$" suffix="MXN" />
        <x-rh.select label="Departamento" name="departamento" required>
            <option value="ventas">Ventas</option>
            <option value="finanzas">Finanzas</option>
        </x-rh.select>
        <x-rh.password-field label="Contraseña" name="password" strength required />
    </section>

    <section class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold">Badges</h2>
        <div class="flex gap-2">
            <x-rh.badge estado="activo" />
            <x-rh.badge estado="periodo_prueba" />
            <x-rh.badge estado="baja" />
            <x-rh.badge estado="pendiente" />
            <x-rh.badge estado="aprobada" />
            <x-rh.badge estado="rechazada" />
        </div>
    </section>

    <section class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold">Avatares</h2>
        <div class="flex gap-2">
            <x-rh.avatar name="Ana López Mireles" size="sm" />
            <x-rh.avatar name="Ana López Mireles" size="md" />
            <x-rh.avatar name="Ana López Mireles" size="lg" />
        </div>
    </section>

    <section class="grid grid-cols-4 gap-6">
        <x-rh.stat-tile label="Colaboradores activos" value="48" meta="+3 altas este mes" />
        <x-rh.stat-tile label="Vacantes abiertas" value="5" meta="2 por aprobar" />
    </section>

    <section class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold">Empty state</h2>
        <x-rh.card :padded="false">
            <x-rh.empty-state
                title="Sin resultados"
                description="Ningún colaborador coincide con la búsqueda o los filtros."
                icon='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path></svg>'
            >
                <x-rh.button variant="ghost">Limpiar filtros</x-rh.button>
            </x-rh.empty-state>
        </x-rh.card>
    </section>

    @fluxScripts
</body>
</html>
