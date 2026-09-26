<?php

use App\Actions\Contratos\GenerarContratoPdf;
use App\Models\Contrato;
use Flux\Flux;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Contrato')] class extends Component
{
    use WithFileUploads;

    public Contrato $contrato;

    public ?UploadedFile $documentoFirmado = null;

    public string $fecha_firma = '';

    public function mount(Contrato $contrato): void
    {
        $this->authorize('contratos.ver');

        $this->contrato = $contrato->load('empleado', 'puesto');
        $this->fecha_firma = now()->toDateString();
    }

    public function generarPdf(GenerarContratoPdf $generar): void
    {
        $this->authorize('contratos.gestionar');

        $generar->generar($this->contrato);

        $this->contrato->refresh();

        Flux::toast(variant: 'success', text: __('PDF del contrato generado.'));
    }

    public function descargarPdf(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('contratos.ver');
        $this->contrato->refresh();
        abort_unless($this->contrato->pdf_path && Storage::disk('local')->exists($this->contrato->pdf_path), 404);

        return Storage::disk('local')->download($this->contrato->pdf_path, "contrato-{$this->contrato->empleado->numero_empleado}.pdf");
    }

    public function marcarFirmado(): void
    {
        $this->authorize('contratos.gestionar');
        $this->contrato->refresh();
        abort_if($this->contrato->estatus === 'firmado', 403);
        if (! $this->contrato->pdf_path || ! Storage::disk('local')->exists($this->contrato->pdf_path)) {
            $this->addError('pdf', 'Genera el PDF antes de registrar la firma.');

            return;
        }

        $this->validate([
            'fecha_firma' => ['required', 'date', 'after_or_equal:'.($this->contrato->fecha_celebracion ?? $this->contrato->fecha_inicio)->toDateString(), 'before_or_equal:today'],
            'documentoFirmado' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $path = $this->contrato->documento_firmado_path;

        if ($this->documentoFirmado !== null) {
            $path = $this->documentoFirmado->store('contratos/firmados', 'local');
        }

        $this->contrato->update([
            'estatus' => 'firmado',
            'fecha_firma' => $this->fecha_firma,
            'documento_firmado_path' => $path,
        ]);

        Flux::toast(variant: 'success', text: __('Contrato marcado como firmado.'));
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Contrato') }} — {{ $contrato->empleado->nombreCompleto() }}</h1>
            <p class="m-0 text-base text-ink-muted">{{ $contrato->puesto->nombre }}</p>
        </div>

        <x-rh.badge :estado="$contrato->estatus" />
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-md border border-danger p-4 text-danger">
            <p class="font-semibold">Completa o corrige los siguientes datos antes de continuar:</p>
            <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li wire:key="error-{{ $loop->index }}">{{ $error }}</li>@endforeach</ul>
            @can('empresa.gestionar')<a href="{{ route('empresa.config') }}" wire:navigate class="underline">Configuración de la empresa</a>@endcan
            @can('empleados.gestionar')<a href="{{ route('empleados.edit', $contrato->empleado) }}" wire:navigate class="underline">Expediente del empleado</a>@endcan
        </div>
    @endif

    <x-rh.card>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div><dt class="text-ink-muted">{{ __('Tipo') }}</dt><dd class="m-0 capitalize">{{ str_replace('_', ' ', $contrato->tipo) }}</dd></div>
            <div><dt class="text-ink-muted">{{ __('Vigencia') }}</dt><dd class="m-0">{{ $contrato->fecha_inicio->translatedFormat('d M Y') }} — {{ $contrato->fecha_fin?->translatedFormat('d M Y') ?? __('indefinido') }}</dd></div>
            <div><dt class="text-ink-muted">{{ __('Salario diario') }}</dt><dd class="m-0 tabular-nums">${{ number_format((float) $contrato->salario_diario, 2) }}</dd></div>
            <div><dt class="text-ink-muted">{{ __('Jornada') }}</dt><dd class="m-0 capitalize">{{ $contrato->jornada }}</dd></div>
            <div class="col-span-2"><dt class="text-ink-muted">{{ __('Lugar de trabajo') }}</dt><dd class="m-0">{{ $contrato->lugar_trabajo }}</dd></div>
        </dl>
    </x-rh.card>

    @can('contratos.gestionar')
        <x-rh.card>
            <h2 class="m-0 text-lg font-semibold mb-4">{{ __('Documento') }}</h2>

            <div class="flex flex-wrap gap-2">
                @if ($contrato->estatus !== 'firmado')
                <x-rh.button variant="secondary" href="{{ route('contratos.edit', $contrato) }}">Editar datos del contrato</x-rh.button>
                <x-rh.button variant="primary" wire:click="generarPdf" wire:loading.attr="disabled" wire:target="generarPdf">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.25 3H6.75A1.5 1.5 0 0 0 5.25 4.5v15A1.5 1.5 0 0 0 6.75 21h10.5a1.5 1.5 0 0 0 1.5-1.5V7.5L14.25 3ZM14.25 3v4.5h4.5M9 12.75h6M9 15.75h6"></path></svg>
                    {{ $contrato->pdf_path ? __('Regenerar PDF') : __('Generar PDF') }}
                </x-rh.button>
                @endif

                @if ($contrato->pdf_path)
                    <x-rh.button variant="secondary" wire:click="descargarPdf">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"></path></svg>
                        {{ __('Descargar PDF') }}
                    </x-rh.button>
                @endif
            </div>
        </x-rh.card>

        @if ($contrato->estatus !== 'firmado')
            <x-rh.card>
                <h2 class="m-0 text-lg font-semibold">{{ __('Marcar como firmado') }}</h2>
                <p class="m-0 mt-1 mb-4 text-sm text-ink-muted">{{ __('Registra la firma física y, opcionalmente, adjunta el escaneo del documento firmado.') }}</p>

                <div class="max-w-md flex flex-col gap-4">
                    <x-rh.text-field wire:model="fecha_firma" name="fecha_firma" type="date" label="{{ __('Fecha de firma') }}" required :error="$errors->first('fecha_firma')" />

                    <div class="flex flex-col gap-1.5">
                        <label for="documentoFirmado" class="text-sm font-medium text-ink">{{ __('Documento firmado (opcional)') }}</label>
                        <input wire:model="documentoFirmado" id="documentoFirmado" name="documentoFirmado" type="file" class="text-sm text-ink-muted file:mr-3 file:rounded-md file:border-0 file:bg-surface-sunken file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink hover:file:bg-line">
                        @error('documentoFirmado')
                            <p class="text-xs font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-rh.button variant="primary" wire:click="marcarFirmado" class="self-start">{{ __('Confirmar firma') }}</x-rh.button>
                </div>
            </x-rh.card>
        @endif
    @endcan
</section>
