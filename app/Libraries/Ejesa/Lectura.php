<?php

namespace App\Libraries\Ejesa;

use CodeIgniter\Database\BaseConnection;
use LogicException;

/**
 * Lectura de los datos de EJESA que genera sistemabasec (base compartida `rep`).
 *
 * SOLO LECTURA: esta clase únicamente arma SELECT. No tiene ningún método que escriba y no hay
 * que agregarle ninguno: los cortes, el historial, el protocolo y la auditoría son de sistemabasec.
 * Usa el grupo de conexión 'ejesa' (app/Config/Database.php), pensado para un usuario MySQL de
 * solo SELECT. Se comprueba con:  php spark ejesa:solo-lectura
 *
 * SQL portable (la base va a migrar a PostgreSQL): solo Query Builder, sin funciones propias de
 * MySQL, sin comillas invertidas. Las restas de fechas y redondeos se hacen en Calculo.
 *
 * Nunca se lee ejesa_consulta_persona (tiene nombres de pacientes consultados por EJESA).
 */
class Lectura
{
    public const EMPRESA = 'EJESA';

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect('ejesa');
    }

    // ─── Cortes ──────────────────────────────────────────────────────────

    /** Filtro base de todo el visor: EJESA, no dados de baja, no borradores. */
    private function cortesBase()
    {
        return $this->db->table('ejesa_corte c')
            ->select('c.corte_id, c.referencia, c.motivo, c.inicio_at, c.fin_estimado_at, c.estado, c.version,
                      c.localidad_id, l.nombre AS localidad, c.zona_descripcion, c.publicado_at, c.cancelado_at,
                      c.motivo_cancelacion, c.created_at, c.updated_at')
            ->join('localidad l', 'l.localidad_id = c.localidad_id', 'left')
            ->where('c.empresa', self::EMPRESA)
            ->where('c.baja_at', null)
            ->where('c.estado <>', 'BORRADOR');
    }

    /** Cortes cuyo inicio (hora local) cae en [desde, hasta). */
    public function cortes(string $desde, string $hasta, ?int $localidadId = null): array
    {
        $q = $this->cortesBase()
            ->where('c.inicio_at >=', $desde)
            ->where('c.inicio_at <', $hasta);
        if ($localidadId) {
            $q->where('c.localidad_id', $localidadId);
        }

        return $q->orderBy('c.inicio_at', 'ASC')->orderBy('c.corte_id', 'ASC')->get()->getResultArray();
    }

    /** Publicados que todavía no terminaron (fin estimado >= ahora). */
    public function proximos(string $ahora, ?int $localidadId = null): array
    {
        $q = $this->cortesBase()
            ->whereIn('c.estado', ['PROGRAMADO', 'EN_CURSO'])
            ->where('c.fin_estimado_at >=', $ahora);
        if ($localidadId) {
            $q->where('c.localidad_id', $localidadId);
        }

        return $q->orderBy('c.inicio_at', 'ASC')->get()->getResultArray();
    }

    /** Publicados cuyo fin estimado ya pasó y nadie finalizó. */
    public function vencidos(string $ahora): array
    {
        return $this->cortesBase()
            ->whereIn('c.estado', ['PROGRAMADO', 'EN_CURSO'])
            ->where('c.fin_estimado_at <', $ahora)
            ->orderBy('c.fin_estimado_at', 'ASC')
            ->get()->getResultArray();
    }

    public function corte(int $id): ?array
    {
        return $this->cortesBase()
            ->select('c.geometria')
            ->where('c.corte_id', $id)
            ->get()->getRowArray() ?: null;
    }

    // ─── Historial, protocolo y afectados ────────────────────────────────

    /** Historial de los cortes indicados, agrupado por corte_id. */
    public function historialDe(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $filas = $this->db->table('ejesa_corte_historial h')
            ->select('h.historial_id, h.corte_id, h.version, h.accion, h.cambios, h.registrado_at, u.username')
            ->join('users u', 'u.id = h.actor_id', 'left')
            ->whereIn('h.corte_id', $ids)
            ->orderBy('h.historial_id', 'ASC')
            ->get()->getResultArray();

        $porCorte = [];
        foreach ($filas as $f) {
            $porCorte[(int) $f['corte_id']][] = $f;
        }

        return $porCorte;
    }

    /** Historial de cambios en un rango UTC (pestaña Auditoría). */
    public function historial(string $desdeUtc, string $hastaUtc, ?string $accion = null, int $limite = 500): array
    {
        $q = $this->db->table('ejesa_corte_historial h')
            ->select('h.historial_id, h.corte_id, h.version, h.accion, h.cambios, h.registrado_at, u.username,
                      c.referencia, c.inicio_at, l.nombre AS localidad')
            ->join('ejesa_corte c', 'c.corte_id = h.corte_id')
            ->join('localidad l', 'l.localidad_id = c.localidad_id', 'left')
            ->join('users u', 'u.id = h.actor_id', 'left')
            ->where('c.empresa', self::EMPRESA)
            ->where('h.registrado_at >=', $desdeUtc)
            ->where('h.registrado_at <', $hastaUtc);
        if ($accion) {
            $q->where('h.accion', $accion);
        }

        return $q->orderBy('h.historial_id', 'DESC')->limit($limite)->get()->getResultArray();
    }

    /** Afectados VIGENTES por corte y riesgo: [corte_id => [[riesgo_snapshot, cantidad], ...]] */
    public function afectadosPorRiesgo(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $filas = $this->db->table('ejesa_corte_afectado a')
            ->select('a.corte_id, a.riesgo_snapshot, COUNT(*) AS cantidad')
            ->where('a.estado_vinculo', 'VIGENTE')
            ->whereIn('a.corte_id', $ids)
            ->groupBy(['a.corte_id', 'a.riesgo_snapshot'])
            ->get()->getResultArray();

        $porCorte = [];
        foreach ($filas as $f) {
            $porCorte[(int) $f['corte_id']][] = $f;
        }

        return $porCorte;
    }

    /** Vínculos VIGENTES corte ↔ persona (solo ids y riesgo), para contar personas distintas. */
    public function vinculos(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        return $this->db->table('ejesa_corte_afectado a')
            ->select('a.corte_id, a.electrodependiente_id, a.riesgo_snapshot')
            ->where('a.estado_vinculo', 'VIGENTE')
            ->whereIn('a.corte_id', $ids)
            ->get()->getResultArray();
    }

    /** Último protocolo (incidencia CORTE_PROGRAMADO) de cada corte: [corte_id => fila] */
    public function protocolos(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $filas = $this->db->table('contingencia_incidencia i')
            ->select('i.incidencia_id, i.corte_id, i.estado, i.ambito, i.created_at, i.cerrado_at')
            ->where('i.tipo', 'CORTE_PROGRAMADO')
            ->whereIn('i.corte_id', $ids)
            ->orderBy('i.incidencia_id', 'ASC')
            ->get()->getResultArray();

        $porCorte = [];
        foreach ($filas as $f) {
            $porCorte[(int) $f['corte_id']] = $f; // queda la última
        }

        return $porCorte;
    }

    /**
     * Quiénes son los afectados de un corte (los guardados al activar el protocolo).
     * Datos para identificar a la persona y su dependencia eléctrica; sin domicilio, coordenadas,
     * teléfono ni diagnóstico (están en la ficha, con su propio permiso).
     */
    public function afectadosDetalle(int $corteId): array
    {
        $personas = $this->db->table('ejesa_corte_afectado a')
            ->select('a.electrodependiente_id, a.riesgo_snapshot, a.nro_servicio_snapshot, a.revision_corte, a.calculado_at,
                      p.paciente, p.edad, p.tipo, p.factor_riesgo, p.estado, p.estado_vital, p.nro_servicio,
                      p.servicio_verificado, l.nombre AS localidad, e.nombre AS efector')
            ->join('electrodependiente p', 'p.electrodependiente_id = a.electrodependiente_id', 'left')
            ->join('localidad l', 'l.localidad_id = p.localidad_id', 'left')
            ->join('efector e', 'e.efector_id = p.efector_id', 'left')
            ->where('a.corte_id', $corteId)
            ->where('a.estado_vinculo', 'VIGENTE')
            ->orderBy('a.riesgo_snapshot', 'ASC')
            ->orderBy('p.paciente', 'ASC')
            ->get()->getResultArray();

        return $this->conEquipos($personas);
    }

    /** Datos de identificación de un grupo de personas (para "quiénes fueron afectados en el período"). */
    public function personas(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $filas = $this->db->table('electrodependiente p')
            ->select('p.electrodependiente_id, p.paciente, p.edad, p.tipo, p.factor_riesgo, p.estado, p.estado_vital,
                      l.nombre AS localidad')
            ->join('localidad l', 'l.localidad_id = p.localidad_id', 'left')
            ->whereIn('p.electrodependiente_id', $ids)
            ->get()->getResultArray();

        $porId = [];
        foreach ($this->conEquipos($filas) as $f) {
            $porId[(int) $f['electrodependiente_id']] = $f;
        }

        return $porId;
    }

    /** Agrega a cada persona sus equipos activos (nombre y tiempo de uso). */
    private function conEquipos(array $personas): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', array_column($personas, 'electrodependiente_id')))));
        if (!$ids) {
            return $personas;
        }

        $equipos = [];
        $filas   = $this->db->table('electrodependiente_equipamiento')
            ->select('electrodependiente_id, equipamiento, tiempo_uso')
            ->whereIn('electrodependiente_id', $ids)
            ->where('estado', 'activo')
            ->orderBy('equipamiento_id', 'ASC')
            ->get()->getResultArray();
        foreach ($filas as $f) {
            $equipos[(int) $f['electrodependiente_id']][] = $f;
        }

        foreach ($personas as &$p) {
            $p['equipos'] = $equipos[(int) $p['electrodependiente_id']] ?? [];
        }
        unset($p);

        return $personas;
    }

    // ─── Auditoría de consultas de EJESA ─────────────────────────────────

    private function consultasBase(string $desdeUtc, string $hastaUtc, array $filtros)
    {
        $q = $this->db->table('ejesa_consulta q')
            ->where('q.consultado_at >=', $desdeUtc)
            ->where('q.consultado_at <', $hastaUtc);

        if (!empty($filtros['usuario_id'])) $q->where('q.usuario_id', (int) $filtros['usuario_id']);
        if (!empty($filtros['modalidad']))  $q->where('q.modalidad', $filtros['modalidad']);
        if (!empty($filtros['resultado']))  $q->where('q.resultado', $filtros['resultado']);
        if (!empty($filtros['corte_id']))   $q->where('q.corte_id', (int) $filtros['corte_id']);

        return $q;
    }

    /** Todas las consultas del rango, con lo justo para las estadísticas (sin número de servicio). */
    public function consultasParaEstadistica(string $desdeUtc, string $hastaUtc, array $filtros = []): array
    {
        return $this->consultasBase($desdeUtc, $hastaUtc, $filtros)
            ->select('q.consulta_id, q.usuario_id, q.usuario_nombre_snapshot, q.perfil_snapshot, q.consultado_at,
                      q.modalidad, q.resultado, q.cantidad_personas, q.corte_id')
            ->orderBy('q.consultado_at', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Una página de consultas, de la más nueva a la más vieja, paginada por cursor
     * (consultado_at, consulta_id) en lugar de OFFSET. Devuelve ['filas' => [...], 'siguiente' => ?cursor].
     * El número de servicio sale de acá y se enmascara en la vista con Calculo::servicio().
     */
    public function consultas(string $desdeUtc, string $hastaUtc, array $filtros = [], ?array $cursor = null, int $limite = 50): array
    {
        $q = $this->consultasBase($desdeUtc, $hastaUtc, $filtros)
            ->select('q.consulta_id, q.usuario_id, q.usuario_nombre_snapshot, q.perfil_snapshot, q.consultado_at,
                      q.nro_servicio_consultado, q.modalidad, q.resultado, q.cantidad_personas, q.corte_id,
                      q.filtro_riesgo, q.ambito_snapshot, q.ip, c.referencia AS corte_referencia');
        $q->join('ejesa_corte c', 'c.corte_id = q.corte_id', 'left');

        if ($cursor !== null) {
            // (consultado_at, consulta_id) < (cursor) sin comparar tuplas: portable
            $q->groupStart()
                ->where('q.consultado_at <', $cursor['at'])
                ->orGroupStart()
                    ->where('q.consultado_at', $cursor['at'])
                    ->where('q.consulta_id <', (int) $cursor['id'])
                ->groupEnd()
            ->groupEnd();
        }

        $filas = $q->orderBy('q.consultado_at', 'DESC')->orderBy('q.consulta_id', 'DESC')
            ->limit($limite + 1)->get()->getResultArray();

        $siguiente = null;
        if (count($filas) > $limite) {
            array_pop($filas);
            $ultima    = end($filas);
            $siguiente = ['at' => (string) $ultima['consultado_at'], 'id' => (int) $ultima['consulta_id']];
        }

        return ['filas' => $filas, 'siguiente' => $siguiente];
    }

    /** Usuarios que consultaron alguna vez (para el filtro). */
    public function usuariosQueConsultaron(): array
    {
        return $this->db->table('ejesa_consulta q')
            ->select('q.usuario_id, MAX(q.usuario_nombre_snapshot) AS nombre')
            ->groupBy('q.usuario_id')
            ->orderBy('nombre', 'ASC')
            ->get()->getResultArray();
    }

    /** Localidades con algún corte publicado (para el filtro). */
    public function localidadesConCortes(): array
    {
        return $this->db->table('ejesa_corte c')
            ->select('l.localidad_id, l.nombre')
            ->join('localidad l', 'l.localidad_id = c.localidad_id')
            ->where('c.empresa', self::EMPRESA)
            ->where('c.baja_at', null)
            ->where('c.estado <>', 'BORRADOR')
            ->groupBy(['l.localidad_id', 'l.nombre'])
            ->orderBy('l.nombre', 'ASC')
            ->get()->getResultArray();
    }

    // ─── Control de solo lectura ─────────────────────────────────────────

    /**
     * Intenta un UPDATE que no cambia ninguna fila (WHERE sin coincidencias).
     * Con un usuario de solo lectura la base lo rechaza: eso es lo correcto.
     * Devuelve true si la conexión PUEDE escribir (configuración insegura).
     */
    public function puedeEscribir(): bool
    {
        if (ENVIRONMENT === 'production' && !is_cli()) {
            throw new LogicException('El control de escritura solo se corre desde la consola (php spark ejesa:solo-lectura).');
        }

        // WHERE imposible (corte_id = 0 y = -1): aunque la base lo acepte, no cambia ninguna fila
        try {
            $ok = $this->db->table('ejesa_corte')->where('corte_id', 0)->where('corte_id', -1)->update(['version' => 0]);
        } catch (\Throwable $e) {
            $ok = false; // la base lo rechazó: sin permiso de escritura
        }

        return (bool) $ok;
    }

    public function usuarioConexion(): string
    {
        return (string) $this->db->username . '@' . (string) $this->db->hostname . '/' . (string) $this->db->database;
    }
}
