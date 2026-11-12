<?php

namespace Tests\Unit;

use App\Libraries\Ejesa\Calculo;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Cálculos de la pestaña EJESA (docs/ejesa_visor/SKILL.md §4 y checklist §8).
 * Sin base de datos: todo con filas armadas a mano.
 * Recordatorio: inicio_at / fin_estimado_at en hora local de Jujuy (UTC-3); registrado_at y cancelado_at en UTC.
 */
final class EjesaCalculoTest extends CIUnitTestCase
{
    private const AHORA = '2026-11-10 12:00:00';

    private function corte(array $datos = []): array
    {
        return $datos + [
            'corte_id' => 1, 'referencia' => 'R1', 'motivo' => null, 'estado' => 'PROGRAMADO', 'version' => 1,
            'inicio_at' => '2026-11-05 08:00:00', 'fin_estimado_at' => '2026-11-05 12:00:00',
            'localidad_id' => 15, 'localidad' => 'San Salvador de Jujuy', 'zona_descripcion' => 'Centro',
            'publicado_at' => '2026-11-01 12:00:00', 'cancelado_at' => null, 'motivo_cancelacion' => null,
        ];
    }

    // ─── Estado que se muestra ───────────────────────────────────────────

    public function testEstados(): void
    {
        $this->assertSame('PROGRAMADO', Calculo::estado($this->corte(['inicio_at' => '2026-11-11 08:00:00', 'fin_estimado_at' => '2026-11-11 10:00:00']), self::AHORA));
        $this->assertSame('EN_CURSO', Calculo::estado($this->corte(['inicio_at' => '2026-11-10 10:00:00', 'fin_estimado_at' => '2026-11-10 14:00:00']), self::AHORA));
        $this->assertSame('EN_CURSO', Calculo::estado($this->corte(['estado' => 'EN_CURSO', 'inicio_at' => '2026-11-11 10:00:00', 'fin_estimado_at' => '2026-11-11 14:00:00']), self::AHORA));
        $this->assertSame('VENCIDO', Calculo::estado($this->corte(), self::AHORA));
        $this->assertSame('VENCIDO', Calculo::estado($this->corte(['estado' => 'EN_CURSO']), self::AHORA));
        $this->assertSame('FINALIZADO', Calculo::estado($this->corte(['estado' => 'FINALIZADO']), self::AHORA));
        $this->assertSame('CANCELADO', Calculo::estado($this->corte(['estado' => 'CANCELADO']), self::AHORA));
    }

    // ─── Horas sin luz ───────────────────────────────────────────────────

    public function testFinalizadoAntesDelFinEstimadoUsaLaHoraReal(): void
    {
        // FINALIZAR a las 13:00 UTC = 10:00 Jujuy: 2 h de las 4 programadas
        $h = Calculo::horasSinLuz($this->corte(['estado' => 'FINALIZADO']), ['registrado_at' => '2026-11-05 13:00:00', 'vencido' => false], self::AHORA);

        $this->assertSame(4.0, $h['programadas']);
        $this->assertSame(2.0, $h['sin_luz']);
        $this->assertSame('REAL', $h['marca']);
        $this->assertTrue($h['cuenta']);
    }

    public function testFinalizadoTardeUsaFinEstimadoYMarcaNoRegistrado(): void
    {
        $h = Calculo::horasSinLuz($this->corte(['estado' => 'FINALIZADO']), ['registrado_at' => '2026-11-06 15:00:00', 'vencido' => true], self::AHORA);

        $this->assertSame(4.0, $h['sin_luz']);
        $this->assertSame('NO_REGISTRADO', $h['marca']);
    }

    public function testFinalizadoSinRegistroNoInventaElFin(): void
    {
        $h = Calculo::horasSinLuz($this->corte(['estado' => 'FINALIZADO']), null, self::AHORA);

        $this->assertSame(4.0, $h['sin_luz']);
        $this->assertSame('NO_REGISTRADO', $h['marca']);
    }

