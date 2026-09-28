<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes

 */
$routes->get('/', '\Myth\Auth\Controllers\AuthController::login');

$routes->group('/admin',['filter' => 'login', 'namespace'=>'App\Controllers\Admin'], function($routes){

    $routes->get('inicio', 'IndexController::inicio', ['as'=>'inicio_views']);

    $routes->get('basev', 'BaseVController::basev', ['as'=>'base_views']);
     $routes->get('hospitalariov', 'HospitalarioVController::hospitalariov', ['as'=>'hospitalario_views']);
      $routes->get('internacionv', 'InternacionVController::internacionv', ['as'=>'internacion_views']);

     $routes->get('saludmental', 'SaludMentalController::salud', ['as'=>'saludmental_views']);
    $routes->get('pacientev', 'PacienteVController::pacientev', ['as'=>'paciente_views']);
    $routes->get('guardiav', 'GuardiaVController::guardiav', ['as'=>'guardia_views']);
    $routes->get('quirofanov', 'QuirofanoVController::quirofanov', ['as'=>'quirofano_views']);
    $routes->get('serviciov', 'ServicioVController::serviciov', ['as'=>'servicio_views']);
    $routes->get('laboratoriov', 'LaboratorioVController::laboratoriov', ['as'=>'laboratorio_views']);
    $routes->get('base', 'BaseCController::index', ['as'=>'base_list', 'filter' => 'permiso: LISTADO PERSONA']);
$routes->post('base', 'BaseCController::store', ['as'=>'base_store', 'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('base-create', 'BaseCController::create', ['as'=>'base_create', 'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('base/(:any)', 'BaseCController::show/$1', ['as'=>'base_show', 'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('base', 'BaseCController::update', ['as'=>'base_update', 'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('base', 'BaseCController::destroy', ['as'=>'base_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
    
    $routes->get('base-export', 'BaseCController::export', ['as'=>'base_export', 'filter' => 'permiso: LISTADO PERSONA']);
    
$routes->get('base-visto', 'BaseCController::marcarVisto', ['as'=>'base_visto']);
$routes->get('movil',         'MovilController::index',   ['as'=>'movil_list',    'filter'=>'permiso: LISTADO PERSONA']);
$routes->post('movil',        'MovilController::store',   ['as'=>'movil_store',   'filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('movil-create',  'MovilController::create',  ['as'=>'movil_create',  'filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('movil/(:any)',  'MovilController::show/$1', ['as'=>'movil_show',    'filter'=>'permiso: LISTADO PERSONA']);
$routes->put('movil',         'MovilController::update',  ['as'=>'movil_update',  'filter'=>'permiso: EDITAR PERSONA']);
$routes->delete('movil',      'MovilController::destroy', ['as'=>'movil_destroy', 'filter'=>'permiso: ELIMINAR PERSONA']);
$routes->get('movil-export',  'MovilController::export',  ['as'=>'movil_export',  'filter'=>'permiso: LISTADO PERSONA']);
$routes->get('movil-visto', 'MovilController::marcarVisto', ['as' => 'movil_visto', 'filter' => 'permiso: LISTADO PERSONA']);
// ── Cantidad Operativo ────────────────────────────────────────────────────
$routes->get('cantidad-operativo',           'CantidadOperativoController::index',       ['as' => 'cantidad_operativo_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('cantidad-operativo-create',    'CantidadOperativoController::create',      ['as' => 'cantidad_operativo_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('cantidad-operativo',          'CantidadOperativoController::store',       ['as' => 'cantidad_operativo_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('cantidad-operativo/(:any)',    'CantidadOperativoController::show/$1',     ['as' => 'cantidad_operativo_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('cantidad-operativo',           'CantidadOperativoController::update',      ['as' => 'cantidad_operativo_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('cantidad-operativo',        'CantidadOperativoController::destroy',     ['as' => 'cantidad_operativo_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('cantidad-operativo-export',    'CantidadOperativoController::export',      ['as' => 'cantidad_operativo_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('cantidad-operativo-visto',     'CantidadOperativoController::marcarVisto', ['as' => 'cantidad_operativo_visto',   'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('atencion',        'AtencionController::index',   ['as'=>'atencion_list',   'filter'=>'permiso: LISTADO PERSONA']);
$routes->get('atencion-import', 'AtencionController::import',  ['as'=>'atencion_import', 'filter'=>'permiso: GUARDAR PERSONA']);
$routes->post('atencion-import','AtencionController::processImport', ['as'=>'atencion_process_import','filter'=>'permiso: GUARDAR PERSONA']);
$routes->delete('atencion',     'AtencionController::destroy', ['as'=>'atencion_destroy','filter'=>'permiso: ELIMINAR PERSONA']);
$routes->get('atencion-export', 'AtencionController::export',  ['as'=>'atencion_export', 'filter'=>'permiso: LISTADO PERSONA']);
$routes->get('atencion-template','AtencionController::template',['as'=>'atencion_template','filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('atencion-create',  'AtencionController::create',      ['as'=>'atencion_create', 'filter'=>'permiso: GUARDAR PERSONA']);
$routes->post('atencion',        'AtencionController::store',       ['as'=>'atencion_store',  'filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('atencion-visto',   'AtencionController::marcarVisto', ['as'=>'atencion_visto',  'filter'=>'permiso: LISTADO PERSONA']);

$routes->get('identificacion',         'IdentificacionController::index',         ['as'=>'identificacion_list',           'filter'=>'permiso: LISTADO PERSONA']);
$routes->get('identificacion-import',  'IdentificacionController::import',        ['as'=>'identificacion_import',         'filter'=>'permiso: GUARDAR PERSONA']);
$routes->post('identificacion-import', 'IdentificacionController::processImport', ['as'=>'identificacion_process_import', 'filter'=>'permiso: GUARDAR PERSONA']);
$routes->delete('identificacion',      'IdentificacionController::destroy',       ['as'=>'identificacion_destroy',        'filter'=>'permiso: ELIMINAR PERSONA']);
$routes->get('identificacion-export',  'IdentificacionController::export',        ['as'=>'identificacion_export',         'filter'=>'permiso: LISTADO PERSONA']);
$routes->get('identificacion-template','IdentificacionController::template',      ['as'=>'identificacion_template',       'filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('identificacion-create',  'IdentificacionController::create',      ['as'=>'identificacion_create',  'filter'=>'permiso: GUARDAR PERSONA']);
$routes->post('identificacion',        'IdentificacionController::store',        ['as'=>'identificacion_store',   'filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('identificacion-visto',   'IdentificacionController::marcarVisto', ['as'=>'identificacion_visto',   'filter'=>'permiso: LISTADO PERSONA']);

$routes->get('asistencia',         'AsistenciaController::index',         ['as'=>'asistencia_list',           'filter'=>'permiso: LISTADO PERSONA']);
$routes->get('asistencia-import',  'AsistenciaController::import',        ['as'=>'asistencia_import',         'filter'=>'permiso: GUARDAR PERSONA']);
$routes->post('asistencia-import', 'AsistenciaController::processImport', ['as'=>'asistencia_process_import', 'filter'=>'permiso: GUARDAR PERSONA']);
$routes->delete('asistencia',      'AsistenciaController::destroy',       ['as'=>'asistencia_destroy',        'filter'=>'permiso: ELIMINAR PERSONA']);
$routes->get('asistencia-export',  'AsistenciaController::export',        ['as'=>'asistencia_export',         'filter'=>'permiso: LISTADO PERSONA']);
$routes->get('asistencia-template','AsistenciaController::template',      ['as'=>'asistencia_template',       'filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('asistencia-visto', 'AsistenciaController::marcarVisto', ['as'=>'asistencia_visto']);
$routes->get('asistencia-create', 'AsistenciaController::create', ['as'=>'asistencia_create', 'filter'=>'permiso: GUARDAR PERSONA']);
$routes->post('asistencia',       'AsistenciaController::store',  ['as'=>'asistencia_store',  'filter'=>'permiso: GUARDAR PERSONA']);
$routes->get('asistencia/(:any)', 'AsistenciaController::show/$1', ['as'=>'asistencia_show', 'filter'=>'permiso: LISTADO PERSONA']);
$routes->put('asistencia',        'AsistenciaController::update',  ['as'=>'asistencia_update', 'filter'=>'permiso: EDITAR PERSONA']);
// ── Tipo Operativo ────────────────────────────────────────────────────────
$routes->get('tipo-operativo',         'OperativoABMController::indexTipo',       ['as' => 'tipo_operativo_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('tipo-operativo-create',  'OperativoABMController::createTipo',      ['as' => 'tipo_operativo_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('tipo-operativo',        'OperativoABMController::storeTipo',       ['as' => 'tipo_operativo_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('tipo-operativo/(:any)',  'OperativoABMController::showTipo/$1',     ['as' => 'tipo_operativo_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('tipo-operativo',         'OperativoABMController::updateTipo',      ['as' => 'tipo_operativo_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('tipo-operativo',      'OperativoABMController::destroyTipo',     ['as' => 'tipo_operativo_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
// ── Transfusion ───────────────────────────────────────────────────────────
$routes->get('transfusion',           'TransfusionController::index',       ['as' => 'transfusion_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('transfusion-create',    'TransfusionController::create',      ['as' => 'transfusion_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('transfusion',          'TransfusionController::store',       ['as' => 'transfusion_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('transfusion/(:any)',    'TransfusionController::show/$1',     ['as' => 'transfusion_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('transfusion',           'TransfusionController::update',      ['as' => 'transfusion_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('transfusion',        'TransfusionController::destroy',     ['as' => 'transfusion_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('transfusion-export',    'TransfusionController::export',      ['as' => 'transfusion_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('transfusion-visto',     'TransfusionController::marcarVisto', ['as' => 'transfusion_visto',   'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('electrodependiente',             'ElectrodependienteController::index',       ['as' => 'electrodependiente_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('electrodependiente-create',      'ElectrodependienteController::create',      ['as' => 'electrodependiente_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('electrodependiente-export',      'ElectrodependienteController::export',      ['as' => 'electrodependiente_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('electrodependiente-visto',       'ElectrodependienteController::marcarVisto', ['as' => 'electrodependiente_visto',   'filter' => 'permiso: LISTADO PERSONA']);
$routes->post('electrodependiente',            'ElectrodependienteController::store',       ['as' => 'electrodependiente_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('electrodependiente/(:num)/historial', 'ElectrodependienteController::historial/$1', ['as' => 'electrodependiente_historial', 'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('electrodependiente/(:any)',      'ElectrodependienteController::show/$1',     ['as' => 'electrodependiente_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('electrodependiente',             'ElectrodependienteController::update',      ['as' => 'electrodependiente_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('electrodependiente',          'ElectrodependienteController::destroy',     ['as' => 'electrodependiente_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
// ── Efector ABM ───────────────────────────────────────────────────────────
$routes->get('efector',               'EfectorABMController::index',        ['as' => 'efector_list',        'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('efector-create',        'EfectorABMController::create',       ['as' => 'efector_create',      'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('efector',              'EfectorABMController::store',        ['as' => 'efector_store',       'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('efector/(:any)',        'EfectorABMController::show/$1',      ['as' => 'efector_show',        'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('efector',               'EfectorABMController::update',       ['as' => 'efector_update',      'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('efector',            'EfectorABMController::destroy',      ['as' => 'efector_destroy',     'filter' => 'permiso: ELIMINAR PERSONA']);
// ── Operativo ─────────────────────────────────────────────────────────────
$routes->get('operativo',              'OperativoABMController::indexOperativo',  ['as' => 'operativo_list',         'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('operativo-create',       'OperativoABMController::createOperativo', ['as' => 'operativo_create',       'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('operativo',             'OperativoABMController::storeOperativo',  ['as' => 'operativo_store',        'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('operativo/(:any)',       'OperativoABMController::showOperativo/$1',['as' => 'operativo_show',         'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('operativo',              'OperativoABMController::updateOperativo', ['as' => 'operativo_update',       'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('operativo',           'OperativoABMController::destroyOperativo',['as' => 'operativo_destroy',      'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('transfusion-resumen',        'TransfusionController::resumen',      ['as' => 'transfusion_resumen',        'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('transfusion-resumen-export', 'TransfusionController::exportResumen',['as' => 'transfusion_resumen_export', 'filter' => 'permiso: LISTADO PERSONA']);

// ── Consulta Reclamo ──────────────────────────────────────────────────────
$routes->get('consulta-reclamo',          'ConsultaReclamoController::index',       ['as' => 'consulta_reclamo_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('consulta-reclamo-create',   'ConsultaReclamoController::create',      ['as' => 'consulta_reclamo_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('consulta-reclamo',         'ConsultaReclamoController::store',       ['as' => 'consulta_reclamo_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('consulta-reclamo/(:any)',   'ConsultaReclamoController::show/$1',     ['as' => 'consulta_reclamo_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('consulta-reclamo',          'ConsultaReclamoController::update',      ['as' => 'consulta_reclamo_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('consulta-reclamo',       'ConsultaReclamoController::destroy',     ['as' => 'consulta_reclamo_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('consulta-reclamo-export',   'ConsultaReclamoController::export',      ['as' => 'consulta_reclamo_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('consulta-reclamo-visto',    'ConsultaReclamoController::marcarVisto', ['as' => 'consulta_reclamo_visto',   'filter' => 'permiso: LISTADO PERSONA']);

// ── Categoria ABM ─────────────────────────────────────────────────────────
$routes->get('categoria',                 'CategoriaABMController::indexCategoria',    ['as' => 'categoria_list',           'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('categoria-create',          'CategoriaABMController::createCategoria',   ['as' => 'categoria_create',         'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('categoria',                'CategoriaABMController::storeCategoria',    ['as' => 'categoria_store',          'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('categoria/(:any)',          'CategoriaABMController::showCategoria/$1',  ['as' => 'categoria_show',           'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('categoria',                 'CategoriaABMController::updateCategoria',   ['as' => 'categoria_update',         'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('categoria',              'CategoriaABMController::destroyCategoria',  ['as' => 'categoria_destroy',        'filter' => 'permiso: ELIMINAR PERSONA']);

// ── Sub Categoria ABM ─────────────────────────────────────────────────────
$routes->get('sub-categoria',             'CategoriaABMController::indexSubCategoria',    ['as' => 'sub_categoria_list',       'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('sub-categoria-create',      'CategoriaABMController::createSubCategoria',   ['as' => 'sub_categoria_create',     'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('sub-categoria',            'CategoriaABMController::storeSubCategoria',    ['as' => 'sub_categoria_store',      'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('sub-categoria/(:any)',      'CategoriaABMController::showSubCategoria/$1',  ['as' => 'sub_categoria_show',       'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('sub-categoria',             'CategoriaABMController::updateSubCategoria',   ['as' => 'sub_categoria_update',     'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('sub-categoria',          'CategoriaABMController::destroySubCategoria',  ['as' => 'sub_categoria_destroy',    'filter' => 'permiso: ELIMINAR PERSONA']);

// ── Chat Bot ──────────────────────────────────────────────────────────────
$routes->get('chat-bot',           'ChatBotController::index',       ['as' => 'chat_bot_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('chat-bot-create',    'ChatBotController::create',      ['as' => 'chat_bot_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('chat-bot',          'ChatBotController::store',       ['as' => 'chat_bot_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('chat-bot/(:any)',    'ChatBotController::show/$1',     ['as' => 'chat_bot_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('chat-bot',           'ChatBotController::update',      ['as' => 'chat_bot_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('chat-bot',        'ChatBotController::destroy',     ['as' => 'chat_bot_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('chat-bot-export',    'ChatBotController::export',      ['as' => 'chat_bot_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('chat-bot-visto',     'ChatBotController::marcarVisto', ['as' => 'chat_bot_visto',   'filter' => 'permiso: LISTADO PERSONA']);


// ── Turno Hospitalario ────────────────────────────────────────────────────
$routes->get('turno-hospitalario',          'TurnoHospitalarioController::index',       ['as' => 'turno_hospitalario_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('turno-hospitalario-create',   'TurnoHospitalarioController::create',      ['as' => 'turno_hospitalario_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('turno-hospitalario',         'TurnoHospitalarioController::store',       ['as' => 'turno_hospitalario_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('turno-hospitalario/(:any)',   'TurnoHospitalarioController::show/$1',     ['as' => 'turno_hospitalario_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('turno-hospitalario',          'TurnoHospitalarioController::update',      ['as' => 'turno_hospitalario_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('turno-hospitalario',       'TurnoHospitalarioController::destroy',     ['as' => 'turno_hospitalario_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('turno-hospitalario-export',   'TurnoHospitalarioController::export',      ['as' => 'turno_hospitalario_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('turno-hospitalario-visto',    'TurnoHospitalarioController::marcarVisto', ['as' => 'turno_hospitalario_visto',   'filter' => 'permiso: LISTADO PERSONA']);
// ── Call Center ───────────────────────────────────────────────────────────
$routes->get('call-center',          'CallCenterController::index',       ['as' => 'call_center_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('call-center-create',   'CallCenterController::create',      ['as' => 'call_center_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('call-center',         'CallCenterController::store',       ['as' => 'call_center_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('call-center/(:any)',   'CallCenterController::show/$1',     ['as' => 'call_center_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('call-center',          'CallCenterController::update',      ['as' => 'call_center_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('call-center',       'CallCenterController::destroy',     ['as' => 'call_center_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('call-center-export',   'CallCenterController::export',      ['as' => 'call_center_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('call-center-visto',    'CallCenterController::marcarVisto', ['as' => 'call_center_visto',   'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('gestion_cama',          'GestionCamaController::index',       ['as' => 'gestion_cama_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('gestion_cama-create',   'GestionCamaController::create',      ['as' => 'gestion_cama_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('gestion_cama',         'GestionCamaController::store',       ['as' => 'gestion_cama_store',   'filter' => 'permiso: GUARDAR PERSONA']); // ← quitá la 'r'
$routes->get('gestion_cama/(:any)',   'GestionCamaController::show/$1',     ['as' => 'gestion_cama_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('gestion_cama',          'GestionCamaController::update',      ['as' => 'gestion_cama_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('gestion_cama',       'GestionCamaController::destroy',     ['as' => 'gestion_cama_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('gestion_cama-export',   'GestionCamaController::export',      ['as' => 'gestion_cama_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('gestion_cama-visto',    'GestionCamaController::marcarVisto', ['as' => 'gestion_cama_visto',   'filter' => 'permiso: LISTADO PERSONA']);

// ── Gestión Paciente Hospital ─────────────────────────────────────────────
$routes->get('gestion_paciente_hospital',          'GestionPacienteHospitalController::index',       ['as' => 'gestion_paciente_hospital_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('gestion_paciente_hospital-create',   'GestionPacienteHospitalController::create',      ['as' => 'gestion_paciente_hospital_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('gestion_paciente_hospital',         'GestionPacienteHospitalController::store',       ['as' => 'gestion_paciente_hospital_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('gestion_paciente_hospital/(:any)',   'GestionPacienteHospitalController::show/$1',     ['as' => 'gestion_paciente_hospital_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('gestion_paciente_hospital',          'GestionPacienteHospitalController::update',      ['as' => 'gestion_paciente_hospital_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('gestion_paciente_hospital',       'GestionPacienteHospitalController::destroy',     ['as' => 'gestion_paciente_hospital_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('gestion_paciente_hospital-export',   'GestionPacienteHospitalController::export',      ['as' => 'gestion_paciente_hospital_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('gestion_paciente_hospital-visto',    'GestionPacienteHospitalController::marcarVisto', ['as' => 'gestion_paciente_hospital_visto',   'filter' => 'permiso: LISTADO PERSONA']);

$routes->get('ambulatorio',          'AmbulatorioController::index',       ['as' => 'ambulatorio_list',    'filter' => 'permiso: LISTADO PERSONA']);

// ── CARTA DE SERVICIO ──
$routes->get('carta_servicio',            'CartaServicioController::index',         ['as' => 'carta_servicio_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('carta_servicio-import',     'CartaServicioController::import',        ['as' => 'carta_servicio_import',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('carta_servicio-import',    'CartaServicioController::processImport', ['as' => 'carta_servicio_process_import', 'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('carta_servicio-template',   'CartaServicioController::template',      ['as' => 'carta_servicio_template', 'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('carta_servicio-export',     'CartaServicioController::export',        ['as' => 'carta_servicio_export',  'filter' => 'permiso: LISTADO PERSONA']);

// ── RRHH CARTA DE SERVICIO ──
$routes->get('rrhh_carta_servicio',            'RrhhCartaServicioController::index',         ['as' => 'rrhh_carta_servicio_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rrhh_carta_servicio-import',     'RrhhCartaServicioController::import',        ['as' => 'rrhh_carta_servicio_import',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('rrhh_carta_servicio-import',    'RrhhCartaServicioController::processImport', ['as' => 'rrhh_carta_servicio_process_import', 'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('rrhh_carta_servicio-template',   'RrhhCartaServicioController::template',      ['as' => 'rrhh_carta_servicio_template', 'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rrhh_carta_servicio-export',     'RrhhCartaServicioController::export',        ['as' => 'rrhh_carta_servicio_export',  'filter' => 'permiso: LISTADO PERSONA']);

// ── LABORATORIO (Red de laboratorios) ──
$routes->get('laboratorio',            'LaboratorioController::index',         ['as' => 'laboratorio_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('laboratorio-import',     'LaboratorioController::import',        ['as' => 'laboratorio_import',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('laboratorio-import',    'LaboratorioController::processImport', ['as' => 'laboratorio_process_import', 'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('laboratorio-template',   'LaboratorioController::template',      ['as' => 'laboratorio_template', 'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('laboratorio-export',     'LaboratorioController::export',        ['as' => 'laboratorio_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('laboratorio-create',     'LaboratorioController::create',        ['as' => 'laboratorio_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('laboratorio',           'LaboratorioController::store',         ['as' => 'laboratorio_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('laboratorio/(:any)',     'LaboratorioController::show/$1',       ['as' => 'laboratorio_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('laboratorio',            'LaboratorioController::update',        ['as' => 'laboratorio_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('laboratorio',         'LaboratorioController::destroy',       ['as' => 'laboratorio_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);


// ── Guardia ────────────────────────────────────────────────────────────────
$routes->get('guardia',           'GuardiaController::index',       ['as' => 'guardia_list']);
$routes->get('guardia-create',    'GuardiaController::create',      ['as' => 'guardia_create']);
$routes->post('guardia',          'GuardiaController::store',       ['as' => 'guardia_store']);
$routes->get('guardia/(:any)',    'GuardiaController::show/$1',     ['as' => 'guardia_show']);
$routes->put('guardia',           'GuardiaController::update',      ['as' => 'guardia_update']);
$routes->delete('guardia',        'GuardiaController::destroy',     ['as' => 'guardia_destroy']);
$routes->get('guardia-export',    'GuardiaController::export',      ['as' => 'guardia_export']);
$routes->get('guardia-visto',     'GuardiaController::marcarVisto', ['as' => 'guardia_visto']);


// ── Producción Quirófano ─────────────────────────────────────────────────
$routes->get('produccion-quirofano',           'ProduccionQuirofanoController::index',   ['as' => 'produccion_quirofano_list']);
$routes->get('produccion-quirofano-create',    'ProduccionQuirofanoController::create',  ['as' => 'produccion_quirofano_create']);
$routes->post('produccion-quirofano',          'ProduccionQuirofanoController::store',   ['as' => 'produccion_quirofano_store']);
$routes->get('produccion-quirofano/(:num)',    'ProduccionQuirofanoController::edit/$1', ['as' => 'produccion_quirofano_edit']);
$routes->put('produccion-quirofano/(:num)',    'ProduccionQuirofanoController::update/$1',['as' => 'produccion_quirofano_update']);
$routes->delete('produccion-quirofano',        'ProduccionQuirofanoController::destroy', ['as' => 'produccion_quirofano_destroy']);
$routes->get('produccion-quirofano-export',    'ProduccionQuirofanoController::export',  ['as' => 'produccion_quirofano_export']);

// ── Producción Quirófano Hospitalario ────────────────────────────────────────
$routes->get('quirofano',           'ProduccionQuirofanoHospController::index',       ['as' => 'quirofano_list']);
$routes->get('quirofano-create',    'ProduccionQuirofanoHospController::create',      ['as' => 'quirofano_create']);
$routes->post('quirofano',          'ProduccionQuirofanoHospController::store',       ['as' => 'quirofano_store']);
$routes->get('quirofano/(:num)',    'ProduccionQuirofanoHospController::show/$1',     ['as' => 'quirofano_show']);
$routes->put('quirofano/(:num)',    'ProduccionQuirofanoHospController::update/$1',   ['as' => 'quirofano_update']);
$routes->delete('quirofano',        'ProduccionQuirofanoHospController::destroy',     ['as' => 'quirofano_destroy']);
$routes->get('quirofano-export',    'ProduccionQuirofanoHospController::export',      ['as' => 'quirofano_export']);
$routes->get('quirofano-visto',     'ProduccionQuirofanoHospController::marcarVisto', ['as' => 'quirofano_visto']);


$routes->get('lista-espera',           'ListaEsperaController::index',       ['as' => 'lista_espera_list']);
$routes->get('lista-espera-create',    'ListaEsperaController::create',      ['as' => 'lista_espera_create']);
$routes->post('lista-espera',          'ListaEsperaController::store',       ['as' => 'lista_espera_store']);
$routes->get('lista-espera/(:num)',    'ListaEsperaController::show/$1',     ['as' => 'lista_espera_show']);
$routes->put('lista-espera/(:num)',    'ListaEsperaController::update/$1',   ['as' => 'lista_espera_update']);
$routes->delete('lista-espera',        'ListaEsperaController::destroy',     ['as' => 'lista_espera_destroy']);
$routes->get('lista-espera-export',    'ListaEsperaController::export',      ['as' => 'lista_espera_export']);
$routes->get('lista-espera-visto',     'ListaEsperaController::marcarVisto', ['as' => 'lista_espera_visto']);

$routes->post('especialidad', 'ListaEsperaController::storeEspecialidad', ['as' => 'especialidad_store']);
// ── Salud Mental - Camas ──────────────────────────────────────────────────
$routes->get('salud-mental-camas',           'SaludMentalCamasController::index',       ['as' => 'salud_mental_camas_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('salud-mental-camas-create',    'SaludMentalCamasController::create',      ['as' => 'salud_mental_camas_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('salud-mental-camas',          'SaludMentalCamasController::store',       ['as' => 'salud_mental_camas_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('salud-mental-camas/(:num)',    'SaludMentalCamasController::show/$1',     ['as' => 'salud_mental_camas_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('salud-mental-camas',           'SaludMentalCamasController::update',      ['as' => 'salud_mental_camas_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('salud-mental-camas',        'SaludMentalCamasController::destroy',     ['as' => 'salud_mental_camas_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('salud-mental-camas-export',    'SaludMentalCamasController::export',      ['as' => 'salud_mental_camas_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('salud-mental-camas-visto',     'SaludMentalCamasController::marcarVisto', ['as' => 'salud_mental_camas_visto',   'filter' => 'permiso: LISTADO PERSONA']);
// ── Capacidad de Camas ───────────────────────────────────────────────────


// ── Hospitalario - Rendimiento ──────────────────────────────────────────────
$routes->get('rendimiento-hospitalario',           'RendimientoHospitalarioController::index',       ['as' => 'rendimiento_hospitalario_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario-create',    'RendimientoHospitalarioController::create',      ['as' => 'rendimiento_hospitalario_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('rendimiento-hospitalario',          'RendimientoHospitalarioController::store',       ['as' => 'rendimiento_hospitalario_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('rendimiento-hospitalario/(:num)',    'RendimientoHospitalarioController::show/$1',     ['as' => 'rendimiento_hospitalario_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('rendimiento-hospitalario',           'RendimientoHospitalarioController::update',      ['as' => 'rendimiento_hospitalario_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('rendimiento-hospitalario',        'RendimientoHospitalarioController::destroy',     ['as' => 'rendimiento_hospitalario_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('rendimiento-hospitalario-export',    'RendimientoHospitalarioController::export',      ['as' => 'rendimiento_hospitalario_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario-visto',     'RendimientoHospitalarioController::marcarVisto', ['as' => 'rendimiento_hospitalario_visto',   'filter' => 'permiso: LISTADO PERSONA']);


// ── Hospitalario - Rendimiento UTI ──────────────────────────────────────────
$routes->get('rendimiento-hospitalario-uti',           'RendimientoHospitalarioUtiController::index',       ['as' => 'rendimiento_hospitalario_uti_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario-uti-create',    'RendimientoHospitalarioUtiController::create',      ['as' => 'rendimiento_hospitalario_uti_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('rendimiento-hospitalario-uti',          'RendimientoHospitalarioUtiController::store',       ['as' => 'rendimiento_hospitalario_uti_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('rendimiento-hospitalario-uti/(:num)',    'RendimientoHospitalarioUtiController::show/$1',     ['as' => 'rendimiento_hospitalario_uti_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('rendimiento-hospitalario-uti',           'RendimientoHospitalarioUtiController::update',      ['as' => 'rendimiento_hospitalario_uti_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('rendimiento-hospitalario-uti',        'RendimientoHospitalarioUtiController::destroy',     ['as' => 'rendimiento_hospitalario_uti_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('rendimiento-hospitalario-uti-export',    'RendimientoHospitalarioUtiController::export',      ['as' => 'rendimiento_hospitalario_uti_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario-uti-visto',     'RendimientoHospitalarioUtiController::marcarVisto', ['as' => 'rendimiento_hospitalario_uti_visto',   'filter' => 'permiso: LISTADO PERSONA']);


$routes->get('rh-materno',           'RhMaternoController::index',       ['as' => 'rh_materno_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rh-materno-create',    'RhMaternoController::create',      ['as' => 'rh_materno_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('rh-materno',          'RhMaternoController::store',       ['as' => 'rh_materno_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('rh-materno/(:any)',    'RhMaternoController::show/$1',     ['as' => 'rh_materno_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('rh-materno',           'RhMaternoController::update',      ['as' => 'rh_materno_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('rh-materno',        'RhMaternoController::destroy',     ['as' => 'rh_materno_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('rh-materno-export',    'RhMaternoController::export',      ['as' => 'rh_materno_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rh-materno-visto',     'RhMaternoController::marcarVisto', ['as' => 'rh_materno_visto',   'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario',                'RendimientoHospitalarioController::index',                  ['as' => 'rh_list',                  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario-create',          'RendimientoHospitalarioController::create',                 ['as' => 'rh_create',                'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('rendimiento-hospitalario',                'RendimientoHospitalarioController::store',                  ['as' => 'rh_store',                 'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('rendimiento-hospitalario/(:any)',          'RendimientoHospitalarioController::show/$1',                ['as' => 'rh_show',                  'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('rendimiento-hospitalario',                 'RendimientoHospitalarioController::update',                 ['as' => 'rh_update',                'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('rendimiento-hospitalario',              'RendimientoHospitalarioController::destroy',                ['as' => 'rh_destroy',               'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('rendimiento-hospitalario-export',          'RendimientoHospitalarioController::export',                 ['as' => 'rh_export',                'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario-visto',           'RendimientoHospitalarioController::marcarVisto',            ['as' => 'rh_visto',                 'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('rendimiento-hospitalario-sectores/(:num)', 'RendimientoHospitalarioController::sectoresPorServicio/$1', ['as' => 'rh_sectores_por_servicio', 'filter' => 'permiso: LISTADO PERSONA']);

$routes->get('capacidad-camas',           'CapacidadCamasController::index',       ['as' => 'capacidad_camas_list',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('capacidad-camas-create',    'CapacidadCamasController::create',      ['as' => 'capacidad_camas_create',  'filter' => 'permiso: GUARDAR PERSONA']);
$routes->post('capacidad-camas',          'CapacidadCamasController::store',       ['as' => 'capacidad_camas_store',   'filter' => 'permiso: GUARDAR PERSONA']);
$routes->get('capacidad-camas/(:num)',    'CapacidadCamasController::show/$1',     ['as' => 'capacidad_camas_show',    'filter' => 'permiso: LISTADO PERSONA']);
$routes->put('capacidad-camas',           'CapacidadCamasController::update',      ['as' => 'capacidad_camas_update',  'filter' => 'permiso: EDITAR PERSONA']);
$routes->delete('capacidad-camas',        'CapacidadCamasController::destroy',     ['as' => 'capacidad_camas_destroy', 'filter' => 'permiso: ELIMINAR PERSONA']);
$routes->get('capacidad-camas-export',    'CapacidadCamasController::export',      ['as' => 'capacidad_camas_export',  'filter' => 'permiso: LISTADO PERSONA']);
$routes->get('capacidad-camas-visto',     'CapacidadCamasController::marcarVisto', ['as' => 'capacidad_camas_visto',   'filter' => 'permiso: LISTADO PERSONA']);
});












