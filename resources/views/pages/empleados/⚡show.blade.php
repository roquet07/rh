<?php

use App\Actions\Empleados\ActualizarFotoEmpleado;
use App\Actions\Empleados\EliminarDocumentoEmpleado;
use App\Actions\Empleados\EliminarFotoEmpleado;
use App\Actions\Empleados\RevisarDocumentacionEmpleado;
use App\Actions\Empleados\SubirDocumentoEmpleado;
use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Empleado;
use Flux\Flux;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Expediente de empleado')] class extends Component {
    use WithFileUploads;

    public Empleado $empleado;

    /** @var array<int, UploadedFile|null> */
    public array $archivos = [];

    public string $comentarioRechazo = '';

    public ?UploadedFile $foto = null;

    public function mount(Empleado $empleado): void
    {
        $this->empleado = $empleado->load('puesto.departamento', 'contratos', 'comisiones', 'documentos.documentoTipo');
    }

    /**
     * @return array<int, DocumentoTipo>
     */
    #[Computed]
    public function tiposDocumento()
    {
        return DocumentoTipo::query()->where('activo', true)->orderBy('orden')->orderBy('nombre')->get();
    }

    public function subirDocumento(int $documentoTipoId, SubirDocumentoEmpleado $accion): void
    {
        $this->validate([
            "archivos.$documentoTipoId" => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $accion($this->empleado, DocumentoTipo::findOrFail($documentoTipoId), $this->archivos[$documentoTipoId], auth()->user());

        unset($this->archivos[$documentoTipoId]);
        $this->empleado->load('documentos.documentoTipo');

        Flux::toast(variant: 'success', text: __('Documento cargado.'));
    }

    public function eliminarDocumento(Documento $documento, EliminarDocumentoEmpleado $accion): void
    {
        $accion($documento);
        $this->empleado->load('documentos.documentoTipo');

        Flux::toast(variant: 'success', text: __('Documento eliminado.'));
    }

    public function descargarDocumento(Documento $documento)
    {
        return Storage::disk('local')->download($documento->path, $documento->nombre_original);
    }

    public function aprobarDocumentacion(RevisarDocumentacionEmpleado $accion): void
    {
        $progreso = $this->empleado->progresoDocumentacion();

        if ($progreso['completados'] < $progreso['total']) {
            Flux::toast(variant: 'danger', text: __('Faltan documentos obligatorios por capturar.'));

            return;
        }

        $accion->aprobar($this->empleado, auth()->user());
        $this->empleado->refresh();

        Flux::toast(variant: 'success', text: __('Documentación aprobada. El empleado ahora está activo.'));
    }

    public function rechazarDocumentacion(RevisarDocumentacionEmpleado $accion): void
    {
        $this->validate(['comentarioRechazo' => ['required', 'string', 'max:1000']]);

        $accion->rechazar($this->empleado, auth()->user(), $this->comentarioRechazo);
        $this->empleado->refresh();
        $this->reset('comentarioRechazo');

        Flux::toast(variant: 'warning', text: __('Documentación rechazada.'));
    }

    public function subirFoto(ActualizarFotoEmpleado $accion): void
    {
        $this->validate(['foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120']]);

        $accion($this->empleado, $this->foto);

        $this->reset('foto');
        $this->empleado->refresh();

        Flux::toast(variant: 'success', text: __('Foto actualizada.'));
    }

    public function eliminarFoto(EliminarFotoEmpleado $accion): void
    {
        $accion($this->empleado);
        $this->empleado->refresh();

        Flux::toast(variant: 'success', text: __('Foto eliminada.'));
    }
}; ?>

