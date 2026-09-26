<?php

use App\Models\EmpresaConfig;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Configuración de la empresa')] class extends Component
{
    public string $razon_social = '';

    public string $nombre_comercial = '';

    public string $rfc = '';

    public string $domicilio_fiscal = '';

    public string $registro_patronal_imss = '';

    public string $representante_legal = '';

    public string $escritura_constitutiva = '';

    public string $poder_representante = '';

    public string $ciudad_firma = '';

    public string $entidad_jurisdiccion = '';

    public string $correo_privacidad = '';

    public string $url_aviso_privacidad = '';

    public string $horario_privacidad = '';

    public function mount(): void
    {
        $this->authorize('empresa.gestionar');

        $empresa = EmpresaConfig::current();

        $this->escritura_constitutiva = (string) $empresa->escritura_constitutiva;
        $this->poder_representante = (string) $empresa->poder_representante;
        $this->ciudad_firma = (string) $empresa->ciudad_firma;
        $this->entidad_jurisdiccion = (string) $empresa->entidad_jurisdiccion;
        $this->correo_privacidad = (string) $empresa->correo_privacidad;
        $this->url_aviso_privacidad = (string) $empresa->url_aviso_privacidad;
        $this->horario_privacidad = (string) $empresa->horario_privacidad;
        $this->razon_social = $empresa->razon_social ?? '';
        $this->nombre_comercial = (string) $empresa->nombre_comercial;
        $this->rfc = $empresa->rfc ?? '';
        $this->domicilio_fiscal = $empresa->domicilio_fiscal ?? '';
        $this->registro_patronal_imss = (string) $empresa->registro_patronal_imss;
        $this->representante_legal = (string) $empresa->representante_legal;
    }

    public function save(): void
    {
        $this->authorize('empresa.gestionar');

        $validated = $this->validate([
            'escritura_constitutiva' => ['nullable', 'string', 'max:10000'],
            'poder_representante' => ['nullable', 'string', 'max:10000'],
            'ciudad_firma' => ['nullable', 'string', 'max:255'],
            'entidad_jurisdiccion' => ['nullable', 'string', 'max:255'],
            'correo_privacidad' => ['nullable', 'email', 'max:255'],
            'url_aviso_privacidad' => ['nullable', 'url:http,https', 'max:2048'],
            'horario_privacidad' => ['nullable', 'string', 'max:255'],
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'max:13'],
            'domicilio_fiscal' => ['required', 'string'],
            'registro_patronal_imss' => ['nullable', 'string', 'max:255'],
            'representante_legal' => ['nullable', 'string', 'max:255'],
        ]);

        EmpresaConfig::query()->updateOrCreate(['id' => 1], $validated);

        Flux::toast(variant: 'success', text: __('Datos de la empresa actualizados.'));
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">{{ __('Configuración de la empresa') }}</flux:heading>
    <flux:subheading>{{ __('Estos datos se usan para generar contratos y recibos de nómina.') }}</flux:subheading>

    <form wire:submit="save" class="mt-6 max-w-lg space-y-6">
        <flux:input wire:model="razon_social" :label="__('Razón social')" required />
        <flux:input wire:model="nombre_comercial" :label="__('Nombre comercial')" />
        <flux:input wire:model="rfc" :label="__('RFC')" required maxlength="13" />
        <flux:textarea wire:model="domicilio_fiscal" :label="__('Domicilio fiscal')" required />
        <flux:input wire:model="registro_patronal_imss" :label="__('Registro patronal IMSS')" />
        <flux:input wire:model="representante_legal" :label="__('Representante legal')" />

        <flux:separator />
        <flux:heading>Datos legales y privacidad para contratos</flux:heading>
        <flux:text>Completa estos datos para generar el contrato y sus anexos. Se utilizarán en todos los contratos de la empresa.</flux:text>
        <flux:textarea wire:model="escritura_constitutiva" :label="__('Escritura constitutiva (número, volumen, notaría, ciudad y notario)')" />
        <flux:textarea wire:model="poder_representante" :label="__('Instrumento que acredita al representante legal')" />
        <flux:input wire:model="ciudad_firma" :label="__('Ciudad de celebración y firma')" />
        <flux:input wire:model="entidad_jurisdiccion" :label="__('Entidad de jurisdicción')" />
        <flux:input wire:model="correo_privacidad" :label="__('Correo de privacidad')" type="email" />
        <flux:input wire:model="url_aviso_privacidad" :label="__('URL del aviso de privacidad integral')" type="url" />
        <flux:input wire:model="horario_privacidad" :label="__('Horario de atención de privacidad')" />

        <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
    </form>
</section>
