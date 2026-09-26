<?php

namespace App\Actions\Nomina;

use App\Models\Comision;
use App\Models\Empleado;
use App\Models\NominaDetalle;
use App\Models\PeriodoNomina;
use Illuminate\Support\Facades\DB;

class CalcularPeriodoNomina
{
    public function __construct(
        private readonly CalcularDiasVacaciones $calcularDiasVacaciones,
        private readonly CalcularSDI $calcularSDI,
        private readonly CalcularISR $calcularISR,
        private readonly CalcularIMSS $calcularIMSS,
    ) {}

    /**
     * Calcula y persiste el detalle de nómina de todos los empleados activos en el periodo dado.
     */
    public function calcular(PeriodoNomina $periodo): void
    {
        DB::transaction(function () use ($periodo) {
            $empleados = Empleado::query()
                ->where('fecha_ingreso', '<=', $periodo->fecha_fin)
                ->where(function ($query) use ($periodo) {
                    $query->whereNull('fecha_baja')->orWhere('fecha_baja', '>=', $periodo->fecha_inicio);
                })
                ->get();

            foreach ($empleados as $empleado) {
                $this->calcularParaEmpleado($periodo, $empleado);
            }

            $periodo->update(['estatus' => 'calculado']);
        });
    }

    private function calcularParaEmpleado(PeriodoNomina $periodo, Empleado $empleado): void
    {
        $diasTrabajados = $this->diasTrabajadosEnPeriodo($periodo, $empleado);
        $salarioDiario = (float) $empleado->salario_diario;
        $ejercicioFiscal = (int) $periodo->fecha_pago->year;

        $diasVacaciones = $this->calcularDiasVacaciones->calcular($empleado->aniosAntiguedad($periodo->fecha_fin));
        $sdi = $this->calcularSDI->calcular($salarioDiario, $diasVacaciones);

        $comisiones = Comision::query()
            ->where('empleado_id', $empleado->id)
            ->whereNull('periodo_nomina_id')
            ->where('fecha_periodo_fin', '<=', $periodo->fecha_fin)
            ->get();

        $percepcionComision = (float) $comisiones->sum('monto_comision');
        $percepcionSalario = round($salarioDiario * $diasTrabajados, 2);

        $baseGravable = $percepcionSalario + $percepcionComision;
        $deduccionIsr = $this->calcularISR->calcular($baseGravable, $ejercicioFiscal, $periodo->tipo);
        $deduccionImss = $this->calcularIMSS->calcular($salarioDiario, (int) $diasTrabajados, $ejercicioFiscal)['cuota'];

        $totalPercepciones = round($percepcionSalario + $percepcionComision, 2);
        $totalDeducciones = round($deduccionIsr + $deduccionImss, 2);

        NominaDetalle::query()->updateOrCreate(
            ['periodo_nomina_id' => $periodo->id, 'empleado_id' => $empleado->id],
            [
                'dias_trabajados' => $diasTrabajados,
                'salario_diario' => $salarioDiario,
                'sdi' => $sdi,
                'percepcion_salario' => $percepcionSalario,
                'percepcion_comision' => $percepcionComision,
                'percepcion_aguinaldo' => 0,
                'percepcion_ptu' => 0,
                'percepcion_otras' => 0,
                'deduccion_isr' => $deduccionIsr,
                'deduccion_imss' => $deduccionImss,
                'deduccion_otras' => 0,
                'total_percepciones' => $totalPercepciones,
                'total_deducciones' => $totalDeducciones,
                'neto_pagar' => round($totalPercepciones - $totalDeducciones, 2),
            ],
        );

        $comisiones->each->update(['periodo_nomina_id' => $periodo->id]);
    }

    private function diasTrabajadosEnPeriodo(PeriodoNomina $periodo, Empleado $empleado): float
    {
        $inicio = $empleado->fecha_ingreso->max($periodo->fecha_inicio);
        $fin = $empleado->fecha_baja !== null ? $empleado->fecha_baja->min($periodo->fecha_fin) : $periodo->fecha_fin;

        if ($fin->lt($inicio)) {
            return 0.0;
        }

        return (float) ($inicio->diffInDays($fin) + 1);
    }
}
