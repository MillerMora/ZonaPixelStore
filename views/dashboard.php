<?php

/**
 * Panel de administración: métricas resumidas y enlaces a módulos en iframe.
 * Acceso restringido a rol administrador (valor 1 en sesión).
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])) {
  $logueo = $_SESSION['rol'];
}

if ($logueo != 1) {
  header('location: ./404.php');
}
include './php/usuarios/usuarioModel.php';
include './php/productos/productoModel.php';
include './php/pedidos/pedidoModel.php';

$consulta = consultar_usuarios_recientes();

// --- KPI y listas recientes para tarjetas del tablero ---
$total_usuarios = total_usuarios();
$usuarios_mes_actual = usuarios_mes();
$pedidos_mes = pedidos_mes() ?: 0;
$ingresos_mes = ingresos_mes() ?: 0;
$ingresos_total = ingresos_totales() ?: 0;
$pedidos_recientes_consulta = pedidos_recientes(5);
$top_productos = productos_mas_vendidos(5);


?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/dashboard.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
  <div class="dashboard-wrapper">
    <!-- ========== SIDEBAR ========== -->
    <aside class="dashboard-sidebar">
      <div class="dash-logo">
        <span class="dash-logo-dot"></span>
        <a href="../index.php" class="dash-logo-text">ZonaPixel</a>
        <span class="text-caption me-auto">Admin</span>
      </div>

      <nav class="dash-nav">
        <div class="dash-nav-section">Principal</div>
        <button class="dash-nav-item active" data-section="dashboard">
          <i class="fas fa-th-large"></i> Dashboard
        </button>
        <button class="dash-nav-item" data-section="pedidos">
          <i class="fas fa-box"></i> Pedidos
        </button>
        <button class="dash-nav-item" data-section="productos">
          <i class="fas fa-gamepad"></i> Productos
        </button>

        <div class="dash-nav-section">Comunidad</div>
        <button class="dash-nav-item" data-section="resenas">
          <i class="fas fa-star"></i> Reseñas
        </button>
        <button class="dash-nav-item" data-section="opiniones">
          <i class="fas fa-comment"></i> Opiniones
        </button>

        <div class="dash-nav-section">Administración</div>
        <button class="dash-nav-item" data-section="usuarios">
          <i class="fas fa-users"></i> Gestión de usuarios
        </button>
        <button class="dash-nav-item" data-section="config">
          <i class="fas fa-cog"></i> Configuración
        </button>
      </nav>

<div class="user-dropdown">
        <div class="dash-user user-dropdown-toggle" title="Mi cuenta">
          <div class="dash-user-avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
          <div>
            <div class="dash-user-name"><?php echo $_SESSION['username']; ?></div>
            <div class="dash-user-role">Administrador</div>
          </div>
        </div>
        <div class="user-dropdown-menu" style="bottom: 100%; top: auto; margin-bottom: 8px;">
          <a href="/views/php/usuarios/editar_usuario_cliente.php" class="user-dropdown-item">
            <i class="fas fa-user-circle"></i> Perfil
          </a>
          <a href="#" class="user-dropdown-item">
            <i class="fas fa-cog"></i> Configuración
          </a>
          <hr class="dropdown-divider">
          <a href="/views/php/usuarios/login/login.php?cerrar=1" class="user-dropdown-item logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
          </a>
        </div>
      </div>
    </aside>

    <!-- ========== MAIN ========== -->
    <div class="dashboard-main">
      <!-- TOPBAR -->
      <div class="dash-topbar">
        <span class="dash-page-title" id="dashPageTitle">Dashboard</span>
        <div class="dash-topbar-actions">
          <a href="../index.php" class="nav-icon-btn" title="Ver tienda"><i class="fas fa-store"></i></a>
          <button class="nav-icon-btn" title="Notificaciones">
            <i class="fas fa-bell"></i>
            <span class="badge-count">4</span>
          </button>
        </div>
      </div>

      <!-- MAIN CONTENT (stats + tables) -->
      <div class="dash-content" id="dashContent">
        <!-- STATS -->
        <div class="dash-stats-grid">
          <div class="dash-stat-card">
            <div class="dash-stat-icon accent-stat-icon">
              <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="dash-stat-value"><?= number_format($pedidos_mes) ?></div>
            <div class="dash-stat-label">Pedidos este mes</div>
            <div class="dash-stat-change change-up">
              <i class="fas fa-arrow-up"></i> +12.5% vs mes anterior
            </div>
            <div class="mini-chart">
              <div class="mini-bar h-40p"></div>
              <div class="mini-bar h-60p"></div>
              <div class="mini-bar h-45p"></div>
              <div class="mini-bar h-80p"></div>
              <div class="mini-bar h-55p"></div>
              <div class="mini-bar accent h-100p"></div>
            </div>
          </div>
          <div class="dash-stat-card">
            <div class="dash-stat-icon cyan-stat-icon">
              <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="dash-stat-value">$<?= number_format($ingresos_mes) ?> COP</div>
            <div class="dash-stat-label">Ingresos este mes</div>
            <div class="dash-stat-change change-up">
              <i class="fas fa-arrow-up"></i> +8.3% vs mes anterior
            </div>
            <div class="mini-chart">
              <div class="mini-bar h-50p"></div>
              <div class="mini-bar h-70p"></div>
              <div class="mini-bar h-40p"></div>
              <div class="mini-bar h-90p"></div>
              <div class="mini-bar h-65p"></div>
              <div class="mini-bar accent h-85p"></div>
            </div>
          </div>
          <div class="dash-stat-card">
            <div class="dash-stat-icon success-stat-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="dash-stat-value"><?= number_format($total_usuarios) ?></div>
            <div class="dash-stat-label">Usuarios totales</div>
            <div class="dash-stat-change change-up">
              <i class="fas fa-arrow-up"></i> +5.1% esta semana
            </div>
            <div class="mini-chart">
              <div class="mini-bar mini-bar-h30"></div>
              <div class="mini-bar mini-bar-h55"></div>
              <div class="mini-bar mini-bar-h70"></div>
              <div class="mini-bar mini-bar-h60"></div>
              <div class="mini-bar mini-bar-h80"></div>
              <div class="mini-bar accent mini-bar-h95"></div>
            </div>
          </div>
          <div class="dash-stat-card">
            <div
              class="dash-stat-icon red-stat-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="dash-stat-value">4.87</div>
            <div class="dash-stat-label">Calificación promedio</div>
            <div class="dash-stat-change change-down">
              <i class="fas fa-arrow-down"></i> -0.04 vs mes anterior
            </div>
            <div class="mini-chart">
              <div class="mini-bar accent h-95p"></div>
              <div class="mini-bar h-90p"></div>
              <div class="mini-bar accent h-88p" data-height="88%"></div>
              <!-- Note: add .h-88p if needed -->
              <div class="mini-bar h-92p"></div>
              <div class="mini-bar h-85p"></div>
              <div class="mini-bar h-87p"></div>
            </div>
          </div>
        </div>

        <!-- RECENT ORDERS + TOP PRODUCTS -->
        <div class="dash-grid-2">
          <div class="dash-card">
            <div class="dash-card-title">
              Pedidos recientes
              <button class="dash-nav-item dash-link-muted-sm" data-section="pedidos"> 
                <p >Ver todos →</p>
              </button>
            </div>
            <div class="dash-scroll-x">
              <table class="dash-table">
                <thead>
                  <tr>
                    <th>#Pedido</th>
                    <th>Cliente</th>
                    <th>Producto</th>
                    <th>Total</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Muestra pedidos recientes: resuelve cliente y etiqueta de estado según ids del modelo
                  while ($fila = mysqli_fetch_assoc($pedidos_recientes_consulta)):
                    $usuario = consultar_usuarios_id($fila['usuario_id']);
                    $estado_nombre = 'Pendiente';
                    if ($fila['estado_id'] == 1) $estado_nombre = 'Completado';
                    elseif ($fila['estado_id'] == 3) $estado_nombre = 'Cancelado';
                  ?>
                    <tr>
                      <td class="dash-td-muted">#<?= $fila['id_pedido'] ?></td>
                      <td>
                        <div class="dash-row-user gap-8">
                          <div class="user-row-avatar"><?= isset($usuario['username']) ? substr($usuario['username'], 0, 1) : 'NA' ?></div>
                          <?= isset($usuario['username']) ? $usuario['username'] : 'usuario no encontrado' ?>
                        </div>
                      </td>
                      <td>Producto</td>
                      <td class="dash-td-accent-strong">$<?= number_format($fila['total']) ?></td>
                      <td><span class="dash-status status-<?= $fila['estado_id'] == 1 ? 'completed' : ($fila['estado_id'] == 3 ? 'cancelled' : 'pending') ?>"><?= $estado_nombre ?></span></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-card-title">Productos más vendidos</div>
            <div class="dash-list-col gap-12">
              <?php
              // Ranking desde productos_mas_vendidos(): unidades y subtotales agregados
              while ($prod = mysqli_fetch_assoc($top_productos)): ?>
                <div class="dash-row-user gap-12">
                  <img
                    src="<?= $prod['imagen_principal'] ?: 'https://via.placeholder.com/60x60?text=? ' ?>"
                    class="dash-top-product-img"
                    alt="<?= $prod['nombre'] ?>" />
                  <div class="dash-flex-1-min0">
                    <div class="dash-top-product-name">
                      <?= $prod['nombre'] ?>
                    </div>
                    <div class="dash-top-product-meta">
                      <?= $prod['unidades'] ?> unidades
                    </div>
                  </div>
                  <div class="dash-top-product-price">
                    $<?= number_format($prod['ventas_total'] ?? 0) ?>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          </div>
        </div>

        <!-- USER TABLE PREVIEW -->
        <div class="dash-card">
          <div class="dash-card-title">
            Usuarios recientes
            <button
              class="dash-nav-item dash-nav-item-compact"
              data-section="usuarios">
              Ver gestión completa →
            </button>
          </div>
          <div class="dash-scroll-x">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Correo</th>
                  <th>Registrado</th>
                  <th>Pedidos</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Vista previa de consultar_usuarios_recientes(); columnas «Pedidos»/estado son placeholders en UI
                while ($filas = mysqli_fetch_assoc($consulta)): ?>
                  <tr>
                    <td>
                      <div class="dash-row-user gap-10">
                        <div class="user-row-avatar"><?= isset($filas['username']) ? substr($filas['username'], 0, 1) : 'NA' ?></div>
                        <strong><?= $filas['username'] ?>.</strong>
                      </div>
                    </td>
                    <td class="dash-td-muted"><?= $filas['email'] ?></td>
                    <td class="dash-td-muted"><?= $filas['creado_en'] ?></td>
                    <td><?= pedidos_por_usuario($filas['id_usuario']) ?></td>
                    <td>
                      <span class="dash-status <?= $resultado = $filas['activo'] == 1 ? 'status-completed' : 'status-pending'; ?>"><?= $resultado = $filas['activo'] == 1 ? 'Activo' : 'Inactivo';  ?></span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- /#dashContent -->

      <!-- IFRAME USUARIOS -->
      <div id="dashIframeUsuarios" class="dash-iframe-wrap is-hidden">
        <div class="dash-iframe-card">
          <div class="dash-iframe-head">
            <i class="fas fa-users dash-iframe-icon"></i>
            <span class="dash-iframe-title">Gestión de Usuarios</span>
            <span class="dash-iframe-subtitle">— Panel backend</span>
          </div>
          <iframe class="dash-iframe" src="./php/usuarios/usuario.php" title="Gestión de usuarios"></iframe>
        </div>
      </div>

      <!-- IFRAME PRODUCTOS -->
      <div id="dashIframeProductos" class="dash-iframe-wrap is-hidden">
        <div class="dash-iframe-card">
          <div class="dash-iframe-head">
            <i class="fas fa-gamepad dash-iframe-icon"></i>
            <span class="dash-iframe-title">Gestión de Productos</span>
            <span class="dash-iframe-subtitle">— Panel backend</span>
          </div>
          <iframe class="dash-iframe" src="./php/productos/productos.php" title="Gestión de Productos"></iframe>
        </div>
      </div>

      <!-- IFRAME RESENAS -->
      <div id="dashIframeResenas" class="dash-iframe-wrap is-hidden">
        <div class="dash-iframe-card">
          <div class="dash-iframe-head">
            <i class="fas fa-star dash-iframe-icon"></i>
            <span class="dash-iframe-title">Gestión de Reseñas</span>
            <span class="dash-iframe-subtitle">— Panel backend</span>
          </div>
          <iframe class="dash-iframe" src="./php/resenas/resenas.php" title="Gestión de Reseñas"></iframe>
        </div>
      </div>

      <!-- IFRAME PEDIDOS -->
      <div id="dashIframePedidos" class="dash-iframe-wrap is-hidden">
        <div class="dash-iframe-card">
          <div class="dash-iframe-head">
            <i class="fas fa-box dash-iframe-icon"></i>
            <span class="dash-iframe-title">Gestión de Pedidos</span>
            <span class="dash-iframe-subtitle">— Panel backend</span>
          </div>
          <iframe class="dash-iframe" src="./php/pedidos/pedidos.php" title="Gestión de Pedidos"></iframe>
        </div>
      </div>

      <!-- IFRAME OPINIONES -->
      <div id="dashIframeOpiniones" class="dash-iframe-wrap is-hidden">
        <div class="dash-iframe-card">
          <div class="dash-iframe-head">
            <i class="fas fa-comment dash-iframe-icon"></i>
            <span class="dash-iframe-title">Gestión de Opiniones</span>
            <span class="dash-iframe-subtitle">— Panel backend</span>
          </div>
          <iframe class="dash-iframe" src="./php/opiniones/opiniones.php" title="Gestión de Opiniones"></iframe>
        </div>
      </div>
    </div>
    <!-- /.dashboard-main -->
  </div>
  <!-- /.dashboard-wrapper -->

  <script type="module" src="../js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>