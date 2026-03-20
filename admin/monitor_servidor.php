<?php
session_start();

// Verificar si el usuario está logueado y es admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["es_admin"]) || $_SESSION["es_admin"] != 1){
    header("location: ../index.php");
    exit;
}

// Conexión a la base de datos
require_once "../config.php";

// Función para formatear bytes a una unidad legible
function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Monitor del Servidor</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Chart.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Inicio</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="../logout.php" role="button">
          <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
      <img src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Panel Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION["username"]); ?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Usuarios
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="usuarios.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Gestionar usuarios</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                Estadísticas
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="estadisticas_acceso.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Estadísticas de acceso</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="monitor_servidor.php" class="nav-link active">
              <i class="nav-icon fas fa-server"></i>
              <p>Monitor del Servidor</p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Monitor del Servidor</h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-clock"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Tiempo Actividad</span>
                <span class="info-box-number" id="uptime">
                  Cargando...
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-database"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Conexiones</span>
                <span class="info-box-number" id="connections">Cargando...</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-microchip"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">CPU</span>
                <span class="info-box-number" id="cpu_usage">Cargando...</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-memory"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Uso Memoria</span>
                <span class="info-box-number" id="memory_usage">Cargando...</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- Gráficas -->
        <div class="row">
          <div class="col-md-6">
            <!-- AREA CHART -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Uso de RAM en tiempo real</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="ramChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->

          <div class="col-md-6">
            <!-- LINE CHART -->
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Uso de CPU en tiempo real</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="cpuChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- Tablas de estadísticas -->
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Variables del Sistema</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0" style="height: 300px;">
                <table class="table table-head-fixed text-nowrap">
                  <thead>
                    <tr>
                      <th>Variable</th>
                      <th>Valor</th>
                    </tr>
                  </thead>
                  <tbody id="system-variables">
                    <tr>
                      <td colspan="2">Cargando...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->

          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Estadísticas InnoDB</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0" style="height: 300px;">
                <table class="table table-head-fixed text-nowrap">
                  <thead>
                    <tr>
                      <th>Métrica</th>
                      <th>Valor</th>
                    </tr>
                  </thead>
                  <tbody id="innodb-status">
                    <tr>
                      <td colspan="2">Cargando...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- Tabla de bases de datos -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Tamaño de Bases de Datos</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0" style="height: 300px;">
                <table class="table table-head-fixed text-nowrap">
                  <thead>
                    <tr>
                      <th>Base de Datos</th>
                      <th>Tamaño</th>
                      <th>Tablas</th>
                    </tr>
                  </thead>
                  <tbody id="database-sizes">
                    <tr>
                      <td colspan="3">Cargando...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- Mensajes de depuración (Solo mostrar en caso de error) -->
        <div class="row" id="debug-container" style="display: none;">
          <div class="col-12">
            <div class="card card-danger">
              <div class="card-header">
                <h3 class="card-title">Mensajes de depuración</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <pre id="debug-messages"></pre>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
  <footer class="main-footer">
    <strong>Copyright &copy; 2025 <a href="#">Sistema de Login</a>.</strong>
    Todos los derechos reservados.
  </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
  // Datos para las gráficas
  const timestamps = [];
  const ramData = [];
  const cpuData = [];
  
  // Inicializar las gráficas
  const ramChart = new Chart(document.getElementById('ramChart').getContext('2d'), {
    type: 'line',
    data: {
      labels: timestamps,
      datasets: [{
        label: 'Uso de RAM (MB)',
        backgroundColor: 'rgba(60,141,188,0.5)',
        borderColor: 'rgba(60,141,188,0.8)',
        pointRadius: true,
        pointColor: '#3b8bba',
        pointStrokeColor: 'rgba(60,141,188,1)',
        pointHighlightFill: '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data: ramData
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          grid: {
            display: true
          },
          beginAtZero: true
        }
      }
    }
  });
  
  const cpuChart = new Chart(document.getElementById('cpuChart').getContext('2d'), {
    type: 'line',
    data: {
      labels: timestamps,
      datasets: [{
        label: 'Uso de CPU (%)',
        backgroundColor: 'rgba(23,162,184,0.5)',
        borderColor: 'rgba(23,162,184,0.8)',
        pointRadius: true,
        pointColor: '#17a2b8',
        pointStrokeColor: 'rgba(23,162,184,1)',
        pointHighlightFill: '#fff',
        pointHighlightStroke: 'rgba(23,162,184,1)',
        data: cpuData
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          grid: {
            display: true
          },
          beginAtZero: true,
          max: 100
        }
      }
    }
  });
  
  // Función para actualizar las gráficas y datos
  function actualizarDatos() {
    $.ajax({
      url: 'get_server_stats.php',
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        // Mostrar mensajes de depuración si existen
        if (data.debug && data.debug.length > 0) {
          $('#debug-messages').html(data.debug.join('<br>'));
          $('#debug-container').show();
        } else {
          $('#debug-container').hide();
        }
        
        // Actualizar contadores
        $('#uptime').text(data.uptime);
        $('#connections').text(data.connections);
        $('#cpu_usage').text(data.cpu_usage);
        $('#memory_usage').text(data.memory_usage);
        
        // Actualizar gráficas
        const now = new Date().toLocaleTimeString();
        timestamps.push(now);
        ramData.push(data.ram_data.value);
        cpuData.push(data.cpu_data.value);
        
        // Mantener solo los últimos 10 puntos
        if (timestamps.length > 10) {
          timestamps.shift();
          ramData.shift();
          cpuData.shift();
        }
        
        ramChart.update();
        cpuChart.update();
        
        // Actualizar tablas de variables
        let systemVarsHtml = '';
        for (const [key, value] of Object.entries(data.system_variables)) {
          systemVarsHtml += `<tr><td>${key}</td><td>${value}</td></tr>`;
        }
        $('#system-variables').html(systemVarsHtml);
        
        let innodbHtml = '';
        for (const [key, value] of Object.entries(data.innodb_status)) {
          innodbHtml += `<tr><td>${key}</td><td>${value}</td></tr>`;
        }
        $('#innodb-status').html(innodbHtml);
        
        // Actualizar tabla de bases de datos
        let dbHtml = '';
        for (const db of data.databases) {
          dbHtml += `<tr><td>${db.name}</td><td>${db.size}</td><td>${db.tables}</td></tr>`;
        }
        $('#database-sizes').html(dbHtml);
      },
      error: function(xhr, status, error) {
        console.error('Error al obtener datos del servidor:', error);
        $('#debug-messages').html('Error de AJAX: ' + error + '<br>Estado: ' + status);
        $('#debug-container').show();
      }
    });
  }
  
  // Actualizar datos inmediatamente y luego cada 5 segundos
  actualizarDatos();
  setInterval(actualizarDatos, 5000);
</script>
</body>
</html>