    public function testEnCursoCuentaHastaAhora(): void
    {
        $h = Calculo::horasSinLuz($this->corte(['inicio_at' => '2026-11-10 09:30:00', 'fin_estimado_at' => '2026-11-10 14:00:00']), null, self::AHORA);

        $this->assertSame(2.5, $h['sin_luz']);
        $this->assertSame(4.5, $h['programadas']);
        $this->assertSame('EN_CURSO', $h['marca']);
        $this->assertTrue($h['cuenta']);
    }

    public function testVencidoSinFinalizarUsaFinEstimado(): void
    {
        $h = Calculo::horasSinLuz($this->corte(), null, self::AHORA);

        $this->assertSame(4.0, $h['sin_luz']);
        $this->assertSame('SIN_FINALIZAR', $h['marca']);
    }

    public function testCanceladoAntesDelInicioNoCuenta(): void
    {
        // 10:00 UTC = 07:00 Jujuy, antes de las 08:00
        $h = Calculo::horasSinLuz($this->corte(['estado' => 'CANCELADO', 'cancelado_at' => '2026-11-05 10:00:00']), null, self::AHORA);

        $this->assertSame(0.0, $h['sin_luz']);
        $this->assertSame('CANCELADO_ANTES', $h['marca']);
        $this->assertFalse($h['cuenta']);
    }

    public function testCanceladoJustoAlInicioNoCuenta(): void
    {
        $h = Calculo::horasSinLuz($this->corte(['estado' => 'CANCELADO', 'cancelado_at' => '2026-11-05 11:00:00']), null, self::AHORA);

        $this->assertFalse($h['cuenta']);
    }

    public function testCanceladoDuranteElCorteCuentaHastaLaCancelacion(): void
    {
        // 12:30 UTC = 09:30 Jujuy
        $h = Calculo::horasSinLuz($this->corte(['estado' => 'CANCELADO', 'cancelado_at' => '2026-11-05 12:30:00']), null, self::AHORA);

        $this->assertSame(1.5, $h['sin_luz']);
        $this->assertSame('CANCELADO_DURANTE', $h['marca']);
        $this->assertTrue($h['cuenta']);
    }

    public function testProgramadoFuturoNoSumaHorasEjecutadas(): void
    {
        $h = Calculo::horasSinLuz($this->corte(['inicio_at' => '2026-11-12 08:00:00', 'fin_estimado_at' => '2026-11-12 13:00:00']), null, self::AHORA);

        $this->assertSame(0.0, $h['sin_luz']);
        $this->assertSame(5.0, $h['programadas']);
        $this->assertFalse($h['cuenta']);
    }

    // ─── Afectados ───────────────────────────────────────────────────────

    public function testSinProtocoloNoHayDatoYNoEsCero(): void
    {
        $a = Calculo::afectados([], null);

        $this->assertNull($a['total']);
        $this->assertNull($a['a_revisar']);
        $this->assertNull($a['fuente']);
    }

    public function testRiesgoNullEsPendienteYARevisarNoSeSuma(): void
    {
        $a = Calculo::afectados(
            [
                ['riesgo_snapshot' => 'ALTO', 'cantidad' => 2],
                ['riesgo_snapshot' => 'BAJO', 'cantidad' => 3],
                ['riesgo_snapshot' => null, 'cantidad' => 4],
                ['riesgo_snapshot' => '', 'cantidad' => 1],
            ],
            ['created_at' => '2026-11-04 15:00:00', 'ambito' => '{"en_zona": 10, "a_revisar": 39, "revision": 1}']
        );

        $this->assertSame(10, $a['total']);
        $this->assertSame(5, $a['PENDIENTE']);
        $this->assertSame(0, $a['MEDIANO']);
        $this->assertSame(39, $a['a_revisar']);
        $this->assertSame('PROTOCOLO', $a['fuente']);
        $this->assertSame('2026-11-04 12:00:00', $a['calculado_at']);
    }

