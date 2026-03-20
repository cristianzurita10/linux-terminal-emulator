<?php
// Iniciar sesión
session_start();
// Verificar si el usuario está logueado y es admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["es_admin"]) || $_SESSION["es_admin"] != 1){
    header("location: ../index.php");
    exit;
}
// Incluir archivo de configuración para la conexión a la base de datos
require_once "../config.php";
// Código de depuración (opcional)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Estadísticas de Acceso</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
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
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                Estadísticas
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="estadisticas_acceso.php" class="nav-link active">
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
            <h1 class="m-0">Estadísticas de Acceso</h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Tabla de logs de acceso -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Registro de accesos</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <?php 
            // Verificar si hay registros en la tabla
            $check_sql = "SELECT COUNT(*) as total FROM logs_acceso";
            $check_result = mysqli_query($conn, $check_sql);
            $check_row = mysqli_fetch_assoc($check_result);
            $total_registros = $check_row['total'];
            
            if ($total_registros == 0) {
                echo '<div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> No hay datos</h5>
                        No se encontraron registros de acceso en la base de datos. Pruebe a iniciar sesión varias veces para generar datos.
                      </div>';
            }
            ?>
            
            <table id="logs-table" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>IP</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Consultar los logs de acceso con join a la tabla usuarios - CAMBIADO fecha por fecha_acceso
                $sql = "SELECT l.id, u.username, l.ip_address, l.estado, l.fecha_acceso 
                        FROM logs_acceso l 
                        LEFT JOIN usuarios u ON l.usuario_id = u.id
                        ORDER BY l.fecha_acceso DESC";
                $result = mysqli_query($conn, $sql);
                
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . ($row['username'] ? htmlspecialchars($row['username']) : "Usuario no registrado") . "</td>";
                        echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                        echo "<td>";
                        if ($row['estado'] == 'exitoso') {
                            echo "<span class='badge badge-success'>Exitoso</span>";
                        } else {
                            echo "<span class='badge badge-danger'>Fallido</span>";
                        }
                        echo "</td>";
                        // CAMBIADO fecha por fecha_acceso
                        echo "<td>" . date("d/m/Y H:i:s", strtotime($row['fecha_acceso'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Error al consultar datos: " . mysqli_error($conn) . "</td></tr>";
                }
                ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>IP</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </tfoot>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <?php if ($total_registros > 0): ?>
        <!-- Gráficas adicionales -->
        <div class="row">
          <div class="col-md-6">
            <!-- GRÁFICA DE BARRAS POR HORA -->
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Accesos por hora del día</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="accesosPorHoraChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <!-- GRÁFICA DE BARRAS POR DIRECCIONES IP -->
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Principales direcciones IP</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="ipChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
        
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
<!-- DataTables  & Plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script>
  $(function () {
    // DataTable
    $("#logs-table").DataTable({
      "responsive": true, 
      "lengthChange": true, 
      "autoWidth": false,
      "order": [[4, 'desc']], // Ordenar por fecha descendente
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
      }
    });
    
    <?php if ($total_registros > 0): ?>
    <?php
    // Consulta para accesos por hora - CAMBIADO fecha por fecha_acceso
    $sql = "SELECT HOUR(fecha_acceso) as hora, 
           COUNT(*) as total,
           SUM(CASE WHEN estado = 'exitoso' THEN 1 ELSE 0 END) as exitosos,
           SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as fallidos
           FROM logs_acceso 
           GROUP BY HOUR(fecha_acceso)
           ORDER BY hora";
    
    $result = mysqli_query($conn, $sql);
    
    $horas = [];
    $data_exitosos_hora = [];
    $data_fallidos_hora = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $horas[] = $row['hora'] . ':00';
            $data_exitosos_hora[] = $row['exitosos'];
            $data_fallidos_hora[] = $row['fallidos'];
        }
    }
    
    // Consulta para top IPs
    $sql = "SELECT ip_address, COUNT(*) as total
            FROM logs_acceso
            GROUP BY ip_address
            ORDER BY total DESC
            LIMIT 10";
    
    $result = mysqli_query($conn, $sql);
    
    $ips = [];
    $data_ips = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $ips[] = $row['ip_address'];
            $data_ips[] = $row['total'];
        }
    }
    ?>
    
    // Gráfica de accesos por hora
    var ctxHora = document.getElementById('accesosPorHoraChart').getContext('2d');
    new Chart(ctxHora, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($horas); ?>,
        datasets: [
          {
            label: 'Accesos exitosos',
            backgroundColor: 'rgba(60,141,188,0.9)',
            borderColor: 'rgba(60,141,188,0.8)',
            pointRadius: false,
            pointColor: '#3b8bba',
            pointStrokeColor: 'rgba(60,141,188,1)',
            pointHighlightFill: '#fff',
            pointHighlightStroke: 'rgba(60,141,188,1)',
            data: <?php echo json_encode($data_exitosos_hora); ?>
          },
          {
            label: 'Accesos fallidos',
            backgroundColor: 'rgba(210, 214, 222, 0.9)',
            borderColor: 'rgba(210, 214, 222, 0.8)',
            pointRadius: false,
            pointColor: 'rgba(210, 214, 222, 1)',
            pointStrokeColor: '#c1c7d1',
            pointHighlightFill: '#fff',
            pointHighlightStroke: 'rgba(220,220,220,1)',
            data: <?php echo json_encode($data_fallidos_hora); ?>
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
    
    // Gráfica de top IPs
    var ctxIP = document.getElementById('ipChart').getContext('2d');
    new Chart(ctxIP, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($ips); ?>,
        datasets: [
          {
            label: 'Número de accesos',
            backgroundColor: 'rgba(23, 162, 184, 0.9)',
            borderColor: 'rgba(23, 162, 184, 1)',
            pointRadius: false,
            pointColor: '#17a2b8',
            pointStrokeColor: 'rgba(23, 162, 184, 1)',
            pointHighlightFill: '#fff',
            pointHighlightStroke: 'rgba(23, 162, 184, 1)',
            data: <?php echo json_encode($data_ips); ?>
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
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
            ticks: {
              beginAtZero: true
            }
          }
        }
      }
    });
    <?php endif; ?>
  });
</script>
</body>
</html>