<x-app-layout>
<x-slot name="header">Panel del Estudiante</x-slot>

<div class="space-y-6">
  @include('components.toast')

  {{-- Mensajes rápidos --}}
  @if (session('ok'))
    <div class="p-3 bg-emerald-600 text-white rounded">{{ session('ok') }}</div>
  @endif
  @if (session('err'))
    <div class="p-3 bg-rose-600 text-white rounded">{{ session('err') }}</div>
  @endif
  @if (session('info'))
    <div class="p-3 bg-blue-600 text-white rounded">{{ session('info') }}</div>
  @endif

  {{-- Normaliza variables y recalcula flags de bloqueo (doble seguro) --}}
  @php
    $estu  = $estu  ?? null;
    $pago  = $pago  ?? null;
    $rein  = $rein  ?? null;
    $notis = $notis ?? collect();

    // ¿Perfil enviado?
    $perfilEnviadoCalc = $estu ? (
      (bool)($estu->validado_en ?? false)
      || (bool) data_get($estu, 'validado_datos', false)
      || (bool) session('perfil_enviado', false)
    ) : false;

    // ¿Rechazo de admin?
    $rechazoAdminCalc = ($rein && ($rein->estatus_final ?? null) === 'RECHAZADA');

    // Bloqueo perfil
    if (!isset($bloqPerfil)) {
      $bloqPerfil = $perfilEnviadoCalc && (!$rechazoAdminCalc);
    }

    // Permitir pago sólo si perfil fue enviado y no hay pago activo
    if (!isset($permitePago)) {
      $permitePago = $perfilEnviadoCalc && ( !$pago || (($pago->estatus_caja ?? null) === 'RECHAZADO') );
    }
    if (!isset($bloqPagoBtn)) {
      $bloqPagoBtn = ! $permitePago;
    }

    // Para la barra de progreso
    $tienePago = (bool)($pago ?? false);
    $step1   = $perfilEnviadoCalc;
    $step2   = $tienePago;
    $step3   = $tienePago && (($pago->estatus_caja ?? null) === 'VALIDADO');
    $rechCaja= $tienePago && (($pago->estatus_caja ?? null) === 'RECHAZADO');
    $step4   = (($rein->estatus_final ?? null) === 'APROBADA');
  @endphp

  {{-- Barra de progreso (4 pasos) --}}
  <div class="card">
    <div class="card-title">Progreso de reinscripción</div>
    <ol class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
      <li class="p-3 rounded-lg border {{ $step1?'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20':'border-gray-300' }}">
        <div class="font-semibold">1) Validación de datos</div>
        <div class="text-sm mt-1">{{ $step1 ? 'Completada' : 'Pendiente' }}</div>
      </li>
      <li class="p-3 rounded-lg border {{ $step2?'border-blue-500 bg-blue-50 dark:bg-blue-900/20':'border-gray-300' }}">
        <div class="font-semibold">2) Envío de pago</div>
        <div class="text-sm mt-1">
          @if(!$tienePago)
            Pendiente
          @else
            Enviado ({{ optional($pago->created_at)->format('d/m H:i') }})
          @endif
        </div>
      </li>
      <li class="p-3 rounded-lg border {{ $step3?'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20':($rechCaja?'border-rose-500 bg-rose-50 dark:bg-rose-900/20':'border-gray-300') }}">
        <div class="font-semibold">3) Validación de Caja</div>
        <div class="text-sm mt-1">
          @if($step3)
            Validado
          @elseif($rechCaja)
            Rechazado ({{ $pago->observaciones_caja ?? 'Sin motivo' }})
          @else
            En revisión
          @endif
        </div>
      </li>
      <li class="p-3 rounded-lg border {{ $step4?'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20':'border-gray-300' }}">
        <div class="font-semibold">4) Aprobación y constancia</div>
        <div class="text-sm mt-1">
          @if($step4)
            Aprobada
          @elseif(($rein->estatus_final ?? null) === 'RECHAZADA')
            Rechazada
          @else
            Pendiente
          @endif
        </div>
      </li>
    </ol>
  </div>

  {{-- Tarjetas de acciones (bloqueo por estado) --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    {{-- Validación de datos --}}
    <div class="card">
      <div class="card-title">Validación de datos</div>
      <p class="text-sm mt-1">Completa tus datos personales y socioeconómicos.</p>
      <div class="mt-3">
        @if($bloqPerfil)
          <button class="btn btn-secondary opacity-60 cursor-not-allowed" disabled>En revisión</button>
          <p class="text-xs text-gray-500 mt-1">Si el Admin rechaza, podrás editarlos.</p>
        @else
          <a href="{{ route('est.perfil.edit') }}" class="btn btn-primary">Editar / Validar</a>
        @endif
      </div>
    </div>

    {{-- Registro de pago --}}
    <div class="card">
      <div class="card-title">Registro de pago</div>
      <p class="text-sm mt-1">Sube tu referencia y comprobante.</p>
      <div class="mt-3">
        @if($bloqPagoBtn)
          <button class="btn btn-secondary opacity-60 cursor-not-allowed" disabled>
            @if(!$perfilEnviadoCalc)
              Primero valida tus datos
            @elseif($pago && ($pago->estatus_caja ?? null) !== 'RECHAZADO')
              {{ ($pago && ($pago->estatus_caja ?? null)==='VALIDADO') ? 'Validado por Caja' : 'En revisión' }}
            @else
              No disponible
            @endif
          </button>
          @if($rechCaja)
            <p class="text-xs text-rose-600 mt-1">Rechazado: {{ $pago->observaciones_caja }}</p>
          @endif
        @else
          <a href="{{ route('est.pago.create') }}" class="btn btn-primary">Cargar pago</a>
        @endif
      </div>
    </div>

    {{-- Constancia --}}
    <div class="card">
      <div class="card-title">Constancia</div>
      <p class="text-sm mt-1">Descarga tu constancia cuando sea aprobada.</p>
      @if((($rein->estatus_final ?? null) === 'APROBADA') && !empty($rein->constancia_pdf_path))
        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ route('est.constancia.descargar') }}" class="btn btn-primary">Descargar constancia</a>
          @if(!empty($rein->token_verificacion))
            <a target="_blank" href="{{ route('verificacion.mostrar',$rein->token_verificacion) }}" class="btn btn-secondary">Verificación</a>
          @endif
        </div>
      @else
        <div class="mt-3">
          <button class="btn btn-secondary opacity-60 cursor-not-allowed" disabled>No disponible</button>
        </div>
      @endif
    </div>
  </div>

  {{-- Notificaciones --}}
  <div class="card">
    <div class="card-title">Notificaciones</div>
    <ul class="mt-2 list-disc ml-6">
      @forelse($notis as $n)
        <li class="text-sm">
          <b>{{ $n->titulo }}</b> — {{ $n->mensaje }}
          <span class="text-xs text-gray-500">({{ optional($n->created_at)->format('d/m H:i') }})</span>
        </li>
      @empty
        <li class="text-sm text-gray-500">Sin notificaciones.</li>
      @endforelse
    </ul>
  </div>
</div>
</x-app-layout>
