<?php

use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\SolicitudVacante;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Nueva solicitud de vacante')] class extends Component {
    public ?int $departamento_id = null;

    public ?int $puesto_id = null;

    public string $puesto_propuesto = '';

    public string $salario_propuesto = '';

    public int $numero_plazas = 1;

    public string $justificacion = '';

    public string $urgencia = 'media';

    #[Computed]
    public function departamentos()
    {
        return Departamento::query()->where('activo', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function puestos()
    {
        if ($this->departamento_id === null) {
            return collect();
        }

        return Puesto::query()->where('departamento_id', $this->departamento_id)->where('activo', true)->orderBy('nombre')->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'puesto_id' => ['nullable', 'integer', 'exists:puestos,id'],
            'puesto_propuesto' => ['nullable', 'required_without:puesto_id', 'string', 'max:255'],
            'salario_propuesto' => ['required', 'numeric', 'min:0'],
            'numero_plazas' => ['required', 'integer', 'min:1'],
            'justificacion' => ['required', 'string'],
            'urgencia' => ['required', Rule::in(['baja', 'media', 'alta'])],
        ]);

        $anio = now()->year;
        $siguiente = SolicitudVacante::query()->whereYear('created_at', $anio)->count() + 1;

        $solicitud = SolicitudVacante::query()->create($validated + [
            'folio' => "SOL-{$anio}-".str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT),
            'solicitante_user_id' => Auth::id(),
            'estatus' => 'pendiente',
        ]);

        Flux::toast(variant: 'success', text: __('Solicitud :folio creada.', ['folio' => $solicitud->folio]));

        $this->redirect(route('solicitudes.show', $solicitud), navigate: true);
    }
}; ?>

<section class="w-full flex flex-col gap-8">
    <div class="flex flex-col gap-1">
        <h1 class="m-0 text-3xl font-semibold tracking-tight">{{ __('Nueva solicitud de vacante') }}</h1>
        <p class="m-0 text-base text-ink-muted">{{ __('Solicita la apertura de una nueva plaza para revisión de RH.') }}</p>
    </div>

    <x-rh.card class="max-w-lg">
        <form wire:submit="save" class="flex flex-col gap-5">
            <x-rh.select wire:model.live="departamento_id" name="departamento_id" label="{{ __('Departamento') }}" required :error="$errors->first('departamento_id')">
                @foreach ($this->departamentos as $departamento)
                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                @endforeach
            </x-rh.select>

            <x-rh.select wire:model="puesto_id" name="puesto_id" label="{{ __('Puesto existente (opcional)') }}" placeholder="{{ __('Puesto nuevo (especificar abajo)') }}">
                @foreach ($this->puestos as $puesto)
                    <option value="{{ $puesto->id }}">{{ $puesto->nombre }}</option>
                @endforeach
            </x-rh.select>

            <x-rh.text-field wire:model="puesto_propuesto" name="puesto_propuesto" label="{{ __('Nombre de puesto propuesto (si es nuevo)') }}" :error="$errors->first('puesto_propuesto')" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-rh.text-field wire:model="salario_propuesto" name="salario_propuesto" type="number" step="0.01" label="{{ __('Salario diario propuesto') }}" required prefix="$" suffix="MXN" :error="$errors->first('salario_propuesto')" />
                <x-rh.text-field wire:model="numero_plazas" name="numero_plazas" type="number" min="1" label="{{ __('Número de plazas') }}" required :error="$errors->first('numero_plazas')" />
            </div>

            <x-rh.select wire:model="urgencia" name="urgencia" label="{{ __('Urgencia') }}" required>
                <option value="baja">{{ __('Baja') }}</option>
                <option value="media">{{ __('Media') }}</option>
                <option value="alta">{{ __('Alta') }}</option>
            </x-rh.select>

            <div class="flex flex-col gap-1.5">
                <label for="justificacion" class="text-sm font-medium text-ink">{{ __('Justificación') }} <span aria-hidden="true" class="text-danger">*</span></label>
                <textarea wire:model="justificacion" id="justificacion" name="justificacion" required rows="4" class="w-full rounded-md border border-line-strong bg-surface-raised px-3 py-2 text-sm text-ink focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus"></textarea>
                @error('justificacion')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <x-rh.button variant="secondary" href="{{ route('solicitudes.index') }}">{{ __('Cancelar') }}</x-rh.button>
                <x-rh.button type="submit" variant="primary">{{ __('Enviar solicitud') }}</x-rh.button>
            </div>
        </form>
    </x-rh.card>
</section>
