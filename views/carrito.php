<?php
session_start();
require_once __DIR__ . '/php/carrito/carritoModel.php';

function carrito_fmt_cop($valor)
{
  return '$' . number_format((float) $valor, 0, ',', '.');
}

$items = carrito_items_actuales();
$cantidad_total = carrito_contar_items_actuales();
$subtotal = 0.0;
foreach ($items as $it) {
  $subtotal += (float) ($it['subtotal'] ?? 0);
}
$descuento = isset($_SESSION['carrito_descuento']) ? (float) $_SESSION['carrito_descuento'] : 0.0;
if ($descuento > $subtotal) {
  $descuento = $subtotal;
}
$total = $subtotal - $descuento;
$codigo_aplicado = $_SESSION['carrito_codigo'] ?? '';

$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Carrito — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<?php
if ($logueo === 1){
  include './plantillas/navbar_admin.php';
} elseif ($logueo >= 2 ){
  include './plantillas/navbar_user.php';
} else {
  include './plantillas/navbar_publico.php';

}
?>

<!-- Navegación móvil auxiliar (mismo patrón que otras vistas públicas) -->
<div class="mobile-nav" id="mobileNav">
  <div class="mobile-nav-overlay"></div>
  <div class="mobile-nav-drawer">
    <button class="mobile-nav-close" id="mobileNavClose"><i class="fas fa-times"></i></button>
<a href="/index.php">Inicio</a><a href="/views/catalogo.php">Catálogo</a>
  </div>
</div>

<!-- Cabecera de página con migas de pan -->
<div class="page-hero pt-0 pb-20">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="/index.php">Inicio</a><span>/</span><span class="text-white">Carrito de compras</span>
    </div>
    <h1 class="page-hero-title">Tu carrito</h1>
    <p class="page-hero-sub"><span id="cartItemsCount"><?php echo (int) $cantidad_total; ?></span> productos seleccionados</p>
  </div>
</div>

<div class="container">
  <div class="cart-layout">

    <!-- ITEMS -->
    <div>
      <div class="cart-table-header">
        <span>Producto</span>
        <span>Precio</span>
        <span>Cantidad</span>
        <span>Total</span>
        <span></span>
      </div>

      <?php if (empty($items)): ?>
        <div class="text-muted py-3" id="cartEmptyMsg">El carrito está vacío</div>
      <?php else: ?>
        <?php foreach ($items as $it): ?>
          <div class="cart-item" data-item-id="<?php echo htmlspecialchars((string) $it['id_item'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="cart-item-info">
              <img src="<?php echo htmlspecialchars($it['imagen'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="cart-item-img" alt="" />
              <div>
                <div class="cart-item-name"><?php echo htmlspecialchars($it['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                <?php if (!empty($it['opcion'])): ?><div class="cart-item-platform"><?php echo htmlspecialchars($it['opcion'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
              </div>
            </div>
            <div class="cart-item-price" data-price="<?php echo (float) $it['precio']; ?>"><?php echo carrito_fmt_cop($it['precio']); ?></div>
            <div>
              <div class="qty-control">
                <button class="qty-btn qty-minus h-36">−</button>
                <input type="number" class="qty-input h-36" value="<?php echo (int) $it['cantidad']; ?>" min="1" />
                <button class="qty-btn qty-plus h-36">+</button>
              </div>
            </div>
            <div class="cart-item-total"><?php echo carrito_fmt_cop($it['subtotal']); ?></div>
            <button class="cart-remove"><i class="fas fa-times"></i></button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">
<a href="./catalogo.php" class="btn-secondary d-inline-flex align-items-center gap-2 text-decoration-none">
          <i class="fas fa-arrow-left"></i> Seguir comprando
        </a>
      </div>
    </div>

    <!-- ORDER SUMMARY -->
    <div>
      <div class="order-summary">
        <div class="order-summary-title">Resumen del pedido</div>
        <div class="summary-row"><span>Subtotal (<span id="summaryItemCount"><?php echo (int) $cantidad_total; ?></span> ítems)</span><span id="summarySubtotal"><?php echo carrito_fmt_cop($subtotal); ?></span></div>
        <div class="summary-row"><span>Envío</span><span class="fg-success">Gratis</span></div>
        <div class="summary-row"><span>Descuento aplicado</span><span class="fg-red" id="summaryDiscount">−<?php echo carrito_fmt_cop($descuento); ?></span></div>
        <div class="summary-row total"><span>Total</span><span id="summaryTotal"><?php echo carrito_fmt_cop($total); ?></span></div>

        <div class="promo-input-row">
          <input type="text" class="promo-input" placeholder="Código promocional" value="<?php echo htmlspecialchars($codigo_aplicado, ENT_QUOTES, 'UTF-8'); ?>" />
          <button class="btn-apply">Aplicar</button>
        </div>
        <div id="promoMessage" class="small mt-2"></div>

        <button class="btn-checkout">
          <i class="fas fa-lock"></i> &nbsp;Proceder al pago
        </button>

        <div class="d-flex align-items-center justify-content-center gap-2 mt-3" style="font-size:12px; color:var(--muted)">
          <i class="fas fa-shield-alt"></i> Pago 100% seguro con SSL
        </div>

        <div class="d-flex gap-2 justify-content-center mt-2" style="font-size:11px; color:var(--muted)">
          <span>💳 Visa</span><span>💳 Mastercard</span><span>💳 PSE</span><span>💳 Nequi</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php 
  include './plantillas/footer.php';
?>

<script type="module" src="../js/main.js"></script>
<script>
  (function () {
    var msg = document.getElementById('promoMessage');
    if (!msg) return;
    window.zpCartUi = {
      setMessage: function (text, ok) {
        msg.textContent = text || '';
        msg.className = 'small mt-2 ' + (ok ? 'fg-success' : 'fg-red');
      }
    };
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
