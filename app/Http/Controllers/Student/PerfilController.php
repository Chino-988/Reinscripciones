<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\{Estudiante, CondicionFuncional, Reinscripcion, Notificacion};

class PerfilController extends Controller
{
    public function edit()
    {
        $estu = Estudiante::where('user_id', auth()->id())->firstOrFail();
        $rein = Reinscripcion::where('estudiante_id',$estu->id)->latest()->first();

        // BLOQUEO: si ya validó (validado_en o validado_datos), no puede editar hasta que Admin rechace
        $perfilEnviado = (bool)($estu->validado_en) || (bool) data_get($estu, 'validado_datos', false);
        if ($perfilEnviado && (!$rein || $rein->estatus_final !== 'RECHAZADA')) {
            return redirect()->route('dash.estudiante')
                ->with('info','Tus datos están en revisión. Si el Admin rechaza, podrás editarlos de nuevo.');
        }

        $cond = $estu->condicionesFuncionales;
        return view('estudiante.perfil', compact('estu','cond'));
    }

    public function update(Request $request)
    {
        $estu = Estudiante::where('user_id', auth()->id())->firstOrFail();
        $rein = Reinscripcion::where('estudiante_id',$estu->id)->latest()->first();

        // BLOQUEO server-side
        $perfilEnviado = (bool)($estu->validado_en) || (bool) data_get($estu,'validado_datos',false);
        if ($perfilEnviado && (!$rein || $rein->estatus_final !== 'RECHAZADA')) {
            return redirect()->route('dash.estudiante')->with('err','No puedes editar mientras está en revisión.');
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'pertenencia_etnica' => 'required|string|max:50',
            'lengua_indigena' => 'nullable|string|max:100',
            'ingreso_mensual' => 'nullable|numeric|min:0',
            'dependientes' => 'nullable|integer|min:0',
            'estado_civil' => 'nullable|string|max:50',
            'acepta_declaracion' => 'accepted',

            // Comunicación
            'correo_institucional'=> 'nullable|email|max:150',
            'correo_personal'     => 'nullable|email|max:150',
            'tel_movil'           => 'nullable|string|max:30',
            'tel_padre'           => 'nullable|string|max:30',
            'tel_madre'           => 'nullable|string|max:30',
            'direccion'           => 'nullable|string|max:1000',
        ]);

        // Datos generales
        $estu->fill([
            'nombre' => $data['nombre'],
            'apellido_paterno' => $data['apellido_paterno'],
            'apellido_materno' => $data['apellido_materno'] ?? null,
            'pertenencia_etnica' => $data['pertenencia_etnica'],
            'lengua_indigena' => $data['lengua_indigena'] ?? null,
            'ingreso_mensual' => $data['ingreso_mensual'] ?? null,
            'dependientes' => $data['dependientes'] ?? null,
            'estado_civil' => $data['estado_civil'] ?? null,
            'acepta_declaracion' => $request->boolean('acepta_declaracion'),
        ]);

        // === Comunicación en formato LISTA DE OBJETOS (para que se vea en Admin) ===
        $tels = [];
        if (!empty($data['tel_movil'])) $tels[] = ['tipo'=>'Teléfono móvil','dato'=>$data['tel_movil'],'comentario'=>'Estudiante'];
        if (!empty($data['tel_padre'])) $tels[] = ['tipo'=>'Teléfono móvil','dato'=>$data['tel_padre'],'comentario'=>'Padre'];
        if (!empty($data['tel_madre'])) $tels[] = ['tipo'=>'Teléfono móvil','dato'=>$data['tel_madre'],'comentario'=>'Madre'];

        $correos = [];
        if (!empty($data['correo_institucional'])) $correos[] = ['tipo'=>'Correo institucional','dato'=>$data['correo_institucional'],'comentario'=>'Institucional'];
        if (!empty($data['correo_personal']))      $correos[] = ['tipo'=>'Correo personal','dato'=>$data['correo_personal'],'comentario'=>'Personal'];

        $domicilios = [];
        if (!empty($data['direccion'])) $domicilios[] = ['direccion'=>$data['direccion'],'comentario'=>'Principal'];

        $estu->telefonos = $tels;
        $estu->correos   = $correos;
        $estu->domicilios= $domicilios;

        // Marca envío para revisión (bloqueo)
        $estu->validado_en = now();
        if (Schema::hasColumn('estudiantes','validado_datos')) {
            $estu->validado_datos = true;
        }
        $estu->save();

        // Condiciones funcionales (checkboxes)
        $fields = [
            'ninguna','estar_de_pie_mareo','caminar_sin_ayuda','desplazar_problemas',
            'manipular_no_dibuja_casa','hablar_no_solicita_ayuda','postura_pierde_fuerza',
            'otras_acciones_no_deporte','oido_izq_oigo_poco','oido_der_oigo_poco','oido_izq_no_oigo','oido_der_no_oigo',
            'ojo_izq_casi_no_ve','ojo_der_casi_no_ve','ojo_izq_no_ve','ojo_der_no_ve','tarda_comprender_lectura',
            'no_entiende_lectura','escritura_no_entendible','dificultad_lect_escr_mapa','dificultad_matematicas_basicas',
            'olvida_datos_personales','dificultad_interactuar','dificultad_establecer_platica','prefiere_solo',
            'prefiere_trabajar_solo','escucha_voces','ve_personas_objetos','cambios_estado_animo','enfermedad_nacimiento','enfermedad_cronica',
        ];
        $cond = $estu->condicionesFuncionales()->firstOrNew([]);
        $ninguna = $request->boolean('ninguna');
        foreach ($fields as $f) { $cond->$f = $f==='ninguna' ? $ninguna : ($ninguna ? false : $request->has($f)); }
        $cond->estudiante_id = $estu->id;
        $cond->save();

        // Bandera de sesión para que el Dashboard bloquee de inmediato
        session(['perfil_enviado' => true]);

        // Notificar a ADMIN
        Notificacion::toRole('ADMIN', 'Estudiante validó datos', "Matrícula {$estu->matricula}: datos enviados para revisión.");

        return redirect()->route('dash.estudiante')->with('ok', 'Datos enviados. Quedan en revisión.');
    }
}
