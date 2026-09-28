<?php
namespace App\Models;

use CodeIgniter\Model;

class EquipamientoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'electrodependiente_equipamiento';
    protected $primaryKey       = 'equipamiento_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'electrodependiente_id',
        'equipamiento',
        'marca',
        'serie',
        'modelo',
        'fecha_entrega',
        'tiempo_uso',
        'medico_tratante',
        'titular_servicio',
        'nro_servicio',
        'fecha_ingreso_recs',
        'ultima_evaluacion',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ─────────────────────────────────────────────────────────────────────
    //  Devuelve todos los equipos activos de un paciente
    // ─────────────────────────────────────────────────────────────────────
    public function porPaciente(int $electrodependienteId): array
    {
        return $this->where('electrodependiente_id', $electrodependienteId)
                    ->where('estado', 'activo')
                    ->orderBy('equipamiento_id', 'ASC')
                    ->findAll();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  SINCRONIZAR — con historial completo
    //  - ALTA   → equipo nuevo
    //  - MODIF  → equipo existente con datos diferentes
    //  - BAJA   → equipo que ya no viene en el POST
    // ─────────────────────────────────────────────────────────────────────
    public function sincronizar(int $electrodependienteId, array $equiposPost): void
    {
        helper('auth');
        $userId = function_exists('user') && user() ? user()->id : null;
        $ahora  = date('Y-m-d H:i:s');
        $histModel = model('EquipamientoHistorialModel');

        // IDs que vienen del formulario (equipos ya existentes en BD)
        $idsPost = array_filter(array_column($equiposPost, 'equipamiento_id'));

        // ── BAJA: equipos activos que ya no vienen en el POST ────────────
        $query = $this->where('electrodependiente_id', $electrodependienteId)
                      ->where('estado', 'activo');

        if (!empty($idsPost)) {
            $query->whereNotIn('equipamiento_id', $idsPost);
        }

        $equiposADarDeBaja = $query->findAll();

        foreach ($equiposADarDeBaja as $eq) {
            $this->update((int)$eq['equipamiento_id'], ['estado' => 'baja']);

            $histModel->insert([
                'equipamiento_id'       => $eq['equipamiento_id'],
                'electrodependiente_id' => $electrodependienteId,
                'accion'                => 'BAJA',
                'datos_anteriores'      => json_encode($eq),
                'datos_nuevos'          => null,
                'modificado_por'        => $userId,
                'created_at'            => $ahora,
            ]);
        }

        // ── ALTA / MODIFICACIÓN ───────────────────────────────────────────
        foreach ($equiposPost as $eq) {
            if (empty(trim($eq['equipamiento'] ?? ''))) continue;

            $data = [
                'electrodependiente_id' => $electrodependienteId,
                'equipamiento'          => strtoupper(trim($eq['equipamiento'])),
                'marca'                 => $eq['marca']               ?? null,
                'serie'                 => $eq['serie']               ?? null,
                'modelo'                => $eq['modelo']              ?? null,
                'fecha_entrega'         => $eq['fecha_entrega']       ?: null,
                'tiempo_uso'            => $eq['tiempo_uso']          ?? null,
                'medico_tratante'       => $eq['medico_tratante']     ?? null,
                'titular_servicio'      => $eq['titular_servicio']    ?? null,
                'nro_servicio'          => $eq['nro_servicio']        ?? null,
                'fecha_ingreso_recs'    => $eq['fecha_ingreso_recs']  ?: null,
                'ultima_evaluacion'     => $eq['ultima_evaluacion']   ?: null,
                'estado'                => 'activo',
            ];

            if (!empty($eq['equipamiento_id'])) {
                // ── MODIFICACIÓN ──────────────────────────────────────────
                $anterior = $this->find((int)$eq['equipamiento_id']);
                $this->update((int)$eq['equipamiento_id'], $data);

                // Solo graba historial si algo realmente cambió
                if ($anterior && $this->hayDiferencias($anterior, $data)) {
                    $histModel->insert([
                        'equipamiento_id'       => (int)$eq['equipamiento_id'],
                        'electrodependiente_id' => $electrodependienteId,
                        'accion'                => 'MODIFICACION',
                        'datos_anteriores'      => json_encode($anterior),
                        'datos_nuevos'          => json_encode($data),
                        'modificado_por'        => $userId,
                        'created_at'            => $ahora,
                    ]);
                }
            } else {
                // ── ALTA ──────────────────────────────────────────────────
                $nuevoId = $this->insert($data, true);

                $histModel->insert([
                    'equipamiento_id'       => $nuevoId,
                    'electrodependiente_id' => $electrodependienteId,
                    'accion'                => 'ALTA',
                    'datos_anteriores'      => null,
                    'datos_nuevos'          => json_encode($data),
                    'modificado_por'        => $userId,
                    'created_at'            => $ahora,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Compara dos arrays ignorando campos de control
    // ─────────────────────────────────────────────────────────────────────
    private function hayDiferencias(array $anterior, array $nuevo): bool
    {
        $ignorar = ['created_at', 'updated_at', 'electrodependiente_id'];

        foreach ($nuevo as $campo => $valorNuevo) {
            if (in_array($campo, $ignorar, true)) continue;
            if (!array_key_exists($campo, $anterior))  continue;

            $va = $anterior[$campo] === null ? '' : (string)$anterior[$campo];
            $vn = $valorNuevo       === null ? '' : (string)$valorNuevo;

            if ($va !== $vn) return true;
        }
        return false;
    }
}