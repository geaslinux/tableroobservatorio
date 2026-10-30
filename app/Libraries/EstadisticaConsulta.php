<?php
namespace App\Libraries;

use CodeIgniter\Exceptions\PageNotFoundException;

/** Lecturas estadísticas: todas parten del mismo alcance y filtros. */
final class EstadisticaConsulta
{
    private $db;
    private array $ids;
    public array $filtros = [];
    public const FILTROS = ['efector', 'formulario', 'especialidad', 'medico', 'hospital', 'servicio', 'desde', 'hasta', 'con_procedimiento'];
    /** Médicos con más atenciones que se dibujan como líneas en el gráfico temporal. */
    public const MEDICOS_EN_SERIE = 10;
    // Médicos que viajan al gráfico temporal: los primeros MEDICOS_EN_SERIE se dibujan y el resto se agrega desde un desplegable
    public const MEDICOS_MAXIMO_SERIE = 60;

    public function __construct(int $usuario, array $entrada)
    {
        $this->db = db_connect('default');
        $rows = $this->db->table('grupo_formulario_consulta g')->distinct()->select('g.formulario_id')
            ->join('auth_groups_users u', 'u.group_id = g.group_id')->where('u.user_id', $usuario)->get()->getResultArray();
        $this->ids = array_map('intval', array_column($rows, 'formulario_id'));
        foreach (self::FILTROS as $key) {
            $value = $entrada[$key] ?? '';
            if (!is_scalar($value)) throw new \InvalidArgumentException('Filtro inválido.');
            $this->filtros[$key] = trim((string) $value);
        }
        foreach (['efector', 'formulario', 'especialidad', 'medico'] as $key) {
            if ($this->filtros[$key] !== '' && !ctype_digit($this->filtros[$key])) throw new \InvalidArgumentException('Selección inválida.');
        }
        foreach (['desde', 'hasta'] as $key) {
            $v = $this->filtros[$key];
            $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $v);
            if ($v !== '' && (!$d || $d->format('Y-m-d') !== $v)) throw new \InvalidArgumentException('Fecha inválida.');
        }
        if ($this->filtros['desde'] && $this->filtros['hasta'] && $this->filtros['desde'] > $this->filtros['hasta']) throw new \InvalidArgumentException('La fecha desde debe ser anterior o igual a la fecha hasta.');
        if (!in_array($this->filtros['con_procedimiento'], ['', '1'], true)) throw new \InvalidArgumentException('Filtro de procedimiento inválido.');
        if ($this->filtros['formulario'] !== '' && !in_array((int) $this->filtros['formulario'], $this->ids, true)) throw PageNotFoundException::forPageNotFound();
        // No permitir combinaciones manipuladas fuera de los catálogos autorizados.
        foreach (['efector' => 'efector_id', 'formulario' => 'formulario_id', 'especialidad' => 'especialidad_id'] as $key => $col) {
            if ($this->filtros[$key] !== '' && !$this->formularios()->where('f.' . $col, $this->filtros[$key])->countAllResults()) throw PageNotFoundException::forPageNotFound();
        }
        if ($this->filtros['formulario'] !== '') {
            $f = $this->formularios()->where('f.formulario_id', $this->filtros['formulario'])->get()->getRowArray();
            if (($this->filtros['efector'] && $f['efector_id'] != $this->filtros['efector']) || ($this->filtros['especialidad'] && $f['especialidad_id'] != $this->filtros['especialidad'])) throw new \InvalidArgumentException('El formulario no corresponde al efector o especialidad seleccionados.');
        }
    }

    private function alcance($q, string $col): void
    {
        if ($this->ids) $q->whereIn($col, $this->ids);
        else $q->where('1 = 0', null, false);
    }

    private function formularios()
    {
        $q = $this->db->table('formulario_consulta f');
        $this->alcance($q, 'f.formulario_id');
        return $q;
    }

    private function base(bool $filtrada = true)
    {
        $q = $this->db->table('atencion_consulta a')->join('formulario_consulta f', 'f.formulario_id = a.formulario_id')
            ->join('efector e', 'e.efector_id = f.efector_id')->join('especialidad_consulta ec', 'ec.especialidad_id = f.especialidad_id')
            ->join('localidad l', 'l.localidad_id = a.localidad_id', 'left')->where('a.deleted_at', null);
        $this->alcance($q, 'a.formulario_id');
        if (!$filtrada) return $q;
        foreach (['efector' => 'f.efector_id', 'formulario' => 'f.formulario_id', 'especialidad' => 'f.especialidad_id', 'medico' => 'a.profesional_id', 'hospital' => 'a.hospital_origen_texto', 'servicio' => 'a.servicio_origen_texto', 'desde' => 'a.fecha >=', 'hasta' => 'a.fecha <='] as $key => $col) {
            if ($this->filtros[$key] !== '') $q->where($col, $this->filtros[$key]);
        }
        if ($this->filtros['con_procedimiento']) $q->where("NULLIF(TRIM(a.procedimiento), '') IS NOT NULL", null, false);
        return $q;
    }

    public function catalogos(): array
    {
        $formularios = $this->formularios()->select('f.formulario_id, f.efector_id, f.especialidad_id, f.titulo, e.nombre AS efector, ec.nombre AS especialidad')
            ->join('efector e', 'e.efector_id = f.efector_id')->join('especialidad_consulta ec', 'ec.especialidad_id = f.especialidad_id')->orderBy('e.nombre')->orderBy('f.titulo')->get()->getResultArray();
        $historicos = $this->base(false);
        if ($this->filtros['efector']) $historicos->where('f.efector_id', $this->filtros['efector']);
        if ($this->filtros['formulario']) $historicos->where('f.formulario_id', $this->filtros['formulario']);
        $salida = ['formularios' => $formularios];
        // Rango de fechas con atenciones: limita los años que ofrece el calendario
        $salida['fechas'] = $this->base(false)->select('MIN(a.fecha) AS min, MAX(a.fecha) AS max')->get()->getRowArray();
        // Médicos: una opción por profesional con el nombre del catálogo (las atenciones guardan cada variante tipeada)
        $q = clone $historicos;
        $salida['medico'] = $q->select('a.profesional_id AS id, p.nombre_completo AS nombre')->join('profesional_consulta p', 'p.profesional_id = a.profesional_id')
            ->groupBy(['a.profesional_id', 'p.nombre_completo'])->orderBy('p.nombre_completo')->get()->getResultArray();
        foreach (['hospital' => 'a.hospital_origen_texto', 'servicio' => 'a.servicio_origen_texto'] as $key => $col) {
            $q = clone $historicos;
            $salida[$key] = $q->distinct()->select("$col AS id, $col AS nombre")->orderBy($col)->get()->getResultArray();
        }
        return $salida;
    }

    public function resumen(): array
    {
        $kpi = $this->base()->select("COUNT(*) AS atenciones, COUNT(DISTINCT a.paciente_id) AS pacientes, COUNT(DISTINCT a.profesional_id) AS medicos, COALESCE(SUM(NULLIF(TRIM(a.procedimiento), '') IS NOT NULL),0) AS procedimientos, MIN(a.fecha) AS desde, MAX(a.fecha) AS hasta", false)->get()->getRowArray();
        $serie = $this->base()->select("DATE_FORMAT(a.fecha, '%Y-%m') AS nombre, COUNT(*) AS cantidad", false)->groupBy('nombre')->orderBy('nombre')->get()->getResultArray();
        if ($serie) {
            $porMes = array_column($serie, 'cantidad', 'nombre');
            $mes = new \DateTimeImmutable($serie[0]['nombre'] . '-01');
            $ultimo = new \DateTimeImmutable($serie[count($serie) - 1]['nombre'] . '-01');
            $serie = [];
            while ($mes <= $ultimo) {
                $nombre = $mes->format('Y-m');
                $serie[] = ['nombre' => $nombre, 'cantidad' => (int) ($porMes[$nombre] ?? 0)];
                $mes = $mes->modify('+1 month');
            }
        }
        $medicos = $this->base()->join('profesional_consulta p', 'p.profesional_id = a.profesional_id')->select("a.profesional_id AS id, MAX(p.nombre_completo) AS nombre, COUNT(*) AS cantidad, COUNT(DISTINCT a.paciente_id) AS pacientes, SUM(NULLIF(TRIM(a.procedimiento), '') IS NOT NULL) AS procedimientos", false)->groupBy('a.profesional_id')->orderBy('cantidad', 'DESC')->orderBy('id')->get()->getResultArray();
        $serie_medicos = $this->serieMedicos($medicos, $kpi);
        return compact('kpi', 'serie', 'medicos', 'serie_medicos');
    }

    /**
     * Atenciones por día de los médicos con más actividad, con 0 en los días sin atenciones.
     * ['dias' => ['2024-01-01', ...], 'series' => [['id' => 5, 'nombre' => ..., 'datos' => [3, 0, ...]], ...]]
     */
    private function serieMedicos(array $medicos, array $kpi): array
    {
        $salida = ['dias' => [], 'series' => []];
        $top = array_slice($medicos, 0, self::MEDICOS_EN_SERIE);
        $salida = ['dias' => [], 'series' => [], 'visibles' => self::MEDICOS_EN_SERIE];
        $top = array_slice($medicos, 0, self::MEDICOS_MAXIMO_SERIE);
        if (!$top || !$kpi['desde']) return $salida;
        $filas = $this->base()->select('a.fecha AS dia, a.profesional_id AS id, COUNT(*) AS cantidad', false)
            ->whereIn('a.profesional_id', array_column($top, 'id'))->groupBy(['a.fecha', 'a.profesional_id'])->get()->getResultArray();
        $porMedico = [];
        foreach ($filas as $f) $porMedico[$f['id']][$f['dia']] = (int) $f['cantidad'];
        $dia = new \DateTimeImmutable($kpi['desde']);
        $ultimo = new \DateTimeImmutable($kpi['hasta']);
        while ($dia <= $ultimo) {
            $salida['dias'][] = $dia->format('Y-m-d');
            $dia = $dia->modify('+1 day');
        }
        foreach ($top as $m) {
            $salida['series'][] = ['id' => (int) $m['id'], 'nombre' => $m['nombre'], 'datos' => array_map(static fn($d) => $porMedico[$m['id']][$d] ?? 0, $salida['dias'])];
        }
        return $salida;
    }

    public function distribuciones(): array
    {
        $resultado = [];
        foreach (['Efector de atención' => 'e.nombre', 'Servicio/formulario de atención' => 'f.titulo', 'Especialidad' => 'ec.nombre', 'Hospital de origen' => 'a.hospital_origen_texto', 'Servicio de origen' => 'a.servicio_origen_texto', 'Diagnósticos registrados' => 'a.diagnostico', 'Procedimientos registrados' => 'a.procedimiento', 'Localidades' => "COALESCE(NULLIF(a.localidad_otra,''),l.nombre)", 'Día de la semana (1=lunes, 7=domingo)' => 'WEEKDAY(a.fecha)+1', 'Franja horaria (hora de inicio)' => "CONCAT(LPAD(HOUR(a.hora),2,'0'), ':00–', LPAD(HOUR(a.hora),2,'0'), ':59')"] as $titulo => $col) {
            // El formulario define cuándo un campo no aplica; vacío visible es sin informar.
            $campo = match ($titulo) { 'Hospital de origen' => 'hospital_origen', 'Servicio de origen' => 'servicio_origen', 'Procedimientos registrados' => 'procedimiento', 'Localidades' => 'localidad', default => null };
            $vacio = "'Sin informar'";
            if ($campo) $vacio = "CASE WHEN EXISTS (SELECT 1 FROM formulario_campo_consulta fc WHERE fc.formulario_id=f.formulario_id AND fc.campo='$campo' AND fc.modo='oculto') THEN 'No aplica' ELSE 'Sin informar' END";
            $expr = "COALESCE(NULLIF(TRIM($col),''), $vacio)";
            $resultado[$titulo] = $this->base()->select("$expr AS nombre, COUNT(*) AS cantidad", false)->groupBy('nombre')->orderBy('cantidad', 'DESC')->orderBy('nombre')->get()->getResultArray();
        }
        return $resultado;
    }

    public function detalle(int $pagina = 1, ?int $id = null): array
    {
        $q = $this->base();
        if ($id !== null) $q->where('a.atencion_id', $id);
        // Lista explícita: nunca enviar nombres o DNI de pacientes ni datos_originales.
        return $q->select('a.atencion_id, a.fecha, a.hora, f.titulo AS formulario, a.profesional_nombre_completo AS profesional, a.diagnostico, a.procedimiento, a.hospital_origen_texto AS hospital, a.servicio_origen_texto AS servicio, a.indicaciones, a.observaciones')
            ->orderBy('a.fecha', 'DESC')->orderBy('a.hora', 'DESC')->orderBy('a.atencion_id', 'DESC')->limit($id === null ? 25 : 1, $id === null ? ($pagina - 1) * 25 : 0)->get()->getResultArray();
    }
}