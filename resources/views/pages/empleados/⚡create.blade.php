<?php

use App\Actions\Empleados\CrearEmpleado;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\SolicitudVacante;
use App\Support\EmpleadoRules;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Nuevo empleado')] class extends Component {
    #[Url]
    public ?int $solicitud = null;

    // Datos personales
    public string $nombre_completo = '';

    public string $apellido_paterno = '';

    public string $apellido_materno = '';

    public string $fecha_nacimiento = '';

    public string $estado_civil = '';

    public string $telefono = '';

    public string $correo_personal = '';

    // Identificación
    public string $rfc = '';

    public string $curp = '';

    public string $nss = '';

    public string $regimen_fiscal = '';

    // Domicilio
    public string $domicilio = '';

    public string $colonia = '';

    public string $codigo_postal = '';

    public string $municipio = '';

    public string $estado_direccion = '';

    // Datos laborales
    public ?int $puesto_id = null;

    public string $fecha_ingreso = '';

    public string $tipo_contrato = '';

    public string $salario_diario = '';

    public string $jornada = 'diurna';

    public ?int $jefe_directo_id = null;

    public function mount(): void
    {
        if ($this->solicitud === null) {
            return;
        }

        $solicitudVacante = SolicitudVacante::query()->find($this->solicitud);

        if ($solicitudVacante !== null) {
            $this->puesto_id = $solicitudVacante->puesto_id;
            $this->salario_diario = (string) $solicitudVacante->salario_propuesto;
        }
    }

    #[Computed]
    public function puestos()
    {
        return Puesto::query()->where('activo', true)->with('departamento')->orderBy('nombre')->get();
    }

    #[Computed]
    public function jefes()
    {
        return Empleado::query()->where('estatus', 'activo')->orderBy('nombre_completo')->get();
    }

    public function save(CrearEmpleado $crearEmpleado): void
    {
        $this->rfc = mb_strtoupper($this->rfc);
        $this->curp = mb_strtoupper($this->curp);

        $validated = $this->validate(EmpleadoRules::rules());

        $tipoContrato = $this->validate([
            'tipo_contrato' => ['required', Rule::in(['indeterminado', 'determinado', 'periodo_prueba', 'capacitacion_inicial'])],
        ])['tipo_contrato'];

        if ($this->solicitud !== null) {
            $validated['solicitud_vacante_id'] = $this->solicitud;
        }

        $empleado = $crearEmpleado($validated);

        Contrato::query()->create([
            'empleado_id' => $empleado->id,
            'solicitud_vacante_id' => $this->solicitud,
            'puesto_id' => $empleado->puesto_id,
            'tipo' => $tipoContrato,
            'fecha_inicio' => $validated['fecha_ingreso'],
            'salario_diario' => $validated['salario_diario'],
            'jornada' => $validated['jornada'],
            'lugar_trabajo' => __('Por definir'),
            'estatus' => 'borrador',
        ]);

        $this->dispatch('empleado-creado');

        $this->redirect(route('empleados.show', $empleado), navigate: true);
    }
}; ?>

<section
    class="w-full flex flex-col gap-8"
    x-data="{
        step: 0,
        goTo(i) { this.step = i; },
        siguiente() {
            const el = this.$refs['paso' + this.step];
            const requeridos = el.querySelectorAll('[required]');
            for (const campo of requeridos) {
                if (!campo.value || !campo.value.trim()) {
                    campo.reportValidity();
                    campo.focus();
                    return;
                }
            }
            if (this.step < 4) this.step++;
        },
        anterior() { if (this.step > 0) this.step--; },
        guardando: false,
        guardarBorrador() {
            const datos = {};
            this.$el.querySelectorAll('[name]').forEach((campo) => { datos[campo.name] = campo.value; });
            localStorage.setItem('empleado-draft', JSON.stringify(datos));
            this.guardando = true;
            setTimeout(() => { this.guardando = false; }, 2000);
        },
        restaurarBorrador() {
            const crudo = localStorage.getItem('empleado-draft');
            if (!crudo) return;
            try {
                const datos = JSON.parse(crudo);
                Object.entries(datos).forEach(([nombre, valor]) => {
                    if (valor) this.$wire.set(nombre, valor, false);
                });
            } catch (e) {}
        },
        init() {
            this.restaurarBorrador();
            this.$watch('step', () => {});
        },
    }"
    x-on:empleado-creado.window="localStorage.removeItem('empleado-draft')"