    public function testProtocoloSinPersonasEnZonaEsCeroConDato(): void
    {
        $a = Calculo::afectados([], ['created_at' => '2026-11-04 15:00:00', 'ambito' => '{"en_zona": 0, "a_revisar": 39, "revision": 1}']);

        $this->assertSame(0, $a['total']);
        $this->assertSame(39, $a['a_revisar']);
    }

    public function testRiesgoNuncaEsBajoPorDefecto(): void
    {
        $this->assertSame('PENDIENTE', Calculo::riesgo(null));
        $this->assertSame('PENDIENTE', Calculo::riesgo('S/D'));
        $this->assertSame('MEDIANO', Calculo::riesgo(' mediano '));
    }

    // ─── Corte completo, KPIs y personas distintas ───────────────────────

    public function testCorteSinLocalidadYPersonaHoras(): void
    {
        $c = Calculo::corte(
            // Versión 3: el protocolo se activó en la 1 y después se reprogramó
            $this->corte(['estado' => 'FINALIZADO', 'version' => 3, 'localidad_id' => null, 'localidad' => null]),
            [
                ['accion' => 'ACTIVAR_PROTOCOLO', 'cambios' => '{"protocolo": {"en_zona": 3}}', 'registrado_at' => '2026-11-04 15:00:00'],
                ['accion' => 'REPROGRAMAR', 'cambios' => '{"inicio_at": {"antes": "2026-11-05 07:00:00", "despues": "2026-11-05 08:00:00"}}', 'registrado_at' => '2026-11-04 16:00:00'],
                ['accion' => 'FINALIZAR', 'cambios' => '{"estado": {"antes": "PROGRAMADO", "despues": "FINALIZADO"}}', 'registrado_at' => '2026-11-05 14:00:00'],
            ],
            [['riesgo_snapshot' => 'ALTO', 'cantidad' => 1], ['riesgo_snapshot' => 'BAJO', 'cantidad' => 2]],
            ['created_at' => '2026-11-04 15:00:00', 'ambito' => '{"en_zona": 3, "a_revisar": 5, "revision": 1}', 'estado' => 'ABIERTA'],
            self::AHORA
        );

        // FINALIZAR 14:00 UTC = 11:00 local: 3 h
        $this->assertSame(3.0, $c['horas']['sin_luz']);
        $this->assertSame(9.0, $c['persona_horas']);
        $this->assertSame(3.0, $c['persona_horas_alto']);
        $this->assertSame(1, $c['reprogramaciones']);
        $this->assertTrue($c['afectados']['desactualizado']);
        $this->assertNull($c['localidad']);

        $loc = Calculo::porLocalidad([$c]);
        $this->assertSame('Localidad sin indicar', $loc[0]['localidad']);
    }

    public function testKpisExcluyenCanceladosAntesYNoSumanSinDato(): void
    {
        $prot = ['created_at' => '2026-11-01 15:00:00', 'ambito' => '{"en_zona": 2, "a_revisar": 7, "revision": 1}'];

        $cortes = [
            // vencido sin finalizar, 4 h, 2 afectados (1 ALTO)
            Calculo::corte($this->corte(['corte_id' => 1]), [], [['riesgo_snapshot' => 'ALTO', 'cantidad' => 1], ['riesgo_snapshot' => null, 'cantidad' => 1]], $prot, self::AHORA),
            // cancelado antes: fuera de totales
            Calculo::corte($this->corte(['corte_id' => 2, 'estado' => 'CANCELADO', 'cancelado_at' => '2026-11-05 09:00:00']), [], [], null, self::AHORA),
            // finalizado a tiempo, sin protocolo: 2 h, sin dato de afectados
            Calculo::corte($this->corte(['corte_id' => 3, 'estado' => 'FINALIZADO']), [['accion' => 'FINALIZAR', 'cambios' => '{}', 'registrado_at' => '2026-11-05 13:00:00']], [], null, self::AHORA),
        ];

        $k = Calculo::kpis($cortes, 2);

        $this->assertSame(3, $k['cortes']);
        $this->assertSame(2, $k['ejecutados']);
        $this->assertSame(1, $k['cancelados_antes']);
        $this->assertSame(6.0, $k['horas_sin_luz']);
        $this->assertSame(3.0, $k['duracion_media']);
        $this->assertSame(2, $k['afectados_suma']);
        $this->assertSame(1, $k['afectados_alto']);
        $this->assertSame(7, $k['a_revisar']);
        $this->assertSame(8.0, $k['persona_horas']);
        $this->assertSame(1, $k['sin_dato_afectados']);
        $this->assertSame(1, $k['vencidos']);
        $this->assertSame(50.0, $k['cumplimiento_cierre']);
        $this->assertSame(2, $k['personas_distintas']);
    }

