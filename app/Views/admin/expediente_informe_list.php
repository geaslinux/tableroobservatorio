<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Expedientes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .content-wrapper {
            padding: 20px;
        }
        .small-box {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Informe de Expedientes</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <!-- Tarjetas de conteo -->
                    <div class="row">
                        <?php foreach ($expedientesPorEstado as $estado): ?>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= $estado->count ?></h3>
                                        <p><?= $estado->estado ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Selectores -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuarioSelect">Seleccionar Usuario</label>
                                <select id="usuarioSelect" class="form-control">
                                    <option value="">Seleccione un usuario</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario->id ?>"><?= $usuario->username ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reparticionSelect">Seleccionar Área Emitida</label>
                                <select id="reparticionSelect" class="form-control">
                                    <option value="">Seleccione un área</option>
                                    <?php foreach ($reparticiones as $reparticion): ?>
                                        <option value="<?= $reparticion->reparticion_id ?>"><?= $reparticion->nombre ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Cantidad de Expedientes por Estado</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="expedientesPorEstadoChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Cantidad de Asignaciones por Usuario</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="asignacionesPorUsuarioChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Cantidad de Expedientes por Tipo</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="expedientesPorTipoChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Cantidad de Expedientes por Prioridad</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="expedientesPorPrioridadChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Detalles -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Detalles de Asignaciones</h3>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Expediente</th>
                                                <th>Usuario Asignado</th>
                                                <th>Mensaje</th>
                                                <th>Fecha de Asignación</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($asignaciones as $asignacion): ?>
                                                <tr>
                                                    <td><?= $asignacion->asignacion_id ?></td>
                                                    <td><?= $asignacion->tablero_id ?></td>
                                                    <td><?= $asignacion->username ?></td>
                                                    <td><?= $asignacion->mensaje ?></td>
                                                    <td><?= $asignacion->fecha_asignacion ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark"></aside>
        <!-- Main Footer -->
       
    </div>
    <!-- Script para los gráficos -->
  <script>document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de expedientes por estado
    var ctx1 = document.getElementById('expedientesPorEstadoChart').getContext('2d');
    var expedientesPorEstadoChart = new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($expedientesPorEstado, 'estado')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($expedientesPorEstado, 'count')) ?>,
                backgroundColor: ['#f39c12', '#00c0ef', '#00a65a', '#f56954']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de asignaciones por usuario
    var ctx2 = document.getElementById('asignacionesPorUsuarioChart').getContext('2d');
    var asignacionesPorUsuarioChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($asignacionesPorUsuario, 'usuario_asignado_id')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($asignacionesPorUsuario, 'count')) ?>,
                backgroundColor: '#3c8dbc'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de expedientes por tipo
    var ctx3 = document.getElementById('expedientesPorTipoChart').getContext('2d');
    var expedientesPorTipoChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($expedientesPorTipo, 'tipo_expediente')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($expedientesPorTipo, 'count')) ?>,
                backgroundColor: ['#f39c12', '#00c0ef', '#00a65a']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de expedientes por prioridad
    var ctx4 = document.getElementById('expedientesPorPrioridadChart').getContext('2d');
    var expedientesPorPrioridadChart = new Chart(ctx4, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($expedientesPorPrioridad, 'prioridad')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($expedientesPorPrioridad, 'count')) ?>,
                backgroundColor: ['#f56954', '#00a65a', '#f39c12']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Evento para el selector de usuario
    document.getElementById('usuarioSelect').addEventListener('change', function() {
        var userId = this.value;
        // Aquí puedes agregar la lógica para filtrar los gráficos y tablas según el usuario seleccionado
    });

    // Evento para el selector de área emitida
    document.getElementById('reparticionSelect').addEventListener('change', function() {
        var areaId = this.value;
        // Aquí puedes agregar la lógica para filtrar los gráficos y tablas según el área seleccionada
    });
});
</script>
</body>
</html>

                   