>
    <div class="flex items-end justify-between gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Nuevo empleado') }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ __('Datos personales y laborales del expediente.') }}</p>
        </div>

        <x-rh.button variant="secondary" href="{{ route('empleados.index') }}">{{ __('Cancelar') }}</x-rh.button>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        <x-rh.stepper
            class="w-full lg:w-[300px] lg:shrink-0"
            :steps="[
                ['title' => __('Datos personales'), 'desc' => __('Nombre, nacimiento y contacto')],
                ['title' => __('Identificación'), 'desc' => __('RFC, CURP y NSS')],
                ['title' => __('Domicilio'), 'desc' => __('Dirección particular')],
                ['title' => __('Datos laborales'), 'desc' => __('Puesto, contrato y salario')],
                ['title' => __('Revisión'), 'desc' => __('Confirma y crea el expediente')],
            ]"
        />

        <form wire:submit="save" class="flex-1 min-w-0 bg-surface-raised border border-line rounded-xl shadow-sm flex flex-col">
            <div class="px-6 pt-6 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wide text-brand" x-text="'{{ __('Paso') }} ' + (step + 1) + ' {{ __('de') }} 5'"></span>
                    <span class="text-xs text-ink-muted"><span aria-hidden="true" class="text-danger">*</span> {{ __('Campo obligatorio') }}</span>
                </div>

                <div role="progressbar" aria-label="{{ __('Avance del alta') }}" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="Math.round(((step + 1) / 5) * 100)" class="h-1.5 rounded-full bg-surface-sunken overflow-hidden">
                    <div class="h-full rounded-full bg-brand transition-all duration-200" :style="{ width: Math.round(((step + 1) / 5) * 100) + '%' }"></div>
                </div>
            </div>

            <div class="p-6 flex-1">
                {{-- Paso 1: Datos personales --}}
                <div x-show="step === 0" x-ref="paso0">
                    <h2 class="m-0 text-2xl font-semibold">{{ __('Datos personales') }}</h2>
                    <p class="m-0 mt-1 mb-5 text-sm text-ink-muted">{{ __('Así aparecerá en el expediente y en los recibos de nómina.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <x-rh.text-field wire:model.blur="nombre_completo" name="nombre_completo" label="{{ __('Nombre(s)') }}" required placeholder="{{ __('Ej. Ana María') }}" :error="$errors->first('nombre_completo')" />
                        <x-rh.text-field wire:model.blur="apellido_paterno" name="apellido_paterno" label="{{ __('Apellido paterno') }}" required placeholder="{{ __('Ej. López') }}" :error="$errors->first('apellido_paterno')" />
                        <x-rh.text-field wire:model.blur="apellido_materno" name="apellido_materno" label="{{ __('Apellido materno') }}" placeholder="{{ __('Ej. Mireles') }}" />
                        <x-rh.text-field wire:model.blur="fecha_nacimiento" name="fecha_nacimiento" type="date" label="{{ __('Fecha de nacimiento') }}" required :error="$errors->first('fecha_nacimiento')" />

                        <x-rh.select wire:model.blur="estado_civil" name="estado_civil" label="{{ __('Estado civil') }}">
                            <option value="Soltero(a)">{{ __('Soltero(a)') }}</option>
                            <option value="Casado(a)">{{ __('Casado(a)') }}</option>
                            <option value="Unión libre">{{ __('Unión libre') }}</option>
                            <option value="Divorciado(a)">{{ __('Divorciado(a)') }}</option>
                            <option value="Viudo(a)">{{ __('Viudo(a)') }}</option>
                        </x-rh.select>

                        <x-rh.text-field wire:model.blur="telefono" name="telefono" type="tel" label="{{ __('Teléfono') }}" placeholder="5512345678" hint="{{ __('A 10 dígitos, sin lada internacional.') }}" :error="$errors->first('telefono')" />

                        <div class="col-span-2">
                            <x-rh.text-field wire:model.blur="correo_personal" name="correo_personal" type="email" label="{{ __('Correo personal') }}" placeholder="nombre@correo.com" hint="{{ __('Aquí llegarán sus recibos de nómina.') }}" :error="$errors->first('correo_personal')" />
                        </div>
                    </div>
                </div>

                {{-- Paso 2: Identificación --}}
                <div x-show="step === 1" x-ref="paso1">
                    <h2 class="m-0 text-2xl font-semibold">{{ __('Identificación') }}</h2>
                    <p class="m-0 mt-1 mb-5 text-sm text-ink-muted">{{ __('Datos fiscales y de seguridad social.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div class="col-span-2 flex gap-3 px-4 py-3 rounded-lg bg-info-soft text-info text-sm">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"></path></svg>
                            <span>{{ __('Captura los datos tal como aparecen en la Constancia de Situación Fiscal y en el documento del IMSS.') }}</span>
                        </div>

                        <x-rh.text-field wire:model.blur="rfc" name="rfc" label="{{ __('RFC') }}" required mono maxlength="13" placeholder="XAXX010101000" hint="{{ __('13 caracteres con homoclave.') }}" :error="$errors->first('rfc')" />
                        <x-rh.text-field wire:model.blur="curp" name="curp" label="{{ __('CURP') }}" required mono maxlength="18" placeholder="XAXX010101HDFXXX00" hint="{{ __('18 caracteres.') }}" :error="$errors->first('curp')" />
                        <x-rh.text-field wire:model.blur="nss" name="nss" label="{{ __('NSS') }}" mono maxlength="11" placeholder="12345678901" hint="{{ __('Número de Seguridad Social: 11 dígitos.') }}" :error="$errors->first('nss')" />

                        <x-rh.select wire:model.blur="regimen_fiscal" name="regimen_fiscal" label="{{ __('Régimen fiscal') }}">
                            <option value="Sueldos y salarios">{{ __('Sueldos y salarios') }}</option>
                            <option value="Asimilados a salarios">{{ __('Asimilados a salarios') }}</option>
                        </x-rh.select>
                    </div>
                </div>

                {{-- Paso 3: Domicilio --}}
                <div x-show="step === 2" x-ref="paso2">
                    <h2 class="m-0 text-2xl font-semibold">{{ __('Domicilio') }}</h2>
                    <p class="m-0 mt-1 mb-5 text-sm text-ink-muted">{{ __('Dirección actual del colaborador.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div class="col-span-2">
                            <x-rh.text-field wire:model.blur="domicilio" name="domicilio" label="{{ __('Calle y número') }}" required placeholder="{{ __('Av. Reforma 123, int. 4') }}" :error="$errors->first('domicilio')" />
                        </div>

                        <x-rh.text-field wire:model.blur="colonia" name="colonia" label="{{ __('Colonia') }}" required placeholder="{{ __('Juárez') }}" :error="$errors->first('colonia')" />
                        <x-rh.text-field wire:model.blur="codigo_postal" name="codigo_postal" label="{{ __('Código postal') }}" required maxlength="5" placeholder="06600" :error="$errors->first('codigo_postal')" />
                        <x-rh.text-field wire:model.blur="municipio" name="municipio" label="{{ __('Municipio o alcaldía') }}" placeholder="{{ __('Cuauhtémoc') }}" />

                        <x-rh.select wire:model.blur="estado_direccion" name="estado_direccion" label="{{ __('Estado') }}" required :error="$errors->first('estado_direccion')">
                            @foreach (['Ciudad de México', 'Estado de México', 'Jalisco', 'Nuevo León', 'Puebla', 'Querétaro', 'Otro'] as $estadoOpcion)
                                <option value="{{ $estadoOpcion }}">{{ $estadoOpcion }}</option>
                            @endforeach
                        </x-rh.select>
                    </div>
                </div>

                {{-- Paso 4: Datos laborales --}}
                <div x-show="step === 3" x-ref="paso3">
                    <h2 class="m-0 text-2xl font-semibold">{{ __('Datos laborales') }}</h2>
                    <p class="m-0 mt-1 mb-5 text-sm text-ink-muted">{{ __('Condiciones de trabajo con las que inicia.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <x-rh.select wire:model.blur="puesto_id" name="puesto_id" label="{{ __('Puesto') }}" required :error="$errors->first('puesto_id')">
                            @foreach ($this->puestos as $puesto)
                                <option value="{{ $puesto->id }}">{{ $puesto->nombre }} — {{ $puesto->departamento->nombre }}</option>
                            @endforeach
                        </x-rh.select>

                        <x-rh.text-field wire:model.blur="fecha_ingreso" name="fecha_ingreso" type="date" label="{{ __('Fecha de ingreso') }}" required :error="$errors->first('fecha_ingreso')" />

                        <x-rh.select wire:model.blur="tipo_contrato" name="tipo_contrato" label="{{ __('Tipo de contrato') }}" required :error="$errors->first('tipo_contrato')">
                            <option value="indeterminado">{{ __('Tiempo indeterminado') }}</option>
                            <option value="determinado">{{ __('Tiempo determinado') }}</option>
                            <option value="periodo_prueba">{{ __('Periodo de prueba') }}</option>
                            <option value="capacitacion_inicial">{{ __('Capacitación inicial') }}</option>
                        </x-rh.select>

                        <x-rh.text-field wire:model.blur="salario_diario" name="salario_diario" type="number" step="0.01" label="{{ __('Salario diario') }}" required prefix="$" suffix="MXN" hint="{{ __('Base para el cálculo de nómina y cuotas IMSS.') }}" :error="$errors->first('salario_diario')" />

                        <x-rh.select wire:model.blur="jornada" name="jornada" label="{{ __('Jornada') }}">
                            <option value="diurna">{{ __('Diurna') }}</option>
                            <option value="nocturna">{{ __('Nocturna') }}</option>
                            <option value="mixta">{{ __('Mixta') }}</option>
                        </x-rh.select>

                        <div class="col-span-2">
                            <x-rh.select wire:model.blur="jefe_directo_id" name="jefe_directo_id" label="{{ __('Jefe directo') }}">
                                @foreach ($this->jefes as $jefe)
                                    <option value="{{ $jefe->id }}">{{ $jefe->nombreCompleto() }}</option>
                                @endforeach
                            </x-rh.select>
                        </div>
                    </div>
                </div>

                {{-- Paso 5: Revisión --}}
                <div x-show="step === 4" x-ref="paso4">
                    <h2 class="m-0 text-2xl font-semibold">{{ __('Revisión') }}</h2>
                    <p class="m-0 mt-1 mb-5 text-sm text-ink-muted">{{ __('Revisa la información antes de crear el expediente.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        @php
                            $sinCapturar = __('Sin capturar');
                            $dato = fn ($valor) => filled($valor) ? $valor : $sinCapturar;
                        @endphp

                        <div class="border border-line rounded-lg p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <h3 class="m-0 text-base font-semibold">{{ __('Datos personales') }}</h3>
                                <button type="button" @click="goTo(0)" class="text-sm font-medium text-brand hover:underline">{{ __('Editar') }}</button>
                            </div>
                            <dl class="m-0 flex flex-col gap-2 text-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Nombre') }}</dt><dd class="m-0">{{ $dato(trim("$nombre_completo $apellido_paterno $apellido_materno")) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Nacimiento') }}</dt><dd class="m-0">{{ $dato($fecha_nacimiento) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Teléfono') }}</dt><dd class="m-0">{{ $dato($telefono) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Correo') }}</dt><dd class="m-0">{{ $dato($correo_personal) }}</dd></div>
                            </dl>
                        </div>

                        <div class="border border-line rounded-lg p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <h3 class="m-0 text-base font-semibold">{{ __('Identificación') }}</h3>
                                <button type="button" @click="goTo(1)" class="text-sm font-medium text-brand hover:underline">{{ __('Editar') }}</button>
                            </div>
                            <dl class="m-0 flex flex-col gap-2 text-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('RFC') }}</dt><dd class="m-0 font-mono">{{ $dato(mb_strtoupper($rfc)) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('CURP') }}</dt><dd class="m-0 font-mono">{{ $dato(mb_strtoupper($curp)) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('NSS') }}</dt><dd class="m-0 font-mono">{{ $dato($nss) }}</dd></div>
                            </dl>
                        </div>

                        <div class="border border-line rounded-lg p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <h3 class="m-0 text-base font-semibold">{{ __('Domicilio') }}</h3>
                                <button type="button" @click="goTo(2)" class="text-sm font-medium text-brand hover:underline">{{ __('Editar') }}</button>
                            </div>
                            <dl class="m-0 flex flex-col gap-2 text-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Calle') }}</dt><dd class="m-0">{{ $dato($domicilio) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Colonia') }}</dt><dd class="m-0">{{ $dato($colonia) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('C.P.') }}</dt><dd class="m-0">{{ $dato($codigo_postal) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Estado') }}</dt><dd class="m-0">{{ $dato($estado_direccion) }}</dd></div>
                            </dl>
                        </div>

                        <div class="border border-line rounded-lg p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <h3 class="m-0 text-base font-semibold">{{ __('Datos laborales') }}</h3>
                                <button type="button" @click="goTo(3)" class="text-sm font-medium text-brand hover:underline">{{ __('Editar') }}</button>
                            </div>
                            <dl class="m-0 flex flex-col gap-2 text-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Puesto') }}</dt><dd class="m-0">{{ $dato(optional($this->puestos->firstWhere('id', $puesto_id))->nombre) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Contrato') }}</dt><dd class="m-0">{{ $dato($tipo_contrato ? str_replace('_', ' ', ucfirst($tipo_contrato)) : null) }}</dd></div>
                                <div class="grid grid-cols-[140px_1fr] gap-3"><dt class="text-ink-muted">{{ __('Salario diario') }}</dt><dd class="m-0">{{ $salario_diario !== '' ? '$'.$salario_diario.' MXN' : $sinCapturar }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2 px-6 py-4 border-t border-line bg-surface-sunken rounded-b-xl">
                <x-rh.button type="button" variant="secondary" @click="anterior()" x-bind:disabled="step === 0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"></path></svg>
                    {{ __('Anterior') }}
                </x-rh.button>

                <div class="flex items-center gap-3">
                    <span x-show="guardando" x-cloak class="text-sm text-success">{{ __('Borrador guardado') }}</span>

                    <x-rh.button type="button" variant="ghost" @click="guardarBorrador()">{{ __('Guardar borrador') }}</x-rh.button>

                    <div x-show="step < 4">
                        <x-rh.button type="button" variant="primary" @click="siguiente()">
                            {{ __('Siguiente') }}
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"></path></svg>
                        </x-rh.button>
                    </div>

                    <div x-show="step === 4">
                        <x-rh.button type="submit" variant="primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            {{ __('Crear expediente') }}
                        </x-rh.button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