    public function testPersonasDistintasNoSumanTotalesDeCortes(): void
    {
        $prot = ['created_at' => '2026-11-01 15:00:00', 'ambito' => '{}'];
        $c1   = Calculo::corte($this->corte(['corte_id' => 1]), [], [['riesgo_snapshot' => 'ALTO', 'cantidad' => 2]], $prot, self::AHORA);
        $c2   = Calculo::corte($this->corte(['corte_id' => 2, 'inicio_at' => '2026-11-06 08:00:00', 'fin_estimado_at' => '2026-11-06 10:00:00']), [], [['riesgo_snapshot' => 'ALTO', 'cantidad' => 1]], $prot, self::AHORA);

        $vinculos = [
            ['corte_id' => 1, 'electrodependiente_id' => 7, 'riesgo_snapshot' => 'ALTO'],
            ['corte_id' => 1, 'electrodependiente_id' => 8, 'riesgo_snapshot' => 'ALTO'],
            ['corte_id' => 2, 'electrodependiente_id' => 7, 'riesgo_snapshot' => 'ALTO'],
        ];
        $p = Calculo::acumuladoPorPersona($vinculos, [$c1, $c2]);

        $this->assertCount(2, $p);              // 2 personas distintas, aunque la suma por corte da 3
        $this->assertSame(2, $p[7]['cortes']);
        $this->assertSame(6.0, $p[7]['horas']);
        $this->assertSame(7, array_key_first($p)); // el más afectado primero
    }

    public function testSerieMensualIncluyeMesesVacios(): void
    {
        $c = Calculo::corte($this->corte(), [], [], null, self::AHORA);
        $s = Calculo::serieMensual([$c], '2026-10-01', '2026-12-01');

        $this->assertSame(['2026-10', '2026-11'], array_keys($s));
        $this->assertSame(0, $s['2026-10']['cortes']);
        $this->assertSame('NOVIEMBRE', $s['2026-11']['mes']);
        $this->assertSame(1, $s['2026-11']['sin_dato']);
    }

    // ─── Auditoría y formato ─────────────────────────────────────────────

    public function testAuditoriaAgrupaEnHoraLocalYDetectaAlertas(): void
    {
        $filas = [];
        // 02:00 UTC = 23:00 del día anterior en Jujuy: fuera de horario
        $filas[] = ['usuario_id' => 5, 'usuario_nombre_snapshot' => 'geas', 'perfil_snapshot' => 'ejesa', 'consultado_at' => '2026-11-06 02:00:00.000000',
            'modalidad' => 'CORTE', 'resultado' => 'CON_RESULTADOS', 'cantidad_personas' => 10];
        for ($i = 0; $i < 5; $i++) {
            $filas[] = ['usuario_id' => 6, 'usuario_nombre_snapshot' => 'otro', 'perfil_snapshot' => 'ejesa', 'consultado_at' => '2026-11-06 15:0' . $i . ':00',
                'modalidad' => 'SERVICIO_EXACTO', 'resultado' => 'SIN_RESULTADOS', 'cantidad_personas' => 0];
        }
        $filas[] = ['usuario_id' => 6, 'usuario_nombre_snapshot' => 'otro', 'perfil_snapshot' => 'ejesa', 'consultado_at' => '2026-11-06 16:00:00',
            'modalidad' => 'SERVICIO_EXACTO', 'resultado' => 'DENEGADA', 'cantidad_personas' => 0];

        $s = Calculo::auditoria($filas);

        $this->assertSame(7, $s['total']);
        $this->assertSame(1, $s['por_dia']['2026-11-05']);
        $this->assertSame(6, $s['por_dia']['2026-11-06']);
        $this->assertSame(1, $s['fuera_horario']);
        $this->assertSame(1, $s['denegadas']);
        $tipos = array_column($s['alertas'], 'tipo');
        $this->assertContains('FUERA_HORARIO', $tipos);
        $this->assertContains('SIN_RESULTADOS', $tipos);
        $this->assertSame(6, $s['por_usuario']['otro']['total']);
    }

