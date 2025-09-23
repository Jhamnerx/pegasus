<div>
    <x-modal-card :title="$isEditing ? 'Editar Recibo' : 'Nuevo Recibo'" wire:model="isOpen" max-width="2xl">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Número de Recibo -->
            <x-input label="Número de Recibo *" placeholder="Ingrese el número de recibo" wire:model="numero_recibo" />

            <!-- Cobro (Opcional) -->
            <x-select label="Cobro" placeholder="Seleccione un cobro" wire:model.live="cobro_id" :async-data="route('select.cobros')"
                option-label="label" option-value="value" />

            <!-- Cliente -->
            <div class="col-span-1 sm:col-span-2">
                <x-select label="Cliente *" placeholder="Seleccione un cliente" wire:model.live="cliente_id"
                    :async-data="route('select.clientes')" option-label="label" option-value="value" />
            </div>

            <!-- Servicio -->
            <div class="col-span-1 sm:col-span-2">
                <x-select label="Servicio *" placeholder="Seleccione un servicio" wire:model.live="servicio_id"
                    :async-data="route('select.servicios')" option-label="label" option-value="value" />
            </div>

            <!-- Información del Cliente (Solo lectura) -->
            @if (!empty($dataCliente))
                <div class="col-span-1 sm:col-span-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Información del Cliente</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><strong>Nombre:</strong> {{ $dataCliente['nombre_cliente'] ?? 'N/A' }}</div>
                        <div><strong>Documento:</strong> {{ $dataCliente['ruc_dni'] ?? 'N/A' }}</div>
                        @if (!empty($dataCliente['telefono']))
                            <div><strong>Teléfono:</strong> {{ $dataCliente['telefono'] }}</div>
                        @endif
                        @if (!empty($dataCliente['correo_electronico']))
                            <div><strong>Email:</strong> {{ $dataCliente['correo_electronico'] }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Información del Servicio (Solo lectura) -->
            @if (!empty($dataServicio))
                <div class="col-span-1 sm:col-span-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Información del Servicio</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><strong>Servicio:</strong> {{ $dataServicio['nombre_servicio'] ?? 'N/A' }}</div>
                        <div><strong>Precio Base:</strong> S/ {{ number_format($dataServicio['precio_base'] ?? 0, 2) }}
                        </div>
                        @if (!empty($dataServicio['descripcion']))
                            <div class="col-span-2"><strong>Descripción:</strong> {{ $dataServicio['descripcion'] }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Información del Cobro (Solo lectura) -->
            @if (!empty($dataCobro))
                <div class="col-span-1 sm:col-span-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Información del Cobro</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><strong>Cantidad de Placas:</strong> {{ $dataCobro['cantidad_placas'] ?? 1 }}</div>
                        <div><strong>Monto Base:</strong> S/ {{ number_format($dataCobro['monto_base'] ?? 0, 2) }}</div>
                        <div><strong>Monto Total:</strong> S/ {{ number_format($dataCobro['monto_total'] ?? 0, 2) }}
                        </div>
                        @if (!empty($dataCobro['periodo_facturacion']))
                            <div><strong>Período:</strong> {{ $dataCobro['periodo_facturacion'] }}</div>
                        @endif
                        @if (!empty($dataCobro['fecha_inicio_periodo']) && !empty($dataCobro['fecha_fin_periodo']))
                            <div class="col-span-2"><strong>Fechas:</strong> {{ $dataCobro['fecha_inicio_periodo'] }}
                                al {{ $dataCobro['fecha_fin_periodo'] }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Monto del Recibo -->
            <x-input label="Monto del Recibo *" placeholder="0.00" wire:model="monto_recibo" type="number"
                step="0.01" prefix="S/" />

            <!-- Estado -->
            <x-select label="Estado del Recibo *" placeholder="Seleccione el estado" wire:model.live="estado_recibo">
                <x-select.option label="Pendiente" value="pendiente" />
                <x-select.option label="Pagado" value="pagado" />
                <x-select.option label="Anulado" value="anulado" />
            </x-select>

            <!-- Fecha de Emisión -->
            <x-datetime-picker label="Fecha de Emisión *" placeholder="Seleccione la fecha" wire:model="fecha_emision"
                without-time />

            <!-- Fecha de Vencimiento -->
            <x-datetime-picker label="Fecha de Vencimiento *" placeholder="Seleccione la fecha"
                wire:model="fecha_vencimiento" without-time />

            <!-- Información de Pago (Solo si está pagado) -->
            @if ($estado_recibo === 'pagado')
                <!-- Fecha de Pago -->
                <x-datetime-picker label="Fecha de Pago" placeholder="Seleccione la fecha" wire:model="fecha_pago"
                    without-time />

                <!-- Método de Pago -->
                <x-select label="Método de Pago" placeholder="Seleccione método" wire:model="metodo_pago">
                    <x-select.option label="Efectivo" value="efectivo" />
                    <x-select.option label="Transferencia" value="transferencia" />
                    <x-select.option label="Depósito" value="deposito" />
                    <x-select.option label="Cheque" value="cheque" />
                </x-select>

                <!-- Número de Referencia -->
                <x-input label="Número de Referencia" placeholder="Referencia del pago"
                    wire:model="numero_referencia" />

                <!-- Monto Pagado -->
                <x-input label="Monto Pagado" placeholder="0.00" wire:model="monto_pagado" type="number" step="0.01"
                    prefix="S/" />
            @endif

            <!-- Observaciones -->
            <div class="col-span-1 sm:col-span-2">
                <x-textarea label="Observaciones" placeholder="Notas adicionales sobre el recibo..."
                    wire:model="observaciones" rows="3" />
            </div>
        </div> <x-slot name="footer" class="flex justify-between gap-x-4">
            @if ($isEditing)
                <x-button flat negative label="Eliminar"
                    wire:click="$dispatch('openDeleteModal', { reciboId: {{ $recibo->id ?? 0 }} })" />
            @else
                <div></div>
            @endif

            <div class="flex gap-x-4">
                <x-button flat label="Cancelar" wire:click="closeModal" />

                <x-button primary label="{{ $isEditing ? 'Actualizar' : 'Guardar' }}" wire:click="save" />
            </div>
        </x-slot>
    </x-modal-card>

</div>
