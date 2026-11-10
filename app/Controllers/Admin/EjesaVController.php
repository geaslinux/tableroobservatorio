<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Ejesa\Calculo;
use App\Libraries\Ejesa\Lectura;
use DateTime;
use DateTimeZone;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

/**
 * Panel EJESA dentro de Electrodependientes (solo lectura).
 * Sub-pestañas: Resumen · Próximos cortes · Historial de cortes · Impacto · Auditoría.
 * Reglas y cálculos: docs/ejesa_visor/SKILL.md. Los datos los genera sistemabasec.
 */
class EjesaVController extends BaseController
{
    public const TABS = ['resumen', 'proximos', 'historial', 'impacto', 'auditoria'];

    private const MODALIDADES = [
        'SERVICIO_EXACTO' => 'Búsqueda por Nº de servicio',
        'CORTE'           => 'Afectados de un corte',
        'RIESGO_AMBITO'   => 'Listado operativo',
    ];

    private const RESULTADOS = [
        'CON_RESULTADOS' => 'Con resultados',
        'SIN_RESULTADOS' => 'Sin resultados',
        'DENEGADA'       => 'Denegada',
        'ERROR'          => 'Error',
    ];

    private const ACCIONES = [
        'CREAR' => 'Creación', 'EDITAR' => 'Edición', 'PUBLICAR' => 'Publicación', 'REPROGRAMAR' => 'Reprogramación',
        'CANCELAR' => 'Cancelación', 'FINALIZAR' => 'Finalización', 'ACTIVAR_PROTOCOLO' => 'Protocolo activado',
        'BAJA_BORRADOR' => 'Baja de borrador',
    ];

    // ─── Panel ───────────────────────────────────────────────────────────
    public function index()
    {
        $tab = in_array($this->request->getGet('tab'), self::TABS, true) ? $this->request->getGet('tab') : 'resumen';

        try {
            $lectura = new Lectura();
            $f       = $this->filtros();
            $datos   = $this->armarPeriodo($lectura, $f);
        } catch (Throwable $e) {
            log_message('error', 'Panel EJESA: ' . $e->getMessage());

            return view('admin/ejesa_views', [
                'tab' => $tab, 'error' => 'No se pudieron leer los datos de EJESA. Intente de nuevo en unos minutos.',
                'f' => $this->filtros(), 'ahora' => Calculo::ahora(),
            ]);
        }

        $ahora = $datos['ahora'];

        // Próximos (independiente del período) y vencidos sin finalizar (siempre visibles)
        $proximos = $this->enriquecer($lectura, $lectura->proximos($ahora, $f['localidad']), $ahora);
        $vencidos = $this->enriquecer($lectura, $lectura->vencidos($ahora), $ahora);
        $enCurso  = array_values(array_filter($proximos, static fn ($c) => $c['estado_visible'] === 'EN_CURSO'));
        $futuros  = array_values(array_filter($proximos, static fn ($c) => $c['estado_visible'] === 'PROGRAMADO'));

        // Auditoría: consultas y cambios del período (columnas UTC)
        $desdeUtc = Calculo::localAUtc($f['desde'] . ' 00:00:00');
        $hastaUtc = Calculo::localAUtc($f['hasta_excl'] . ' 00:00:00');
        $fa       = [
            'usuario_id' => $f['usuario'], 'modalidad' => $f['modalidad'], 'resultado' => $f['resultado'],
        ];
        $consultasStats = $lectura->consultasParaEstadistica($desdeUtc, $hastaUtc, $fa);
        $pagina         = $lectura->consultas($desdeUtc, $hastaUtc, $fa, $f['cursor']);
        $cambios        = $lectura->historial($desdeUtc, $hastaUtc, $f['accion'] ?: null);

        return view('admin/ejesa_views', $datos + [
            'tab'           => $tab,
            'f'             => $f,
            'proximos'      => $futuros,
            'enCurso'       => $enCurso,
            'vencidos'      => $vencidos,
            'auditoria'     => Calculo::auditoria($consultasStats),
            'consultas'     => $pagina['filas'],
            'siguiente'     => $pagina['siguiente'] ? base64_encode($pagina['siguiente']['at'] . '|' . $pagina['siguiente']['id']) : null,
            'cambios'       => $cambios,
            'localidades'   => $lectura->localidadesConCortes(),
            'usuarios'      => $lectura->usuariosQueConsultaron(),
            'modalidades'   => self::MODALIDADES,
            'resultados'    => self::RESULTADOS,
            'acciones'      => self::ACCIONES,
            'puedeVerFicha' => has_permission('LISTADO PERSONA'),
        ]);
    }

