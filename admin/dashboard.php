<?php
session_start();
// Verificar si el usuario está logueado y es admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["es_admin"]) || $_SESSION["es_admin"] != 1){
    header("location: ../index.php");
    exit;
}
// Conexión a la base de datos
require_once "../config.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel de Administración</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.1/css/OverlayScrollbars.min.css">
  <!-- Chart.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>
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
            <a href="dashboard.php" class="nav-link active">
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
          <!-- NUEVO: Enlace al Monitor del Servidor -->
          <li class="nav-item">
            <a href="monitor_servidor.php" class="nav-link">
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
            <h1 class="m-0">Dashboard</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <?php
                // Contar usuarios totales
                $sql = "SELECT COUNT(*) as total FROM usuarios";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                $total_usuarios = $row['total'];
                ?>
                <h3><?php echo $total_usuarios; ?></h3>
                <p>Usuarios registrados</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="usuarios.php" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <?php
                // Contar accesos exitosos
                $sql = "SELECT COUNT(*) as total FROM logs_acceso WHERE estado = 'exitoso'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                $accesos_exitosos = $row['total'];
                ?>
                <h3><?php echo $accesos_exitosos; ?></h3>
                <p>Accesos exitosos</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="estadisticas_acceso.php" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <?php
                // Contar accesos fallidos
                $sql = "SELECT COUNT(*) as total FROM logs_acceso WHERE estado = 'fallido'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                $accesos_fallidos = $row['total'];
                ?>
                <h3><?php echo $accesos_fallidos; ?></h3>
                <p>Intentos fallidos</p>
              </div>
              <div class="icon">
                <i class="ion ion-alert-circled"></i>
              </div>
              <a href="estadisticas_acceso.php" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <?php
                // Calcular tasa de fallos
                $tasa_fallos = 0;
                if ($accesos_exitosos + $accesos_fallidos > 0) {
                    $tasa_fallos = round(($accesos_fallidos / ($accesos_exitosos + $accesos_fallidos)) * 100);
                }
                ?>
                <h3><?php echo $tasa_fallos; ?><sup style="font-size: 20px">%</sup></h3>
                <p>Tasa de fallos</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="estadisticas_acceso.php" class="small-box-footer">Más información <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Gráficas -->
        <div class="row">
          <div class="col-md-6">
            <!-- ÁREA CHART -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Accesos por día</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="accesosChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <!-- PIE CHART -->
            <div class="card card-danger">
              <div class="card-header">
                <h3 class="card-title">Distribución de accesos</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
<!-- jQuery UI -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<!-- Overlay Scrollbars -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.1/js/jquery.overlayScrollbars.min.js"></script>
<!-- Gráficas en tiempo real -->
<script>
  // Preparar datos para gráficas
  <?php
  // Consulta para accesos por día (últimos 7 días)
  // CAMBIO: todas las referencias a 'fecha' por 'fecha_acceso'
  $sql = "SELECT DATE(fecha_acceso) as dia, COUNT(*) as total, 
         SUM(CASE WHEN estado = 'exitoso' THEN 1 ELSE 0 END) as exitosos,
         SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as fallidos
         FROM logs_acceso 
         WHERE fecha_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         GROUP BY DATE(fecha_acceso)
         ORDER BY dia";
  
  $result = mysqli_query($conn, $sql);
  
  $labels = [];
  $data_exitosos = [];
  $data_fallidos = [];
  
  while ($row = mysqli_fetch_assoc($result)) {
      $labels[] = date("d/m", strtotime($row['dia']));
      $data_exitosos[] = $row['exitosos'];
      $data_fallidos[] = $row['fallidos'];
  }
  ?>
  
  // Gráfica de accesos por día
  var ctxAccesos = document.getElementById('accesosChart').getContext('2d');
  var accesosChart = new Chart(ctxAccesos, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($labels); ?>,
      datasets: [
        {
          label: 'Accesos exitosos',
          backgroundColor: 'rgba(60,141,188,0.5)',
          borderColor: 'rgba(60,141,188,0.8)',
          pointRadius: true,
          pointColor: '#3b8bba',
          pointStrokeColor: 'rgba(60,141,188,1)',
          pointHighlightFill: '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data: <?php echo json_encode($data_exitosos); ?>
        },
        {
          label: 'Accesos fallidos',
          backgroundColor: 'rgba(210, 214, 222, 0.5)',
          borderColor: 'rgba(210, 214, 222, 0.8)',
          pointRadius: true,
          pointColor: 'rgba(210, 214, 222, 1)',
          pointStrokeColor: '#c1c7d1',
          pointHighlightFill: '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data: <?php echo json_encode($data_fallidos); ?>
        }
      ]
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
          }
        }
      }
    }
  });
  // Gráfica de distribución de accesos (pie chart)
  var ctxPie = document.getElementById('pieChart').getContext('2d');
  var pieChart = new Chart(ctxPie, {
    type: 'pie',
    data: {
      labels: ['Accesos exitosos', 'Accesos fallidos'],
      datasets: [
        {
          data: [<?php echo $accesos_exitosos; ?>, <?php echo $accesos_fallidos; ?>],
          backgroundColor: ['#00a65a', '#f56954']
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
  // Función para actualizar las gráficas en tiempo real
  function actualizarGraficas() {
    $.ajax({
      url: 'get_chart_data.php',
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        // Actualizar datos de la gráfica de accesos
        accesosChart.data.labels = data.accesos.labels;
        accesosChart.data.datasets[0].data = data.accesos.exitosos;
        accesosChart.data.datasets[1].data = data.accesos.fallidos;
        accesosChart.update();
        
        // Actualizar datos del pie chart
        pieChart.data.datasets[0].data = [data.pie.exitosos, data.pie.fallidos];
        pieChart.update();
        
        // Actualizar los contadores
        $('.total-usuarios').text(data.contadores.total_usuarios);
        $('.accesos-exitosos').text(data.contadores.accesos_exitosos);
        $('.accesos-fallidos').text(data.contadores.accesos_fallidos);
        $('.tasa-fallos').text(data.contadores.tasa_fallos);
      }
    });
  }
  
  // Actualizar cada 30 segundos
  setInterval(actualizarGraficas, 30000);
</script>
</body>
</html>