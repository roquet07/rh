<?php

namespace App\Actions\Nomina;

use App\Models\EmpresaConfig;
use App\Models\NominaDetalle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerarReciboNomina
{
    /**
     * Render the recibo de nómina PDF, store it, and return its storage path.
     */
    public function generar(NominaDetalle $detalle): string
    {
        $detalle->loadMissing('empleado.puesto', 'periodoNomina');

        $pdf = Pdf::loadView('pdf.recibo-nomina', [
            'empresa' => EmpresaConfig::current(),
            'detalle' => $detalle,
            'empleado' => $detalle->empleado,
            'periodo' => $detalle->periodoNomina,
        ]);

        $path = "recibos/recibo-{$detalle->periodo_nomina_id}-{$detalle->empleado_id}.pdf";

        Storage::disk('local')->put($path, $pdf->output());

        $detalle->update(['recibo_pdf_path' => $path]);

        return $path;
    }
}
