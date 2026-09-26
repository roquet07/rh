<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private const PERMISSIONS = [
        'empresa.gestionar',
        'usuarios.gestionar',
        'roles.gestionar',
        'config-fiscal.gestionar',
        'departamentos.ver',
        'departamentos.gestionar',
        'puestos.ver',
        'puestos.gestionar',
        'documento-tipos.ver',
        'documento-tipos.gestionar',
        'empleados.ver',
        'empleados.gestionar',
        'empleados-documentos.capturar',
        'empleados-documentos.aprobar',
        'solicitudes.ver',
        'solicitudes.crear',
        'solicitudes.aprobar',
        'contratos.ver',
        'contratos.gestionar',
        'comisiones.ver',
        'comisiones.gestionar',
        'nomina.ver',
        'nomina.gestionar',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $administrador = Role::findOrCreate('Administrador');
        $administrador->syncPermissions(self::PERMISSIONS);

        $rh = Role::findOrCreate('RH');
        $rh->syncPermissions([
            'departamentos.ver', 'departamentos.gestionar',
            'puestos.ver', 'puestos.gestionar',
            'documento-tipos.ver', 'documento-tipos.gestionar',
            'empleados.ver', 'empleados.gestionar',
            'empleados-documentos.capturar',
            'solicitudes.ver', 'solicitudes.crear', 'solicitudes.aprobar',
            'contratos.ver', 'contratos.gestionar',
            'comisiones.ver', 'comisiones.gestionar',
            'nomina.ver', 'nomina.gestionar',
        ]);

        $gerente = Role::findOrCreate('Gerente');
        $gerente->syncPermissions([
            'departamentos.ver',
            'puestos.ver',
            'documento-tipos.ver',
            'empleados.ver',
            'solicitudes.ver', 'solicitudes.crear',
        ]);

        Role::findOrCreate('Empleado');

        $admin = User::where('email', 'test@example.com')->first();

        if ($admin !== null) {
            $admin->assignRole($administrador);
        }
    }
}
