<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["es_admin"]) || $_SESSION["es_admin"] != 1) {
    header("location: ../index.php");
    exit;
}

// Incluir archivo de configuración
require_once "../config.php";

// Procesar eliminación de usuario si se solicita
if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    
    // No permitir eliminar el usuario actual
    if ($id != $_SESSION["id"]) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                header("location: usuarios.php?success=1");
                exit;
            } else {
                $error = "Error al eliminar usuario: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error = "No puedes eliminar tu propio usuario.";
    }
}

// Procesar cambio de tipo de usuario
if (isset($_GET["action"]) && $_GET["action"] == "toggle_admin" && isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    
    // No permitir cambiar tipo del usuario actual
    if ($id != $_SESSION["id"]) {
        $sql = "SELECT es_admin FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $es_admin);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        // Cambiar estado
        $nuevo_estado = $es_admin ? 0 : 1;
        $sql = "UPDATE usuarios SET es_admin = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $nuevo_estado, $id);
            if (mysqli_stmt_execute($stmt)) {
                header("location: usuarios.php?success=2");
                exit;
            } else {
                $error = "Error al cambiar privilegios: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error = "No puedes cambiar tus propios privilegios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Usuarios</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
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
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Usuarios
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="usuarios.php" class="nav-link active">
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
            <h1 class="m-0">Gestión de Usuarios</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
              <li class="breadcrumb-item active">Usuarios</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h5><i class="icon fas fa-ban"></i> Error!</h5>
          <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET["success"])): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
          <?php 
            if($_GET["success"] == 1) echo "Usuario eliminado correctamente.";
            elseif($_GET["success"] == 2) echo "Privilegios de usuario modificados correctamente.";
            elseif($_GET["success"] == 3) echo "Usuario añadido correctamente.";
          ?>
        </div>
        <?php endif; ?>
        
        <!-- Botón para añadir usuario -->
        <div class="row mb-3">
          <div class="col-12">
            <a href="agregar_usuario.php" class="btn btn-primary">
              <i class="fas fa-user-plus"></i> Añadir nuevo usuario
            </a>
          </div>
        </div>
        
        <!-- Depuración temporal para verificar la conexión -->
        <?php
        if (!$conn) {
            echo '<div class="alert alert-danger">Error de conexión: ' . mysqli_connect_error() . '</div>';
        }
        ?>
        
        <!-- Tabla de usuarios -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Listado de usuarios</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="usuarios-table" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Email</th>
                  <th>Tipo</th>
                  <th>Fecha registro</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Consultar todos los usuarios
                $sql = "SELECT id, username, email, es_admin, fecha_registro FROM usuarios ORDER BY id";
                $result = mysqli_query($conn, $sql);
                
                // Verificar si hay un error en la consulta
                if (!$result) {
                    echo '<tr><td colspan="6">Error en la consulta: ' . mysqli_error($conn) . '</td></tr>';
                } else {
                    // Verificar si hay resultados
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>";
                            if ($row['es_admin'] == 1) {
                                echo "<span class='badge badge-primary'>Administrador</span>";
                            } else {
                                echo "<span class='badge badge-secondary'>Usuario</span>";
                            }
                            echo "</td>";
                            // Comprobar si fecha_registro es un campo NULL o una fecha válida
                            $fecha = isset($row['fecha_registro']) ? date("d/m/Y H:i:s", strtotime($row['fecha_registro'])) : "N/A";
                            echo "<td>" . $fecha . "</td>";
                            echo "<td>";
                            // No mostrar botones de acción para el usuario actual
                            if ($row['id'] != $_SESSION['id']) {
                                echo "<a href='editar_usuario.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'><i class='fas fa-edit'></i></a> ";
                                
                                // Botón para cambiar privilegios
                                if ($row['es_admin'] == 1) {
                                    echo "<a href='usuarios.php?action=toggle_admin&id=" . $row['id'] . "' class='btn btn-warning btn-sm toggle-admin' data-toggle='tooltip' title='Quitar privilegios de administrador'><i class='fas fa-user-minus'></i></a> ";
                                } else {
                                    echo "<a href='usuarios.php?action=toggle_admin&id=" . $row['id'] . "' class='btn btn-success btn-sm toggle-admin' data-toggle='tooltip' title='Hacer administrador'><i class='fas fa-user-shield'></i></a> ";
                                }
                                
                                echo "<a href='usuarios.php?action=delete&id=" . $row['id'] . "' class='btn btn-danger btn-sm delete-user' data-toggle='tooltip' title='Eliminar usuario'><i class='fas fa-trash'></i></a>";
                            } else {
                                echo "<span class='text-muted'>Usuario actual</span>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="6">No hay usuarios registrados.</td></tr>';
                    }
                }
                ?>
              </tbody>
              <tfoot>
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Email</th>
                  <th>Tipo</th>
                  <th>Fecha registro</th>
                  <th>Acciones</th>
                </tr>
              </tfoot>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
        
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
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
  $(function () {
    // DataTable
    $("#usuarios-table").DataTable({
      "responsive": true, 
      "lengthChange": true, 
      "autoWidth": false,
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
      }
    });
    
    // Confirmación para eliminar usuario
    $('.delete-user').on('click', function(e) {
      e.preventDefault();
      const href = $(this).attr('href');
      
      Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = href;
        }
      });
    });
    
    // Confirmación para cambiar privilegios
    $('.toggle-admin').on('click', function(e) {
      e.preventDefault();
      const href = $(this).attr('href');
      const action = $(this).hasClass('btn-success') ? 'hacer administrador' : 'quitar privilegios de administrador';
      
      Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas ${action} a este usuario?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = href;
        }
      });
    });
    
    // Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
</body>
</html>