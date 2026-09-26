<?php

use App\Actions\Empleados\ExportarEmpleadosCsv;
use App\Actions\Empleados\MostrarFotoEmpleado;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('departamentos', 'pages::departamentos.index')
        ->middleware('permission:departamentos.ver')
        ->name('departamentos.index');

    Route::livewire('puestos', 'pages::puestos.index')
        ->middleware('permission:puestos.ver')
        ->name('puestos.index');

    Route::livewire('documento-tipos', 'pages::documento-tipos.index')
        ->middleware('permission:documento-tipos.ver')
        ->name('documento-tipos.index');

    Route::livewire('empresa/config', 'pages::empresa.config')
        ->middleware('permission:empresa.gestionar')
        ->name('empresa.config');

    Route::livewire('empleados', 'pages::empleados.index')
        ->middleware('permission:empleados.ver')
        ->name('empleados.index');

    Route::livewire('empleados/crear', 'pages::empleados.create')
        ->middleware('permission:empleados.gestionar')
        ->name('empleados.create');

    Route::get('empleados/exportar', function (Request $request, ExportarEmpleadosCsv $action) {
        return $action($request);
    })->middleware('permission:empleados.ver')->name('empleados.exportar');

    Route::livewire('empleados/{empleado}/editar', 'pages::empleados.edit')
        ->middleware('permission:empleados.gestionar')
        ->name('empleados.edit');

    Route::livewire('empleados/{empleado}', 'pages::empleados.show')
        ->middleware('permission:empleados.ver')
        ->name('empleados.show');

    Route::get('empleados/{empleado}/foto', function (Empleado $empleado, MostrarFotoEmpleado $action) {
        return $action($empleado);
    })->middleware('permission:empleados.ver')->name('empleados.foto');

    Route::livewire('mi-expediente', 'pages::empleados.mi-expediente')
        ->name('mi-expediente');

    Route::livewire('solicitudes', 'pages::solicitudes.index')
        ->middleware('permission:solicitudes.ver')
        ->name('solicitudes.index');

    Route::livewire('solicitudes/crear', 'pages::solicitudes.create')
        ->middleware('permission:solicitudes.crear')
        ->name('solicitudes.create');

    Route::livewire('solicitudes/{solicitud}', 'pages::solicitudes.show')
        ->middleware('permission:solicitudes.ver')
        ->name('solicitudes.show');

    Route::livewire('contratos', 'pages::contratos.index')
        ->middleware('permission:contratos.ver')
        ->name('contratos.index');

    Route::livewire('contratos/crear/{empleado}', 'pages::contratos.create')
        ->middleware('permission:contratos.gestionar')
        ->name('contratos.create');

    Route::livewire('contratos/{contrato}/editar', 'pages::contratos.create')
        ->middleware('permission:contratos.gestionar')
        ->name('contratos.edit');

    Route::livewire('contratos/{contrato}', 'pages::contratos.show')
        ->middleware('permission:contratos.ver')
        ->name('contratos.show');

    Route::livewire('comisiones', 'pages::comisiones.index')
        ->middleware('permission:comisiones.ver')
        ->name('comisiones.index');

    Route::livewire('nomina/periodos', 'pages::nomina.periodos')
        ->middleware('permission:nomina.ver')
        ->name('nomina.periodos');

    Route::livewire('nomina/periodos/{periodo}', 'pages::nomina.periodo-detalle')
        ->middleware('permission:nomina.ver')
        ->name('nomina.periodo-detalle');

    Route::livewire('config-fiscal/isr', 'pages::config-fiscal.isr')
        ->middleware('permission:config-fiscal.gestionar')
        ->name('config-fiscal.isr');

    Route::livewire('config-fiscal/imss', 'pages::config-fiscal.imss')
        ->middleware('permission:config-fiscal.gestionar')
        ->name('config-fiscal.imss');

    Route::livewire('config-fiscal/vacaciones', 'pages::config-fiscal.vacaciones')
        ->middleware('permission:config-fiscal.gestionar')
        ->name('config-fiscal.vacaciones');

    Route::livewire('admin/usuarios', 'pages::admin.usuarios')
        ->middleware('permission:usuarios.gestionar')
        ->name('admin.usuarios');

    Route::livewire('admin/roles', 'pages::admin.roles')
        ->middleware('permission:roles.gestionar')
        ->name('admin.roles');
});