    public function testServicioEnmascarado(): void
    {
        $this->assertSame('•••••392', Calculo::servicio('309392'));
        $this->assertSame('—', Calculo::servicio(null));
        $this->assertStringNotContainsString('309', Calculo::servicio('309392'));
    }

    public function testFormatos(): void
    {
        $this->assertSame('3,5 h', Calculo::horas(3.5));
        $this->assertSame('03:30', Calculo::hhmm(3.5));
        $this->assertSame('2 d 5 h', Calculo::faltan('2026-11-12 17:00:00', self::AHORA));
        $this->assertSame('3 h 20 min', Calculo::faltan('2026-11-10 15:20:00', self::AHORA));
        $this->assertSame('', Calculo::faltan('2026-11-10 11:00:00', self::AHORA));
        $this->assertSame('10/11/2026 09:00', Calculo::fecha(Calculo::utcALocal('2026-11-10 12:00:00')));
        $this->assertSame('Mar 10/11 08:00 a 13:00', Calculo::rango('2026-11-10 08:00:00', '2026-11-10 13:00:00'));
        $this->assertStringStartsWith('Reprogramado: inicio 05/11/2026 07:00 → 05/11/2026 08:00',
            Calculo::describirCambio('REPROGRAMAR', '{"inicio_at": {"antes": "2026-11-05 07:00:00", "despues": "2026-11-05 08:00:00"}}'));
    }

    public function testEquiposAgrupaPorTipoYCuentaUnaVezPorPersona(): void
    {
        $personas = [
            1 => ['equipos' => [['equipamiento' => 'concentrador de oxigeno portatil'], ['equipamiento' => 'Concentrador de Oxígeno']]],
            2 => ['equipos' => [['equipamiento' => 'CONCENTRADOR DE OXIGENO'], ['equipamiento' => 'CPAP (parcial)']]],
            3 => ['equipos' => [['equipamiento' => 'bipap']]],
            4 => ['equipos' => []],
        ];
        $r = Calculo::equiposResumen($personas);

        $this->assertSame(['tipo' => 'Concentrador de oxígeno', 'clave' => 'concentrador', 'personas' => 2], $r[0]);
        $tipos = array_column($r, 'personas', 'tipo');
        $this->assertSame(1, $tipos['CPAP']);
        $this->assertSame(1, $tipos['BiPAP']); // "bipap" no se confunde con CPAP
        $this->assertSame(1, $tipos['Sin equipo cargado']);
        $this->assertSame('Sin equipo cargado', end($r)['tipo']); // siempre al final
    }

    public function testPorEstadoIncluyeEstadosEnCero(): void
    {
        $e = Calculo::porEstado([['estado_visible' => 'FINALIZADO'], ['estado_visible' => 'FINALIZADO'], ['estado_visible' => 'VENCIDO']]);

        $this->assertSame(2, $e['FINALIZADO']);
        $this->assertSame(1, $e['VENCIDO']);
        $this->assertSame(0, $e['CANCELADO']);
        $this->assertSame(array_keys(Calculo::ESTADOS_VISIBLES), array_keys($e));
    }
}