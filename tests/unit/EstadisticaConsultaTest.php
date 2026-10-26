<?php
namespace Tests\Unit;

use App\Libraries\EstadisticaConsulta;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Pruebas optativas de solo lectura sobre el esquema compartido real. */
final class EstadisticaConsultaTest extends CIUnitTestCase
{
    private int $usuario;
    private int $formulario;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('RUN_CONSULTA_DB_TESTS') !== '1') $this->markTestSkipped('Activar RUN_CONSULTA_DB_TESTS para lecturas de integración.');
        $this->db = db_connect('default');
        $fila = $this->db->query('SELECT u.user_id, g.formulario_id FROM auth_groups_users u JOIN grupo_formulario_consulta g ON g.group_id=u.group_id JOIN atencion_consulta a ON a.formulario_id=g.formulario_id AND a.deleted_at IS NULL LIMIT 1')->getRowArray();
        if (!$fila) $this->markTestSkipped('Se requiere al menos una atención con usuario autorizado.');
        $this->usuario = (int) $fila['user_id'];
        $this->formulario = (int) $fila['formulario_id'];
    }

    public function testTotalesDistribucionesDetalleYPaginacion(): void
    {
        $consulta = new EstadisticaConsulta($this->usuario, ['formulario' => $this->formulario]);
        $k = $consulta->resumen()['kpi'];
        $real = $this->db->table('atencion_consulta')->where('formulario_id', $this->formulario)->where('deleted_at', null)->countAllResults();
        $this->assertSame($real, (int) $k['atenciones']);
        foreach ($consulta->distribuciones() as $filas) $this->assertSame($real, array_sum(array_column($filas, 'cantidad')));
        $resumen = $consulta->resumen();
        $this->assertSame($real, array_sum(array_column($resumen['serie'], 'cantidad')));
        $this->assertSame($real, array_sum(array_column($resumen['medicos'], 'cantidad')));
        $primera = $consulta->detalle();
        $segunda = $consulta->detalle(2);
        $this->assertLessThanOrEqual(25, count($primera));
        $this->assertSame([], array_intersect(array_column($primera, 'atencion_id'), array_column($segunda, 'atencion_id')));
        foreach ($primera as $fila) {
            $this->assertArrayNotHasKey('paciente_dni', $fila);
            $this->assertArrayNotHasKey('paciente_nombre_completo', $fila);
            $this->assertSame($fila, $consulta->detalle(1, (int) $fila['atencion_id'])[0]);
        }
    }

    public function testUsuarioSinAsociacionNoVeNada(): void
    {
        $q = new EstadisticaConsulta(0, []);
        $this->assertSame(0, (int) $q->resumen()['kpi']['atenciones']);
        $this->assertSame([], $q->detalle());
        $this->assertSame([], $q->catalogos()['formularios']);
        $this->assertSame([], $q->catalogos()['medico']);
    }

    public function testFormularioFueraDeAlcanceResponde404(): void
    {
        $this->expectException(PageNotFoundException::class);
        new EstadisticaConsulta(0, ['formulario' => $this->formulario]);
    }

    public function testRangoInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new EstadisticaConsulta($this->usuario, ['desde' => '2026-10-23', 'hasta' => '2026-01-01']);
    }

    public function testFechaInexistente(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new EstadisticaConsulta($this->usuario, ['desde' => '2026-02-30']);
    }

    public function testFiltrosCoherentes(): void
    {
        $q = new EstadisticaConsulta($this->usuario, ['formulario' => $this->formulario, 'con_procedimiento' => '1']);
        $k = $q->resumen()['kpi'];
        $this->assertSame((int) $k['atenciones'], (int) $k['procedimientos']);
        $medico = (new EstadisticaConsulta($this->usuario, ['formulario' => $this->formulario]))->resumen()['medicos'][0];
        $filtrado = new EstadisticaConsulta($this->usuario, ['formulario' => $this->formulario, 'medico' => $medico['id']]);
        $this->assertSame((int) $medico['cantidad'], (int) $filtrado->resumen()['kpi']['atenciones']);
        $fila = $filtrado->detalle()[0];
        $dia = new EstadisticaConsulta($this->usuario, ['formulario' => $this->formulario, 'desde' => $fila['fecha'], 'hasta' => $fila['fecha']]);
        foreach ($dia->detalle() as $registro) $this->assertSame($fila['fecha'], $registro['fecha']);
        $vacio = new EstadisticaConsulta($this->usuario, ['hospital' => '__INEXISTENTE_PRUEBA__']);
        $this->assertSame(0, (int) $vacio->resumen()['kpi']['atenciones']);
        $this->assertSame([], $vacio->detalle());
    }

    private function controlador(array $filtros, bool $permiso = true): \App\Controllers\Admin\ConsultaTableroController
    {
        // Autenticación simulada solo en memoria: no crea sesiones ni cambia usuarios.
        config('Database')->defaultGroup = 'default';
        \CodeIgniter\Config\Factories::injectMock('models', \Myth\Auth\Models\GroupModel::class, new \Myth\Auth\Models\GroupModel($this->db));
        $auth = $this->getMockBuilder(\Myth\Auth\Authentication\LocalAuthenticator::class)->disableOriginalConstructor()->onlyMethods(['check', 'id', 'user'])->getMock();
        $auth->method('check')->willReturn(true);
        $auth->method('id')->willReturn($this->usuario);
        $auth->method('user')->willReturn(new \Myth\Auth\Entities\User(['id' => $this->usuario, 'username' => 'Verificación de lectura']));
        \Config\Services::injectMock('authentication', $auth);
        $autorizacion = $this->getMockBuilder(\Myth\Auth\Authorization\FlatAuthorization::class)->disableOriginalConstructor()->onlyMethods(['hasPermission'])->getMock();
        $autorizacion->method('hasPermission')->willReturn($permiso);
        \Config\Services::injectMock('authorization', $autorizacion);
        $request = \Config\Services::request();
        $request->setGlobal('get', $filtros);
        \Config\Services::routes()->loadRoutes();
        // El footer usa esta URI para seleccionar exclusivamente la tabla del módulo.
        $request->setPath('admin/consulta_tablero');
        $controller = new \App\Controllers\Admin\ConsultaTableroController();
        $controller->initController($request, \Config\Services::response(), \Config\Services::logger());
        return $controller;
    }

    public function testRenderExportacionYDetalleConFiltros(): void
    {
        $filtros = ['formulario' => (string) $this->formulario, 'con_procedimiento' => '1'];
        $consulta = new EstadisticaConsulta($this->usuario, $filtros);
        $controller = $this->controlador($filtros);
        $html = $controller->index();
        $this->assertIsString($html);
        $this->assertStringContainsString('consulta-graficos', $html);
        $this->assertStringNotContainsString('pendiente de conexión', $html);
        $this->assertStringContainsString('Exportar CSV', $html);
        if (getenv('CONSULTA_QA_HTML') === '1') {
            file_put_contents(WRITEPATH . 'consulta-tablero-qa.html', $html);
        }
        $respuesta = $controller->exportar();
        $this->assertSame(200, $respuesta->getStatusCode());
        $this->assertStringContainsString('text/csv', $respuesta->getHeaderLine('Content-Type'));
        $archivo = fopen('php://temp', 'w+');
        fwrite($archivo, $respuesta->getBody());
        rewind($archivo);
        $encabezado = fgetcsv($archivo, 0, ';');
        $this->assertCount(9, $encabezado);
        $filas = 0;
        while (($fila = fgetcsv($archivo, 0, ';')) !== false) {
            $this->assertNotSame('', $fila[6]);
            $filas++;
        }
        fclose($archivo);
        $this->assertSame((int) $consulta->resumen()['kpi']['atenciones'], $filas);
        $primera = $consulta->detalle()[0] ?? null;
        if ($primera) $this->assertStringContainsString('Valores históricos', $controller->detalle($primera['atencion_id']));
    }

    public function testControladorDeniegaAccionesSinPermiso(): void
    {
        $controller = $this->controlador([], false);
        $this->assertSame(403, $controller->index()->getStatusCode());
        $this->assertSame(403, $controller->exportar()->getStatusCode());
        $this->assertSame(403, $controller->detalle(1)->getStatusCode());
    }
}