    // ─── Detalle de un corte ─────────────────────────────────────────────
    public function corte($id)
    {
        $lectura = new Lectura();
        $fila    = $lectura->corte((int) $id);

        if (!$fila) {
            return redirect()->to(route_to('ejesa_views'))
                ->with('msg', ['type' => 'danger', 'body' => 'Corte no encontrado (o es un borrador / dado de baja).']);
        }

        $ahora     = Calculo::ahora();
        $historial = $lectura->historialDe([(int) $id])[(int) $id] ?? [];
        $corte     = $this->enriquecer($lectura, [$fila], $ahora)[0];
        $afectados = $corte['protocolo'] ? $lectura->afectadosDetalle((int) $id) : [];

        $desdeUtc  = '2000-01-01 00:00:00';
        $hastaUtc  = Calculo::localAUtc($ahora);
        $consultas = $lectura->consultas($desdeUtc, $hastaUtc, ['corte_id' => (int) $id], null, 100)['filas'];

        return view('admin/ejesa_corte_views', [
            'corte'         => $corte,
            'geometria'     => $fila['geometria'] ?? null,
            'historial'     => array_reverse($historial),
            'afectados'     => $afectados,
            'consultas'     => $consultas,
            'ahora'         => $ahora,
            'acciones'      => self::ACCIONES,
            'modalidades'   => self::MODALIDADES,
            'resultados'    => self::RESULTADOS,
            'volver'        => $this->request->getGet('volver') ?: 'historial',
            'puedeVerFicha' => has_permission('LISTADO PERSONA'),
        ]);
    }

