<?php

use App\Models\Empleado;
use App\Models\Puesto;
use App\Support\EmpleadoRules;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Editar empleado')] class extends Component {
    public Empleado $empleado;

    public ?int $puesto_id = null;

    public ?int $jefe_directo_id = null;

    public string $nombre_completo = '';

    public string $apellido_paterno = '';

    public string $apellido_materno = '';

    public string $rfc = '';

    public string $curp = '';

    public string $nss = '';

    public string $regimen_fiscal = '';

    public string $fecha_nacimiento = '';

    public string $estado_civil = '';

    public string $domicilio = '';

    public string $colonia = '';

    public string $codigo_postal = '';

    public string $municipio = '';

    public string $estado_direccion = '';

    public string $telefono = '';

    public string $correo_personal = '';

    public string $contacto_emergencia_nombre = '';

    public string $contacto_emergencia_telefono = '';

    public string $fecha_ingreso = '';

    public string $salario_diario = '';

    public string $jornada = 'diurna';

    public string $sucursal = '';

    public string $banco = '';

    public string $clabe = '';

    public string $estatus = 'activo';

    public string $fecha_baja = '';

    public string $motivo_baja = '';

    public function mount(Empleado $empleado): void
    {
        $this->empleado = $empleado;
        $this->puesto_id = $empleado->puesto_id;
        $this->jefe_directo_id = $empleado->jefe_directo_id;
        $this->nombre_completo = $empleado->nombre_completo;
        $this->apellido_paterno = (string) $empleado->apellido_paterno;
        $this->apellido_materno = (string) $empleado->apellido_materno;
        $this->rfc = $empleado->rfc;
        $this->curp = $empleado->curp;
        $this->nss = (string) $empleado->nss;
        $this->regimen_fiscal = (string) $empleado->regimen_fiscal;
        $this->fecha_nacimiento = $empleado->fecha_nacimiento->toDateString();
        $this->estado_civil = (string) $empleado->estado_civil;
        $this->domicilio = (string) $empleado->domicilio;
        $this->colonia = (string) $empleado->colonia;
        $this->codigo_postal = (string) $empleado->codigo_postal;
        $this->municipio = (string) $empleado->municipio;
        $this->estado_direccion = (string) $empleado->estado_direccion;
        $this->telefono = (string) $empleado->telefono;
        $this->correo_personal = (string) $empleado->correo_personal;
        $this->contacto_emergencia_nombre = (string) $empleado->contacto_emergencia_nombre;
        $this->contacto_emergencia_telefono = (string) $empleado->contacto_emergencia_telefono;
        $this->fecha_ingreso = $empleado->fecha_ingreso->toDateString();
        $this->salario_diario = (string) $empleado->salario_diario;
        $this->jornada = $empleado->jornada;
        $this->sucursal = (string) $empleado->sucursal;
        $this->banco = (string) $empleado->banco;
        $this->clabe = (string) $empleado->clabe;
        $this->estatus = $empleado->estatus;
        $this->fecha_baja = $empleado->fecha_baja?->toDateString() ?? '';
        $this->motivo_baja = (string) $empleado->motivo_baja;
    }

    #[Computed]
    public function puestos()
    {
        return Puesto::query()->where('activo', true)->with('departamento')->orderBy('nombre')->get();
    }

    #[Computed]
    public function jefes()
    {
        return Empleado::query()->where('estatus', 'activo')->where('id', '!=', $this->empleado->id)->orderBy('nombre_completo')->get();
    }

    public function save(): void
    {
        $this->rfc = mb_strtoupper($this->rfc);
        $this->curp = mb_strtoupper($this->curp);

        $validated = $this->validate(EmpleadoRules::rules($this->empleado->id) + EmpleadoRules::reglasAdicionalesEdicion() + [
            'estatus' => ['required', Rule::in(['activo', 'baja'])],
            'fecha_baja' => ['nullable', 'required_if:estatus,baja', 'date'],
            'motivo_baja' => ['nullable', 'required_if:estatus,baja', 'string'],
        ]);

        if ($validated['estatus'] === 'activo') {
            $validated['fecha_baja'] = null;
            $validated['motivo_baja'] = null;
        }

        $this->empleado->update($validated);

        Flux::toast(variant: 'success', text: __('Empleado actualizado.'));

        $this->redirect(route('empleados.show', $this->empleado), navigate: true);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex flex-col gap-1">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Editar empleado') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ $empleado->numero_empleado }} — {{ $empleado->nombreCompleto() }}</p>
    </div>

    <form wire:submit="save" class="max-w-3xl flex flex-col gap-8">
        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Datos personales') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <x-rh.text-field wire:model="nombre_completo" name="nombre_completo" label="{{ __('Nombre(s)') }}" required :error="$errors->first('nombre_completo')" />
                <x-rh.text-field wire:model="apellido_paterno" name="apellido_paterno" label="{{ __('Apellido paterno') }}" required :error="$errors->first('apellido_paterno')" />
                <x-rh.text-field wire:model="apellido_materno" name="apellido_materno" label="{{ __('Apellido materno') }}" />
                <x-rh.text-field wire:model="fecha_nacimiento" name="fecha_nacimiento" type="date" label="{{ __('Fecha de nacimiento') }}" required :error="$errors->first('fecha_nacimiento')" />

                <x-rh.select wire:model="estado_civil" name="estado_civil" label="{{ __('Estado civil') }}">
                    <option value="Soltero(a)">{{ __('Soltero(a)') }}</option>
                    <option value="Casado(a)">{{ __('Casado(a)') }}</option>
                    <option value="Unión libre">{{ __('Unión libre') }}</option>
                    <option value="Divorciado(a)">{{ __('Divorciado(a)') }}</option>
                    <option value="Viudo(a)">{{ __('Viudo(a)') }}</option>
                </x-rh.select>

                <x-rh.text-field wire:model="telefono" name="telefono" type="tel" label="{{ __('Teléfono') }}" :error="$errors->first('telefono')" />

                <div class="col-span-2">
                    <x-rh.text-field wire:model="correo_personal" name="correo_personal" type="email" label="{{ __('Correo personal') }}" :error="$errors->first('correo_personal')" />
                </div>

                <x-rh.text-field wire:model="contacto_emergencia_nombre" name="contacto_emergencia_nombre" label="{{ __('Contacto de emergencia') }}" />
                <x-rh.text-field wire:model="contacto_emergencia_telefono" name="contacto_emergencia_telefono" label="{{ __('Teléfono de emergencia') }}" :error="$errors->first('contacto_emergencia_telefono')" />
            </div>
        </x-rh.card>

        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Identificación') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <x-rh.text-field wire:model="rfc" name="rfc" label="{{ __('RFC') }}" required mono maxlength="13" :error="$errors->first('rfc')" />
                <x-rh.text-field wire:model="curp" name="curp" label="{{ __('CURP') }}" required mono maxlength="18" :error="$errors->first('curp')" />
                <x-rh.text-field wire:model="nss" name="nss" label="{{ __('NSS') }}" mono maxlength="11" :error="$errors->first('nss')" />

                <x-rh.select wire:model="regimen_fiscal" name="regimen_fiscal" label="{{ __('Régimen fiscal') }}">
                    <option value="Sueldos y salarios">{{ __('Sueldos y salarios') }}</option>
                    <option value="Asimilados a salarios">{{ __('Asimilados a salarios') }}</option>
                </x-rh.select>
            </div>
        </x-rh.card>

        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Domicilio') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <div class="col-span-2">
                    <x-rh.text-field wire:model="domicilio" name="domicilio" label="{{ __('Calle y número') }}" required :error="$errors->first('domicilio')" />
                </div>

                <x-rh.text-field wire:model="colonia" name="colonia" label="{{ __('Colonia') }}" required :error="$errors->first('colonia')" />
                <x-rh.text-field wire:model="codigo_postal" name="codigo_postal" label="{{ __('Código postal') }}" required maxlength="5" :error="$errors->first('codigo_postal')" />
                <x-rh.text-field wire:model="municipio" name="municipio" label="{{ __('Municipio o alcaldía') }}" />

                <x-rh.select wire:model="estado_direccion" name="estado_direccion" label="{{ __('Estado') }}" required :error="$errors->first('estado_direccion')">
                    @foreach (['Ciudad de México', 'Estado de México', 'Jalisco', 'Nuevo León', 'Puebla', 'Querétaro', 'Otro'] as $estadoOpcion)
                        <option value="{{ $estadoOpcion }}">{{ $estadoOpcion }}</option>
                    @endforeach
                </x-rh.select>
            </div>
        </x-rh.card>

        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Datos laborales') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <x-rh.select wire:model="puesto_id" name="puesto_id" label="{{ __('Puesto') }}" required :error="$errors->first('puesto_id')">
                    @foreach ($this->puestos as $puesto)
                        <option value="{{ $puesto->id }}">{{ $puesto->nombre }} — {{ $puesto->departamento->nombre }}</option>
                    @endforeach
                </x-rh.select>

                <x-rh.text-field wire:model="fecha_ingreso" name="fecha_ingreso" type="date" label="{{ __('Fecha de ingreso') }}" required :error="$errors->first('fecha_ingreso')" />
                <x-rh.text-field wire:model="salario_diario" name="salario_diario" type="number" step="0.01" label="{{ __('Salario diario') }}" required prefix="$" suffix="MXN" :error="$errors->first('salario_diario')" />

                <x-rh.select wire:model="jornada" name="jornada" label="{{ __('Jornada') }}" required>
                    <option value="diurna">{{ __('Diurna') }}</option>
                    <option value="nocturna">{{ __('Nocturna') }}</option>
                    <option value="mixta">{{ __('Mixta') }}</option>
                </x-rh.select>

                <x-rh.select wire:model="jefe_directo_id" name="jefe_directo_id" label="{{ __('Jefe directo') }}">
                    @foreach ($this->jefes as $jefe)
                        <option value="{{ $jefe->id }}">{{ $jefe->nombreCompleto() }}</option>
                    @endforeach
                </x-rh.select>

                <x-rh.text-field wire:model="sucursal" name="sucursal" label="{{ __('Sucursal') }}" />
            </div>
        </x-rh.card>

        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Datos bancarios') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <x-rh.text-field wire:model="banco" name="banco" label="{{ __('Banco') }}" />
                <x-rh.text-field wire:model="clabe" name="clabe" label="{{ __('CLABE') }}" mono maxlength="18" :error="$errors->first('clabe')" />
            </div>
        </x-rh.card>

        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Estatus laboral') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <x-rh.select wire:model.live="estatus" name="estatus" label="{{ __('Estatus') }}" required>
                    <option value="activo">{{ __('Activo') }}</option>
                    <option value="baja">{{ __('Baja') }}</option>
                </x-rh.select>

                @if ($estatus === 'baja')
                    <x-rh.text-field wire:model="fecha_baja" name="fecha_baja" type="date" label="{{ __('Fecha de baja') }}" required :error="$errors->first('fecha_baja')" />

                    <div class="col-span-2 flex flex-col gap-1.5">
                        <label for="motivo_baja" class="text-sm font-medium text-ink">{{ __('Motivo de baja') }} <span aria-hidden="true" class="text-danger">*</span></label>
                        <textarea wire:model="motivo_baja" id="motivo_baja" name="motivo_baja" required class="w-full rounded-md border border-line-strong bg-surface-raised px-3 py-2 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus"></textarea>
                        @error('motivo_baja')
                            <p class="text-xs font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
        </x-rh.card>

        <div class="flex justify-end gap-2">
            <x-rh.button variant="secondary" href="{{ route('empleados.show', $empleado) }}">{{ __('Cancelar') }}</x-rh.button>
            <x-rh.button type="submit" variant="primary">{{ __('Guardar') }}</x-rh.button>
        </div>
    </form>
</section>
