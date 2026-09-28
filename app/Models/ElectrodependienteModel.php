<?php
namespace App\Models;

use CodeIgniter\Model;

class ElectrodependienteModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'electrodependiente';
    protected $primaryKey       = 'electrodependiente_id';
    protected $useAutoIncrement = true;
 protected $returnType = \App\Entities\Electrodependiente::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'paciente', 'dni', 'fecha_nacimiento', 'edad', 'tipo',
        'contacto', 'domicilio', 'coordenadas',
        'localidad_id', 'efector_id', 'obra_social_id',
        'cud', 'diagnostico_id', 'dx_complementario',
        'observacion', 'factor_riesgo', 'seguimiento', 'estado',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ─────────────────────────────────────────────────────────────────────
    //  Etiquetas legibles para el historial
    // ─────────────────────────────────────────────────────────────────────
    private array $etiquetas = [
        'paciente'          => 'Nombre del paciente',
        'dni'               => 'DNI',
        'fecha_nacimiento'  => 'Fecha de nacimiento',
        'edad'              => 'Edad',
        'tipo'              => 'Tipo (Adulto/Niño)',
        'contacto'          => 'Contacto / Teléfono',
        'domicilio'         => 'Domicilio',
        'coordenadas'       => 'Coordenadas geográficas',
        'localidad_id'      => 'Localidad',
        'efector_id'        => 'Hospital de referencia',
        'obra_social_id'    => 'Obra social',
        'cud'               => 'CUD',
        'diagnostico_id'    => 'Diagnóstico',
        'dx_complementario' => 'Diagnóstico complementario',
        'observacion'       => 'Observación',
        'factor_riesgo'     => 'Factor de riesgo',
        'seguimiento'       => 'Seguimiento',
        'estado'            => 'Estado',
    ];

    // ─────────────────────────────────────────────────────────────────────
    //  UPDATE con historial automático
    //  Reemplaza el update() base para interceptar cambios.
    // ─────────────────────────────────────────────────────────────────────
   public function update($id = null, $data = null): bool
{
    // asArray() evita el problema de castear una Entity a array
    $anterior = $this->asArray()->find($id);

    $ok = parent::update($id, $data);

    if ($ok && $anterior) {
        $this->registrarCambios((int)$id, $anterior, (array)$data);
    }

    return $ok;
}

    // ─────────────────────────────────────────────────────────────────────
    //  Compara campo a campo y graba en electrodependiente_historial
    // ─────────────────────────────────────────────────────────────────────
   private function registrarCambios(int $id, array $anterior, array $nuevo): void
{
    try {
        helper('auth');
        $userId = function_exists('user') && user() ? user()->id : null;
    } catch (\Throwable $e) {
        $userId = null;
    }

    $ahora          = date('Y-m-d H:i:s');
    $historialModel = model('ElectrodependienteHistorialModel');

    // Mapa de campos FK → modelo y columna nombre
    $relaciones = [
        'localidad_id'   => ['model' => 'LocalidadModel',   'pk' => 'localidad_id',   'label' => 'nombre'],
        'efector_id'     => ['model' => 'EfectorModel',      'pk' => 'efector_id',     'label' => 'nombre'],
        'obra_social_id' => ['model' => 'ObraSocialModel',   'pk' => 'obra_social_id', 'label' => 'nombre'],
        'diagnostico_id' => ['model' => 'DiagnosticoModel',  'pk' => 'diagnostico_id', 'label' => 'nombre'],
    ];

    foreach ($nuevo as $campo => $valorNuevo) {
        if (!array_key_exists($campo, $anterior)) continue;

        $valorAnterior = $anterior[$campo];

        $va = $valorAnterior === null ? '' : (string)$valorAnterior;
        $vn = $valorNuevo    === null ? '' : (string)$valorNuevo;

        if ($va === $vn) continue;

        // Si es un campo FK, resolver el nombre legible
        if (isset($relaciones[$campo])) {
            $rel = $relaciones[$campo];

            if ($valorAnterior) {
                $regAnt = model($rel['model'])->find($valorAnterior);
                $valorAnterior = $regAnt
                    ? (is_array($regAnt) ? $regAnt[$rel['label']] : $regAnt->{$rel['label']})
                    : $valorAnterior;
            }

            if ($valorNuevo) {
                $regNvo = model($rel['model'])->find($valorNuevo);
                $valorNuevo = $regNvo
                    ? (is_array($regNvo) ? $regNvo[$rel['label']] : $regNvo->{$rel['label']})
                    : $valorNuevo;
            }
        }

        $historialModel->insert([
            'electrodependiente_id' => $id,
            'campo_modificado'      => $this->etiquetas[$campo] ?? $campo,
            'valor_anterior'        => $valorAnterior ?: null,
            'valor_nuevo'           => $valorNuevo    ?: null,
            'modificado_por'        => $userId,
            'created_at'            => $ahora,
        ]);
    }
}

    // ─────────────────────────────────────────────────────────────────────
    //  Relaciones para el listado / export (dejá igual al tuyo actual)
    // ─────────────────────────────────────────────────────────────────────
    public function conRelaciones(): static
    {
        return $this
            ->select('electrodependiente.*, l.nombre AS localidad_nombre, e.nombre AS efector_nombre, e.region AS efector_region, os.nombre AS obra_social_nombre, d.nombre AS diagnostico_nombre')
            ->join('localidad l',     'l.localidad_id = electrodependiente.localidad_id',     'left')
            ->join('efector e',       'e.efector_id   = electrodependiente.efector_id',       'left')
            ->join('obra_social os',  'os.obra_social_id = electrodependiente.obra_social_id','left')
            ->join('diagnostico d',   'd.diagnostico_id  = electrodependiente.diagnostico_id','left');
    }
}