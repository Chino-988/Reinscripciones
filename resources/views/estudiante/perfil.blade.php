<x-app-layout>
<x-slot name="header">Validación de datos</x-slot>

<div class="space-y-6">
  <div class="card">
    <form method="POST" action="{{ route('est.perfil.update') }}" class="space-y-6" x-data="{ ninguna: {{ ($cond->ninguna ?? false) ? 'true' : 'false' }} }">
      @csrf

      {{-- GENERALES --}}
      <div>
        <div class="card-title">Datos generales</div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="label">Matrícula</label>
            <input type="text" class="input bg-gray-100 dark:bg-gray-900/60" value="{{ $estu->matricula ?? '' }}" readonly>
          </div>
          <div>
            <label class="label">Nombre</label>
            <input type="text" name="nombre" class="input" value="{{ old('nombre', $estu->nombre ?? '') }}" required>
          </div>
          <div>
            <label class="label">Apellido paterno</label>
            <input type="text" name="apellido_paterno" class="input" value="{{ old('apellido_paterno', $estu->apellido_paterno ?? '') }}" required>
          </div>
          <div>
            <label class="label">Apellido materno</label>
            <input type="text" name="apellido_materno" class="input" value="{{ old('apellido_materno', $estu->apellido_materno ?? '') }}">
          </div>
        </div>
      </div>

      {{-- MEJORA DE SERVICIOS --}}
      <div>
        <div class="card-title">Información para mejora de servicios</div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="label">¿Pertenece a alguna etnia indígena o pueblo afromexicano?</label>
            @php $pe = old('pertenencia_etnica', $estu->pertenencia_etnica ?? ''); @endphp
            <select name="pertenencia_etnica" class="select" required>
              <option value="">-- Selecciona --</option>
              <option value="NINGUNA"      {{ $pe==='NINGUNA' ? 'selected' : '' }}>Ninguna</option>
              <option value="INDIGENA"     {{ $pe==='INDIGENA' ? 'selected' : '' }}>Indígena</option>
              <option value="AFROMEXICANO" {{ $pe==='AFROMEXICANO' ? 'selected' : '' }}>Afromexicano</option>
              <option value="OTRA"         {{ $pe==='OTRA' ? 'selected' : '' }}>Otra</option>
            </select>
          </div>
          <div>
            <label class="label">¿Habla alguna lengua indígena o dialecto? (opcional)</label>
            <input type="text" name="lengua_indigena" class="input" value="{{ old('lengua_indigena', $estu->lengua_indigena ?? '') }}" placeholder="Ej. Náhuatl / Ninguna">
          </div>
        </div>
      </div>

      {{-- SOCIOECONÓMICO --}}
      <div>
        <div class="card-title">Datos socioeconómicos</div>
        <div class="grid sm:grid-cols-3 gap-4">
          <div><label class="label">Ingreso mensual del hogar</label><input type="number" step="0.01" min="0" name="ingreso_mensual" class="input" value="{{ old('ingreso_mensual', $estu->ingreso_mensual ?? '') }}"></div>
          <div><label class="label">Dependientes económicos</label><input type="number" min="0" name="dependientes" class="input" value="{{ old('dependientes', $estu->dependientes ?? '') }}"></div>
          <div>
            <label class="label">Estado civil</label>
            @php $ec = old('estado_civil', $estu->estado_civil ?? ''); @endphp
            <select name="estado_civil" class="select">
              <option value="">-- Selecciona --</option>
              <option value="SOLTERO" {{ $ec==='SOLTERO' ? 'selected':'' }}>Soltero(a)</option>
              <option value="CASADO" {{ $ec==='CASADO' ? 'selected':'' }}>Casado(a)</option>
              <option value="UNION_LIBRE" {{ $ec==='UNION_LIBRE' ? 'selected':'' }}>Unión libre</option>
            </select>
          </div>
        </div>
      </div>

      {{-- COMUNICACIÓN (JSONB) --}}
      @php
        $correoInst = old('correo_institucional', $estu->correo_institucional);
        $correoPers = old('correo_personal', $estu->correo_personal);
        $telMovil   = old('tel_movil', $estu->tel_movil);
        $telPadre   = old('tel_padre', $estu->tel_padre);
        $telMadre   = old('tel_madre', $estu->tel_madre);
        $direccion  = old('direccion', $estu->direccion_principal);
      @endphp
      <div>
        <div class="card-title">Formas de comunicación</div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div><label class="label">Correo institucional</label><input type="email" name="correo_institucional" class="input" value="{{ $correoInst }}" placeholder="3522110216@uth.edu.mx"></div>
          <div><label class="label">Correo personal</label><input type="email" name="correo_personal" class="input" value="{{ $correoPers }}" placeholder="tucorreo@dominio.com"></div>
          <div><label class="label">Teléfono móvil (estudiante)</label><input type="text" name="tel_movil" class="input" value="{{ $telMovil }}" placeholder="10 dígitos"></div>
          <div><label class="label">Teléfono (papá)</label><input type="text" name="tel_padre" class="input" value="{{ $telPadre }}" placeholder="Opcional"></div>
          <div><label class="label">Teléfono (mamá)</label><input type="text" name="tel_madre" class="input" value="{{ $telMadre }}" placeholder="Opcional"></div>
          <div class="lg:col-span-3"><label class="label">Domicilio</label><textarea name="direccion" rows="2" class="textarea" placeholder="Calle, número, colonia, municipio, CP">{{ $direccion }}</textarea></div>
        </div>
      </div>

      {{-- CONDICIONES --}}
      <div>
        <div class="card-title">Condiciones y padecimientos</div>
        <label class="inline-flex items-center mb-3"><input type="checkbox" name="ninguna" value="1" @change="ninguna = $event.target.checked" {{ old('ninguna',$cond->ninguna ?? false) ? 'checked' : '' }}><span class="ml-2">Ninguna</span></label>
        @php function chk($n,$c){ $v=old($n,$c->$n ?? false); return $v?'checked':''; } @endphp
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <label class="inline-flex items-start gap-2 p-2 rounded border"><input type="checkbox" name="estar_de_pie_mareo" {{ chk('estar_de_pie_mareo',$cond) }} :disabled="ninguna"><span class="text-sm">Estar de pie: mareo/sensación de desmayo/dolor</span></label>
          <label class="inline-flex items-start gap-2 p-2 rounded border"><input type="checkbox" name="caminar_sin_ayuda" {{ chk('caminar_sin_ayuda',$cond) }} :disabled="ninguna"><span class="text-sm">Caminar sin ayuda me es difícil</span></label>
          <label class="inline-flex items-start gap-2 p-2 rounded border"><input type="checkbox" name="desplazar_problemas" {{ chk('desplazar_problemas',$cond) }} :disabled="ninguna"><span class="text-sm">Trasladarme casa-escuela presenta problemas</span></label>
          {{-- ... (incluye todas las demás que ya tenías) --}}
          <label class="inline-flex items-start gap-2 p-2 rounded border"><input type="checkbox" name="enfermedad_nacimiento" {{ chk('enfermedad_nacimiento',$cond) }} :disabled="ninguna"><span class="text-sm">Padecimiento desde el nacimiento</span></label>
          <label class="inline-flex items-start gap-2 p-2 rounded border"><input type="checkbox" name="enfermedad_cronica" {{ chk('enfermedad_cronica',$cond) }} :disabled="ninguna"><span class="text-sm">Padecimiento crónico</span></label>
        </div>
      </div>

      {{-- DECLARACIÓN --}}
      <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
        <label class="inline-flex items-start gap-2">
          <input type="hidden" name="acepta_declaracion" value="0">
          <input type="checkbox" name="acepta_declaracion" value="1" {{ old('acepta_declaracion', $estu->acepta_declaracion ?? false) ? 'checked' : '' }}>
          <span class="text-sm"><b>BAJO PROTESTA DE DECIR VERDAD</b> manifiesto que la información proporcionada es verídica…</span>
        </label>
      </div>

      <div class="flex justify-end gap-3"><a href="{{ route('dash.estudiante') }}" class="btn btn-secondary">Cancelar</a><button class="btn btn-primary">Validar y guardar</button></div>
    </form>
  </div>
</div>
</x-app-layout>
