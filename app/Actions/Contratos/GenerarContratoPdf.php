<?php

namespace App\Actions\Contratos;

use App\Models\Contrato;
use App\Models\EmpresaConfig;
use App\Support\ContratoRules;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use NumberFormatter;
use RuntimeException;

class GenerarContratoPdf
{
    public function generar(Contrato $contrato): string
    {
        $contrato->refresh()->load('empleado', 'puesto');

        if ($contrato->estatus === 'firmado') {
            throw ValidationException::withMessages(['contrato' => 'No se puede regenerar un contrato firmado.']);
        }

        $empresa = EmpresaConfig::current();
        $datos = $contrato->attributesToArray();
        $datos['fecha_inicio'] = $contrato->fecha_inicio->toDateString();
        $datos['fecha_celebracion'] = $contrato->fecha_celebracion?->toDateString();
        $validados = Validator::make($datos, ContratoRules::rules(), [], ContratoRules::attributes())->validate();
        ContratoRules::validarCondiciones($validados);

        $fechaFin = $contrato->tipo === 'determinado'
            ? $contrato->fecha_inicio->copy()->addMonthsNoOverflow((int) $contrato->duracion_meses)->subDay()->toDateString()
            : null;

        if ($contrato->fecha_fin?->toDateString() !== $fechaFin) {
            throw ValidationException::withMessages(['fecha_fin' => 'Edita el contrato para corregir su vigencia.']);
        }

        $camposEmpresa = [
            'razon_social' => 'razón social',
            'rfc' => 'RFC',
            'domicilio_fiscal' => 'domicilio fiscal',
            'representante_legal' => 'representante legal',
            'escritura_constitutiva' => 'escritura constitutiva',
            'poder_representante' => 'instrumento del representante legal',
            'ciudad_firma' => 'ciudad de firma',
            'entidad_jurisdiccion' => 'entidad de jurisdicción',
            'correo_privacidad' => 'correo de privacidad',
            'url_aviso_privacidad' => 'URL del aviso de privacidad',
            'horario_privacidad' => 'horario de atención de privacidad',
        ];
        $reglasEmpresa = array_fill_keys(array_keys($camposEmpresa), ['required', 'string']);
        $reglasEmpresa['correo_privacidad'] = ['required', 'email'];
        $reglasEmpresa['url_aviso_privacidad'] = ['required', 'url:http,https'];
        Validator::make($empresa->attributesToArray(), $reglasEmpresa, [
            'required' => 'Completa :attribute en la configuración de la empresa.',
        ], $camposEmpresa)->validate();

        Validator::make($contrato->empleado->attributesToArray(), [
            'nombre_completo' => ['required', 'string'],
            'rfc' => ['required', 'string'],
            'curp' => ['required', 'string'],
            'fecha_nacimiento' => ['required', 'date', 'before:'.$datos['fecha_celebracion']],
            'estado_civil' => ['required', 'string'],
            'domicilio' => ['required', 'string'],
        ], ['required' => 'Completa :attribute en el expediente del empleado.'])->validate();

        $salarioCentavos = (int) round((float) $contrato->salario_mensual * 100);
        $letras = (new NumberFormatter('es_MX', NumberFormatter::SPELLOUT))->format(intdiv($salarioCentavos, 100));
        $salarioLetras = $letras.' pesos '.str_pad((string) ($salarioCentavos % 100), 2, '0', STR_PAD_LEFT).'/100 M.N.';
        $domicilioEmpleado = implode(', ', array_filter([
            $contrato->empleado->domicilio, $contrato->empleado->colonia,
            $contrato->empleado->municipio, $contrato->empleado->estado_direccion,
            $contrato->empleado->codigo_postal,
        ]));

        $fechaCelebracion = $contrato->fecha_celebracion->copy();
        $fechaCelebracion->locale('es');

        $pdf = Pdf::loadView('pdf.contrato', [
            'empresa' => $empresa,
            'contrato' => $contrato,
            'empleado' => $contrato->empleado,
            'domicilioEmpleado' => $domicilioEmpleado,
            'edad' => (int) $contrato->empleado->fecha_nacimiento->diffInYears($contrato->fecha_celebracion),
            'fechaCelebracionTexto' => $fechaCelebracion->translatedFormat('j \\d\\e F \\d\\e Y'),
            'salarioLetras' => $salarioLetras,
        ])->setPaper('a4');

        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $font = $pdf->getDomPDF()->getFontMetrics()->getFont('Helvetica');
        $canvas->page_text(280, 810, '{PAGE_NUM} / {PAGE_COUNT}', $font, 9);
        $contenido = $pdf->getDomPDF()->output();
        $path = "contratos/contrato-{$contrato->id}.pdf";

        if (! Storage::disk('local')->put($path, $contenido)) {
            throw new RuntimeException('No se pudo guardar el PDF del contrato.');
        }

        $contrato->update(['pdf_path' => $path, 'estatus' => 'generado']);

        return $path;
    }
}