    // ─── Excel del historial (solo cantidades) ───────────────────────────
    public function export()
    {
        $lectura = new Lectura();
        $f       = $this->filtros();
        $d       = $this->armarPeriodo($lectura, $f);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Historial de cortes');

        $localidad = '';
        foreach ($lectura->localidadesConCortes() as $l) {
            if ((int) $l['localidad_id'] === (int) $f['localidad']) $localidad = $l['nombre'];
        }

        $sheet->setCellValue('A1', 'EJESA · Historial de cortes programados');
        $sheet->setCellValue('A2', 'Período: ' . date('d/m/Y', strtotime($f['desde'])) . ' al ' . date('d/m/Y', strtotime($f['hasta'])) . ' (inicio del corte, hora de Jujuy)');
        $sheet->setCellValue('A3', 'Filtros: localidad ' . ($localidad ?: 'todas') . ' · estado ' . ($f['estado'] ? Calculo::ESTADOS_VISIBLES[$f['estado']] : 'todos'));
        $sheet->setCellValue('A4', 'Datos al ' . Calculo::fecha($d['ahora']) . '. Afectados orientativos: el domicilio no prueba de qué red depende la persona. "A revisar" no se suma a los afectados. Solo cantidades.');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $cab = [
            'Nº', 'Referencia', 'Inicio', 'Fin estimado', 'Localidad', 'Zona', 'Estado',
            'Horas programadas', 'Horas sin luz', 'Horas sin luz (HH:MM)', 'Observación de horas',
            'Afectados', 'ALTO', 'MEDIANO', 'BAJO', 'PENDIENTE', 'A revisar (aparte)', 'Fuente de afectados',
            'Persona-horas', 'Persona-horas ALTO', 'Protocolo', 'Reprogramaciones', 'Motivo de cancelación',
        ];
        $sheet->fromArray($cab, null, 'A6');
        $ultima = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cab));
        $sheet->getStyle('A6:' . $ultima . '6')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF13304D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $fila = 7;
        foreach ($d['cortes'] as $c) {
            $a   = $c['afectados'];
            $sin = $a['total'] === null;
            $sheet->fromArray([
                (int) $c['corte_id'],
                (string) $c['referencia'],
                Calculo::fecha($c['inicio_at']),
                Calculo::fecha($c['fin_estimado_at']),
                $c['localidad'] ?: 'Localidad sin indicar',
                (string) $c['zona_descripcion'],
                Calculo::ESTADOS_VISIBLES[$c['estado_visible']],
                round($c['horas']['programadas'], 1),
                $c['horas']['cuenta'] ? round($c['horas']['sin_luz'], 1) : 0,
                $c['horas']['cuenta'] ? Calculo::hhmm($c['horas']['sin_luz']) : '00:00',
                Calculo::MARCAS[$c['horas']['marca']],
                $sin ? 'Sin dato' : $a['total'],
                $sin ? '' : $a['ALTO'],
                $sin ? '' : $a['MEDIANO'],
                $sin ? '' : $a['BAJO'],
                $sin ? '' : $a['PENDIENTE'],
                $a['a_revisar'] ?? '',
                $sin ? 'Sin protocolo: activar en sistemabasec' : 'Al activar el protocolo (' . Calculo::fecha($a['calculado_at']) . ')',
                $sin ? '' : round((float) $c['persona_horas'], 1),
                $sin ? '' : round((float) $c['persona_horas_alto'], 1),
                $c['protocolo'] ? 'SI' : 'NO',
                (int) $c['reprogramaciones'],
                (string) $c['motivo_cancelacion'],
            ], null, 'A' . $fila, true); // true: los 0 se escriben (sin esto PhpSpreadsheet los deja vacíos)
            $fila++;
        }

        if ($fila === 7) {
            $sheet->setCellValue('A7', 'Sin cortes publicados en el período.');
            $fila++;
        } else {
            $k = $d['kpis'];
            $sheet->fromArray([
                'TOTAL', '', '', '', '', '', $k['cortes'] . ' cortes',
                round($k['horas_programadas'], 1), round($k['horas_sin_luz'], 1), Calculo::hhmm($k['horas_sin_luz']), '',
                $k['afectados_suma'], $k['afectados_por_riesgo']['ALTO'], $k['afectados_por_riesgo']['MEDIANO'],
                $k['afectados_por_riesgo']['BAJO'], $k['afectados_por_riesgo']['PENDIENTE'], $k['a_revisar'],
                'Personas distintas: ' . ($k['personas_distintas'] ?? 'sin dato'),
                round($k['persona_horas'], 1), round($k['persona_horas_alto'], 1), $k['con_protocolo'], $k['reprogramaciones'], '',
            ], null, 'A' . $fila, true);
            $sheet->getStyle('A' . $fila . ':' . $ultima . $fila)->getFont()->setBold(true);
            $sheet->setCellValue('A' . ($fila + 1), 'La suma de afectados cuenta una vez por corte: una persona en varios cortes suma varias veces. Personas distintas del período: ' . ($k['personas_distintas'] ?? 'sin dato') . '.');
            $fila += 2;
        }

        foreach (range(1, count($cab)) as $i) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $sheet->freezePane('A7');

        $filename = 'ejesa_cortes_' . str_replace('-', '', $f['desde']) . '_' . str_replace('-', '', $f['hasta']) . '.xlsx';

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $contenido = (string) ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($contenido);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /** Filtros del panel. Período por defecto: el mes actual (hora de Jujuy). */
    private function filtros(): array
    {
        $hoy   = new DateTime('now', new DateTimeZone(Calculo::ZONA));
        $valida = static function ($v) {
            $d = DateTime::createFromFormat('!Y-m-d', (string) $v);

            return $d && $d->format('Y-m-d') === $v ? $v : null;
        };

        $desde = $valida($this->request->getGet('desde')) ?? $hoy->format('Y-m-01');
        $hasta = $valida($this->request->getGet('hasta')) ?? $hoy->format('Y-m-t');
        if ($hasta < $desde) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $cursor = null;
        $crudo  = base64_decode((string) $this->request->getGet('cursor'), true);
        if ($crudo && preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d{1,6})?)\|(\d+)$/', $crudo, $m)) {
            $cursor = ['at' => $m[1], 'id' => (int) $m[2]];
        }

        $estado    = (string) $this->request->getGet('estado');
        $modalidad = (string) $this->request->getGet('modalidad');
        $resultado = (string) $this->request->getGet('resultado');
        $accion    = (string) $this->request->getGet('accion');

        return [
            'desde'      => $desde,
            'hasta'      => $hasta,
            'hasta_excl' => (new DateTime($hasta))->modify('+1 day')->format('Y-m-d'),
            'localidad'  => (int) $this->request->getGet('localidad') ?: null,
            'estado'     => isset(Calculo::ESTADOS_VISIBLES[$estado]) ? $estado : '',
            'usuario'    => (int) $this->request->getGet('usuario') ?: null,
            'modalidad'  => isset(self::MODALIDADES[$modalidad]) ? $modalidad : '',
            'resultado'  => isset(self::RESULTADOS[$resultado]) ? $resultado : '',
            'accion'     => isset(self::ACCIONES[$accion]) ? $accion : '',
            'cursor'     => $cursor,
        ];
    }

    /** Une cada corte con su historial, protocolo y afectados, y calcula horas (§4). */
    private function enriquecer(Lectura $lectura, array $cortes, string $ahora): array
    {
        $ids = array_map(static fn ($c) => (int) $c['corte_id'], $cortes);
        if (!$ids) {
            return [];
        }
        $historial  = $lectura->historialDe($ids);
        $afectados  = $lectura->afectadosPorRiesgo($ids);
        $protocolos = $lectura->protocolos($ids);

        return array_map(static fn ($c) => Calculo::corte(
            $c,
            $historial[(int) $c['corte_id']] ?? [],
            $afectados[(int) $c['corte_id']] ?? [],
            $protocolos[(int) $c['corte_id']] ?? null,
            $ahora
        ), $cortes);
    }

    /** Cortes del período + KPIs + series + quiénes fueron afectados. */
    private function armarPeriodo(Lectura $lectura, array $f): array
    {
        $ahora  = Calculo::ahora();
        $cortes = $this->enriquecer($lectura, $lectura->cortes($f['desde'] . ' 00:00:00', $f['hasta_excl'] . ' 00:00:00', $f['localidad']), $ahora);

        if ($f['estado'] !== '') {
            $cortes = array_values(array_filter($cortes, static fn ($c) => $c['estado_visible'] === $f['estado']));
        }

        // Personas distintas: solo cortes que cuentan y con protocolo (los únicos con personas guardadas)
        $cuentan  = array_values(array_filter($cortes, static fn ($c) => $c['horas']['cuenta'] && $c['protocolo']));
        $vinculos = $lectura->vinculos(array_map(static fn ($c) => (int) $c['corte_id'], $cuentan));
        $hayDato  = count($cuentan) > 0;
        $distintas = $hayDato ? count(array_unique(array_map('intval', array_column($vinculos, 'electrodependiente_id')))) : null;

        $acumulado = Calculo::acumuladoPorPersona($vinculos, $cortes);
        $personas  = $lectura->personas(array_keys($acumulado));

        return [
            'ahora'      => $ahora,
            'cortes'     => $cortes,
            'kpis'       => Calculo::kpis($cortes, $distintas),
            'serie'      => Calculo::serieMensual($cortes, $f['desde'], $f['hasta_excl']),
            'localidadesTop' => Calculo::porLocalidad($cortes),
            'acumulado'  => $acumulado,
            'personas'   => $personas,
        ];
    }
}