<section class="w-full">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="flex flex-col items-center gap-1.5">
                <x-rh.avatar :name="$empleado->nombreCompleto()" :photo-url="$empleado->foto_path ? route('empleados.foto', $empleado) : null" size="lg" />

                @can('empleados.gestionar')
                    <div class="flex flex-col items-center gap-1">
                        <input type="file" wire:model="foto" id="foto-empleado" class="hidden">
                        <div class="flex items-center gap-1.5">
                            <label for="foto-empleado" class="text-xs font-medium text-brand hover:underline cursor-pointer">{{ __('Cambiar foto') }}</label>
                            @if ($empleado->foto_path)
                                <span class="text-xs text-ink-muted">·</span>
                                <button type="button" wire:click="eliminarFoto" wire:confirm="{{ __('¿Quitar la foto del empleado?') }}" class="text-xs font-medium text-danger hover:underline cursor-pointer">{{ __('Quitar') }}</button>
                            @endif
                        </div>
                        @if ($foto)
                            <x-rh.button size="sm" wire:click="subirFoto">{{ __('Confirmar') }}</x-rh.button>
                        @endif
                        @error('foto')
                            <p class="text-xs font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                @endcan
            </div>

            <div>
                <flux:heading size="xl">{{ $empleado->nombre_completo }}</flux:heading>
                <flux:subheading>{{ $empleado->numero_empleado }} — {{ $empleado->puesto->nombre }} ({{ $empleado->puesto->departamento->nombre }})</flux:subheading>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-rh.badge :estado="$empleado->estadoLaboral()" />

            @can('empleados.gestionar')
                <flux:button variant="filled" icon="pencil" :href="route('empleados.edit', $empleado)" wire:navigate>
                    {{ __('Editar') }}
                </flux:button>
            @endcan

            @can('contratos.gestionar')
                <flux:button variant="primary" icon="document-text" :href="route('contratos.create', $empleado)" wire:navigate>
                    {{ __('Nuevo contrato') }}
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card>
            <flux:heading size="lg">{{ __('Datos personales') }}</flux:heading>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('RFC') }}</dt><dd>{{ $empleado->rfc }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('CURP') }}</dt><dd>{{ $empleado->curp }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('NSS') }}</dt><dd>{{ $empleado->nss ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Fecha de nacimiento') }}</dt><dd>{{ $empleado->fecha_nacimiento->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Estado civil') }}</dt><dd>{{ $empleado->estado_civil ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Teléfono') }}</dt><dd>{{ $empleado->telefono ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Domicilio') }}</dt><dd class="text-end">{{ $empleado->domicilio ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Contacto de emergencia') }}</dt><dd class="text-end">{{ $empleado->contacto_emergencia_nombre ?: '—' }} {{ $empleado->contacto_emergencia_telefono }}</dd></div>
            </dl>
        </flux:card>

        <flux:card>
            <flux:heading size="lg">{{ __('Datos laborales') }}</flux:heading>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Fecha de ingreso') }}</dt><dd>{{ $empleado->fecha_ingreso->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Antigüedad') }}</dt><dd>{{ $empleado->aniosAntiguedad() }} {{ __('años') }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Salario diario') }}</dt><dd>${{ number_format((float) $empleado->salario_diario, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Jornada') }}</dt><dd class="capitalize">{{ $empleado->jornada }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Sucursal') }}</dt><dd>{{ $empleado->sucursal ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Banco') }}</dt><dd>{{ $empleado->banco ?: '—' }}</dd></div>
                @if ($empleado->estatus === 'baja')
                    <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Fecha de baja') }}</dt><dd>{{ $empleado->fecha_baja?->format('d/m/Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">{{ __('Motivo') }}</dt><dd class="text-end">{{ $empleado->motivo_baja }}</dd></div>
                @endif
            </dl>
        </flux:card>
    </div>

    <flux:card class="mt-6">
        <flux:heading size="lg">{{ __('Contratos') }}</flux:heading>

        <flux:table class="mt-4">
            <flux:table.columns>
                <flux:table.column>{{ __('Tipo') }}</flux:table.column>
                <flux:table.column>{{ __('Vigencia') }}</flux:table.column>
                <flux:table.column>{{ __('Estatus') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($empleado->contratos as $contrato)
                    <flux:table.row wire:key="contrato-{{ $contrato->id }}">
                        <flux:table.cell class="capitalize">{{ str_replace('_', ' ', $contrato->tipo) }}</flux:table.cell>
                        <flux:table.cell>{{ $contrato->fecha_inicio->format('d/m/Y') }} — {{ $contrato->fecha_fin?->format('d/m/Y') ?? __('indefinido') }}</flux:table.cell>
                        <flux:table.cell class="capitalize">{{ $contrato->estatus }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:link :href="route('contratos.show', $contrato)" wire:navigate>{{ __('Ver') }}</flux:link>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">{{ __('Sin contratos registrados.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:card class="mt-6">
        <flux:heading size="lg">{{ __('Comisiones') }}</flux:heading>

        <flux:table class="mt-4">
            <flux:table.columns>
                <flux:table.column>{{ __('Periodo') }}</flux:table.column>
                <flux:table.column>{{ __('Monto base') }}</flux:table.column>
                <flux:table.column>{{ __('%') }}</flux:table.column>
                <flux:table.column>{{ __('Comisión') }}</flux:table.column>
                <flux:table.column>{{ __('Estatus') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($empleado->comisiones as $comision)
                    <flux:table.row wire:key="comision-{{ $comision->id }}">
                        <flux:table.cell>{{ $comision->fecha_periodo_inicio->format('d/m/Y') }} — {{ $comision->fecha_periodo_fin->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>${{ number_format((float) $comision->monto_base, 2) }}</flux:table.cell>
                        <flux:table.cell>{{ number_format((float) $comision->porcentaje_aplicado, 2) }}%</flux:table.cell>
                        <flux:table.cell>${{ number_format((float) $comision->monto_comision, 2) }}</flux:table.cell>
                        <flux:table.cell class="capitalize">{{ $comision->estatus }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">{{ __('Sin comisiones registradas.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    @php($progresoDocumentacion = $empleado->progresoDocumentacion())
    <flux:card class="mt-6">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Documentación') }}</flux:heading>

            <flux:badge :color="$progresoDocumentacion['completados'] === $progresoDocumentacion['total'] ? 'lime' : 'amber'">
                {{ $progresoDocumentacion['completados'] }}/{{ $progresoDocumentacion['total'] }} {{ __('completados') }}
            </flux:badge>
        </div>

        <div role="progressbar" aria-label="{{ __('Avance de documentación') }}" aria-valuemin="0" aria-valuemax="{{ $progresoDocumentacion['total'] }}" aria-valuenow="{{ $progresoDocumentacion['completados'] }}" class="mt-3 h-1.5 rounded-full bg-surface-sunken overflow-hidden">
            <div class="h-full rounded-full bg-brand" style="width: {{ $progresoDocumentacion['total'] > 0 ? round($progresoDocumentacion['completados'] / $progresoDocumentacion['total'] * 100) : 0 }}%"></div>
        </div>

        <flux:table class="mt-4">
            <flux:table.columns>
                <flux:table.column>{{ __('Tipo de documento') }}</flux:table.column>
                <flux:table.column>{{ __('Estatus') }}</flux:table.column>
                <flux:table.column>{{ __('Archivo') }}</flux:table.column>
                @can('empleados-documentos.capturar')
                    <flux:table.column></flux:table.column>
                @endcan
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->tiposDocumento as $tipo)
                    @php($documento = $empleado->documentos->firstWhere('documento_tipo_id', $tipo->id))
                    <flux:table.row wire:key="tipo-doc-{{ $tipo->id }}">
                        <flux:table.cell>
                            {{ $tipo->nombre }}
                            @if ($tipo->obligatorio)
                                <span class="text-danger" aria-hidden="true">*</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$documento ? 'lime' : ($tipo->obligatorio ? 'red' : 'zinc')">
                                {{ $documento ? __('Cargado') : __('Pendiente') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($documento)
                                <flux:link wire:click="descargarDocumento({{ $documento->id }})">{{ $documento->nombre_original }}</flux:link>
                            @else
                                —
                            @endif
                        </flux:table.cell>
                        @can('empleados-documentos.capturar')
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <input type="file" wire:model="archivos.{{ $tipo->id }}" class="text-xs text-ink-muted file:mr-2 file:rounded-md file:border-0 file:bg-surface-sunken file:px-2 file:py-1 file:text-xs file:font-medium file:text-ink hover:file:bg-line">
                                    <x-rh.button size="sm" wire:click="subirDocumento({{ $tipo->id }})">{{ $documento ? __('Reemplazar') : __('Subir') }}</x-rh.button>
                                    @if ($documento)
                                        <x-rh.button size="sm" variant="secondary" wire:click="eliminarDocumento({{ $documento->id }})" wire:confirm="{{ __('¿Eliminar este documento?') }}">{{ __('Eliminar') }}</x-rh.button>
                                    @endif
                                </div>
                                @error("archivos.{$tipo->id}")
                                    <p class="mt-1 text-xs font-medium text-danger">{{ $message }}</p>
                                @enderror
                            </flux:table.cell>
                        @endcan
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">{{ __('No hay tipos de documento activos en el catálogo.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @can('empleados-documentos.aprobar')
            @if ($empleado->estatus === 'documentacion_pendiente')
                <div class="mt-4 flex flex-col gap-3 border-t border-line pt-4">
                    @if ($empleado->documentacion_estatus === 'rechazada')
                        <p class="m-0 text-sm text-danger">{{ __('Rechazado') }}: {{ $empleado->documentacion_comentario_revision }}</p>
                    @endif

                    <div class="flex flex-wrap items-start gap-2">
                        <x-rh.button variant="primary" wire:click="aprobarDocumentacion" wire:confirm="{{ __('¿Aprobar documentación y activar al empleado?') }}">
                            {{ __('Aprobar documentación') }}
                        </x-rh.button>

                        <textarea wire:model="comentarioRechazo" placeholder="{{ __('Motivo del rechazo') }}" rows="1" class="flex-1 min-w-[220px] rounded-md border border-line-strong bg-surface-raised px-3 py-2 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus"></textarea>

                        <x-rh.button variant="secondary" wire:click="rechazarDocumentacion">{{ __('Rechazar') }}</x-rh.button>
                    </div>
                    @error('comentarioRechazo')
                        <p class="m-0 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        @endcan
    </flux:card>
</section>
