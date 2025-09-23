<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #{{ $recibo->numero_recibo_formateado }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
            background: #fff;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Header - Datos de la Empresa */
        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            position: relative;
            display: table;
            width: 100%;
        }

        .header-logo {
            display: table-cell;
            width: 120px;
            vertical-align: top;
            padding-right: 15px;
        }

        .header-logo img {
            max-width: 100px;
            max-height: 80px;
            object-fit: contain;
        }

        .header-content {
            display: table-cell;
            vertical-align: top;
            text-align: center;
        }

        .header-no-logo {
            text-align: center;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 3px;
        }

        .company-details {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
        }

        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 8px;
        }

        .recibo-number {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 5px;
        }

        /* Sección de información principal */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-left,
        .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 12px;
            background-color: #f9f9f9;
            margin-bottom: 10px;
        }

        .info-box h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }

        .info-item {
            margin: 3px 0;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            min-width: 80px;
        }

        /* Tabla de items */
        .items-section {
            margin: 20px 0;
        }

        .items-title {
            font-size: 13px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            text-align: left;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }

        .items-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
            text-align: center;
        }

        .items-table td.amount {
            text-align: right;
            font-weight: bold;
        }

        .items-table td.center {
            text-align: center;
        }

        /* Total */
        .total-section {
            text-align: right;
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .total-amount {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }

        /* Estado del recibo */
        .status-section {
            margin: 15px 0;
            padding: 8px;
            text-align: center;
            border-radius: 4px;
        }

        .status-pagado {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .status-pendiente {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }

        .status-vencido {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }

        .footer-address {
            margin-bottom: 5px;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .container {
                max-width: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header - Datos de la Empresa -->
        <div class="header">
            @if ($empresaConfig['logo'])
                <div class="header-logo">
                    <img src="{{ $empresaConfig['logo'] }}" alt="Logo">
                </div>
                <div class="header-content">
                    <div class="company-name">
                        {{ $empresaConfig['razon_social'] ?? config('app.name', 'Sistema de Facturación') }}
                    </div>
                    @if ($empresaConfig['telefono'] ?? $empresaConfig['email'])
                        <div class="company-details">
                            @if ($empresaConfig['telefono'])
                                Tel: {{ $empresaConfig['telefono'] }}
                            @endif
                            @if ($empresaConfig['telefono'] && $empresaConfig['email'])
                                |
                            @endif
                            @if ($empresaConfig['email'])
                                Email: {{ $empresaConfig['email'] }}
                            @endif
                        </div>
                    @endif
                    <div class="document-title">RECIBO DE PAGO</div>
                    <div class="recibo-number">Nº {{ $recibo->numero_recibo_formateado }}</div>
                </div>
            @endif
        </div>

        <!-- Información Principal -->
        <div class="info-section">
            <div class="info-left">
                <!-- Datos del Cliente -->
                <div class="info-box">
                    <h3>DATOS DEL CLIENTE</h3>
                    <div class="info-item">
                        <span class="info-label">Nombre:</span>
                        {{ $recibo->cliente_nombre ?? 'N/A' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">RUC/DNI:</span>
                        {{ $recibo->cliente_documento ?? 'N/A' }}
                    </div>
                    @if ($recibo->cliente_direccion)
                        <div class="info-item">
                            <span class="info-label">Dirección:</span>
                            {{ $recibo->cliente_direccion }}
                        </div>
                    @endif
                    @if ($recibo->cliente_telefono)
                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            {{ $recibo->cliente_telefono }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="info-right">
                <!-- Datos del Recibo -->
                <div class="info-box">
                    <h3>DATOS DEL RECIBO</h3>
                    <div class="info-item">
                        <span class="info-label">Fecha Creación:</span>
                        {{ $recibo->fecha_emision ? $recibo->fecha_emision->format('d/m/Y') : ($recibo->created_at ? $recibo->created_at->format('d/m/Y') : 'N/A') }}
                    </div>
                    @if ($recibo->fecha_vencimiento)
                        <div class="info-item">
                            <span class="info-label">Vencimiento:</span>
                            {{ $recibo->fecha_vencimiento->format('d/m/Y') }}
                        </div>
                    @endif
                    @if ($recibo->estado_recibo === 'pagado' && $recibo->fecha_pago)
                        <div class="info-item">
                            <span class="info-label">Fecha Pago:</span>
                            {{ $recibo->fecha_pago->format('d/m/Y') }}
                        </div>
                    @endif
                    @if ($recibo->metodo_pago)
                        <div class="info-item">
                            <span class="info-label">Método Pago:</span>
                            {{ $recibo->metodo_pago }}
                        </div>
                    @endif
                    @if ($recibo->numero_referencia)
                        <div class="info-item">
                            <span class="info-label">Referencia:</span>
                            {{ $recibo->numero_referencia }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Estado del Recibo -->
        <div class="status-section status-{{ $recibo->estado_recibo }}">
            <strong>ESTADO: {{ strtoupper($recibo->estado_label) }}</strong>
        </div>

        <!-- Tabla de Items -->
        <div class="items-section">
            <div class="items-title">DETALLE DE SERVICIOS</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%">Descripción</th>
                        <th style="width: 15%">Placa</th>
                        <th style="width: 20%">Período</th>
                        <th style="width: 15%">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($recibo->detalles && $recibo->detalles->count() > 0)
                        @foreach ($recibo->detalles as $detalle)
                            <tr>
                                <td>
                                    {{ $detalle->concepto }}
                                    @if ($detalle->es_prorrateo)
                                        <br><small style="color: #666; font-style: italic;">Prorrateo:
                                            {{ $detalle->dias_calculados }} días</small>
                                    @endif
                                </td>
                                <td class="center">{{ $detalle->placa ?? 'N/A' }}</td>
                                <td class="center">
                                    @if ($detalle->fecha_inicio_periodo && $detalle->fecha_fin_periodo)
                                        {{ $detalle->fecha_inicio_periodo->format('d/m/Y') }}<br>
                                        al {{ $detalle->fecha_fin_periodo->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="amount">{{ $recibo->moneda ?? 'S/' }}
                                    {{ number_format($detalle->monto_calculado, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                {{ $recibo->servicio_nombre ?? 'Servicio GPS' }}
                                @if ($recibo->periodo_facturacion)
                                    <br><small style="color: #666;">{{ $recibo->periodo_facturacion }}</small>
                                @endif
                            </td>
                            <td class="center">{{ $recibo->placa ?? 'N/A' }}</td>
                            <td class="center">
                                @if ($recibo->fecha_inicio_periodo && $recibo->fecha_fin_periodo)
                                    {{ $recibo->fecha_inicio_periodo->format('d/m/Y') }}<br>
                                    al {{ $recibo->fecha_fin_periodo->format('d/m/Y') }}
                                @else
                                    Mes actual
                                @endif
                            </td>
                            <td class="amount">{{ $recibo->moneda ?? 'S/' }}
                                {{ number_format($recibo->monto_recibo, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="total-section">
            <div style="font-size: 14px; margin-bottom: 5px;">
                <strong>TOTAL A PAGAR: {{ $recibo->moneda ?? 'S/' }}
                    {{ number_format($recibo->monto_recibo, 2) }}</strong>
            </div>
            @if ($recibo->estado_recibo === 'pagado' && $recibo->monto_pagado)
                <div style="font-size: 12px; color: #059669;">
                    Monto Pagado: {{ $recibo->moneda ?? 'S/' }} {{ number_format($recibo->monto_pagado, 2) }}
                </div>
            @endif
        </div>

        <!-- Observaciones -->
        @if ($recibo->observaciones)
            <div style="margin: 15px 0; padding: 8px; background-color: #f8f9fa; border: 1px solid #ddd;">
                <strong style="font-size: 11px;">Observaciones:</strong><br>
                <span style="font-size: 10px;">{{ $recibo->observaciones }}</span>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            @if ($empresaConfig['direccion'])
                <div class="footer-address">{{ $empresaConfig['direccion'] }}</div>
            @endif
            <div>Este documento fue generado electrónicamente el {{ now()->format('d/m/Y H:i:s') }}</div>
            <div style="margin-top: 5px; font-size: 8px;">
                Sistema de Gestión GPS - Recibo {{ $recibo->numero_recibo }}
            </div>
        </div>
    </div>
</body>

</html>
