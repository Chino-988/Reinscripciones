<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiantes';

    protected $fillable = [
        'user_id','matricula','nombre','apellido_paterno','apellido_materno','foto_path',
        'pertenencia_etnica','lengua_indigena',
        'ingreso_mensual','dependientes','estado_civil',
        'telefonos','correos','domicilios',
        'tutor_trato','tutor_nombre','tutor_apellido_paterno','tutor_apellido_materno',
        'acepta_declaracion','validado_en','validado_datos',
    ];

    protected $casts = [
        'correos'            => 'array',
        'telefonos'          => 'array',
        'domicilios'         => 'array',
        'acepta_declaracion' => 'boolean',
        'validado_en'        => 'datetime',
        'validado_datos'     => 'boolean',
    ];

    // Relaciones
    public function user()                  { return $this->belongsTo(User::class); }
    public function condicion()             { return $this->hasOne(CondicionFuncional::class, 'estudiante_id'); }
    public function condicionesFuncionales(){ return $this->hasOne(CondicionFuncional::class, 'estudiante_id'); }

    // ¡IMPORTANTE!: traer siempre la reinscripción MÁS RECIENTE
    public function reinscripcion()         { return $this->hasOne(Reinscripcion::class, 'estudiante_id')->latestOfMany(); }

    public function pagos()                 { return $this->hasMany(Pago::class, 'estudiante_id'); }

    // Helpers (opcionales)
    public function getCorreoInstitucionalAttribute() { return $this->correos['institucional'] ?? null; }
    public function getCorreoPersonalAttribute()      { return $this->correos['personal'] ?? null; }
}
