<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #1a1a1a; }
        h1 { font-size: 15px; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .subtitle { text-align: center; color: #555; margin-bottom: 20px; }
        table.info { width: 100%; margin-bottom: 16px; }
        table.info td { padding: 2px 0; }
        table.info td:first-child { font-weight: bold; width: 160px; }
        table.amounts { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.amounts th, table.amounts td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        table.amounts th { background: #f2f2f2; }
        table.amounts td.amount { text-align: right; }
        .totals { margin-top: 14px; width: 100%; }
        .totals td { padding: 3px 0; }
        .totals td:first-child { font-weight: bold; }
        .totals .neto { font-size: 13px; font-weight: bold; }
        .disclaimer { margin-top: 30px; font-size: 9px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>Recibo de Nómina</h1>
    <p class="subtitle">Documento administrativo interno — {{ $empresa->razon_social }}</p>

    <table class="info">
        <tr><td>Empleado:</td><td>{{ $empleado->nombre_completo }} ({{ $empleado->numero_empleado }})</td></tr>
        <tr><td>Puesto:</td><td>{{ $empleado->puesto->nombre }}</td></tr>
        <tr><td>Periodo:</td><td>{{ $periodo->fecha_inicio->format('d/m/Y') }} — {{ $periodo->fecha_fin->format('d/m/Y') }} ({{ ucfirst($periodo->tipo) }})</td></tr>
        <tr><td>Fecha de pago:</td><td>{{ $periodo->fecha_pago->format('d/m/Y') }}</td></tr>
        <tr><td>Días trabajados:</td><td>{{ $detalle->dias_trabajados }}</td></tr>
        <tr><td>Salario diario:</td><td>${{ number_format((float) $detalle->salario_diario, 2) }}</td></tr>
        <tr><td>SDI (informativo):</td><td>${{ number_format((float) $detalle->sdi, 2) }}</td></tr>
    </table>

    <table class="amounts">
        <thead>
            <tr><th>Percepciones</th><th class="amount">Monto</th></tr>
        </thead>
        <tbody>
            <tr><td>Salario</td><td class="amount">${{ number_format((float) $detalle->percepcion_salario, 2) }}</td></tr>
            @if ($detalle->percepcion_comision > 0)
                <tr><td>Comisiones</td><td class="amount">${{ number_format((float) $detalle->percepcion_comision, 2) }}</td></tr>
            @endif
            @if ($detalle->percepcion_aguinaldo > 0)
                <tr><td>Aguinaldo</td><td class="amount">${{ number_format((float) $detalle->percepcion_aguinaldo, 2) }}</td></tr>
            @endif
            @if ($detalle->percepcion_ptu > 0)
                <tr><td>PTU</td><td class="amount">${{ number_format((float) $detalle->percepcion_ptu, 2) }}</td></tr>
            @endif
            @if ($detalle->percepcion_otras > 0)
                <tr><td>Otras percepciones</td><td class="amount">${{ number_format((float) $detalle->percepcion_otras, 2) }}</td></tr>
            @endif
        </tbody>
    </table>

    <table class="amounts" style="margin-top: 14px;">
        <thead>
            <tr><th>Deducciones</th><th class="amount">Monto</th></tr>
        </thead>
        <tbody>
            <tr><td>ISR</td><td class="amount">${{ number_format((float) $detalle->deduccion_isr, 2) }}</td></tr>
            <tr><td>IMSS (estimado)</td><td class="amount">${{ number_format((float) $detalle->deduccion_imss, 2) }}</td></tr>
            @if ($detalle->deduccion_otras > 0)
                <tr><td>Otras deducciones</td><td class="amount">${{ number_format((float) $detalle->deduccion_otras, 2) }}</td></tr>
            @endif
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Total percepciones:</td><td>${{ number_format((float) $detalle->total_percepciones, 2) }}</td></tr>
        <tr><td>Total deducciones:</td><td>${{ number_format((float) $detalle->total_deducciones, 2) }}</td></tr>
        <tr class="neto"><td>Neto a pagar:</td><td>${{ number_format((float) $detalle->neto_pagar, 2) }}</td></tr>
    </table>

    <p class="disclaimer">
        La deducción de IMSS mostrada es una estimación simplificada y no sustituye el cálculo oficial del IMSS.
        Este recibo es un documento administrativo interno y no constituye un CFDI de nómina 4.0 timbrado ante el SAT.
    </p>
</body>
</html>
