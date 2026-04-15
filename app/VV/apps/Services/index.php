<?php
include('../ui/main_head.php');

$s    = param('s');
$c    = param('c');
$tipo = param('tipo');

$categorias_servicios = get_all_categorias_servicios();
$servicios = get_active_servicios($s, $c, $tipo);
?>

<!-- Toolbar -->
<div class="container vv-toolbar">
  <div class="row">
    <div class="col-12">
      <div class="vv-toolbar-row">

        <!-- Filtros izquierda -->
        <div class="vv-toolbar-left" style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">

          <!-- Búsqueda -->
          <div class="input-group vv-searchgroup" style="flex:1; min-width:180px;">
            <input type="text" id="buscar" class="form-control" placeholder="Buscar Servicio" value="<?php echo htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); ?>">
            <button class="btn btn-outline-secondary" onclick="SearchTema()" type="button">
              <i class="fa fa-search"></i>
            </button>
          </div>

          <!-- Categoría -->
          <select id="filtroCategoria" class="form-control" style="min-width:160px; height:40px;" onchange="filtrarServicios()">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias_servicios as $cat): ?>
              <option value="<?php echo (int)$cat->vars['id']; ?>"
                <?php if ((string)$c === (string)$cat->vars['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($cat->vars['nombre'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <!-- Tipo -->
          <select id="filtroTipo" class="form-control" style="min-width:140px; height:40px;" onchange="filtrarServicios()">
            <option value="">Todos los tipos</option>
            <option value="int" <?php if ($tipo === 'int') echo 'selected'; ?>>Vecinos</option>
            <option value="ext" <?php if ($tipo === 'ext') echo 'selected'; ?>>Local</option>
          </select>

        </div>

        <!-- Crear -->
        <div class="vv-toolbar-right">
          <form action="new_topic.php" method="get" style="margin:0;">
            <button class="btn btn-success vv-btn-create" type="submit">Crear Nuevo Servicio</button>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<section class="content">

  <div class="container">
    <div class="row">
      <div class="col-lg-8 breadcrumbf">
        <?php if ($s != "" || $c != "" || $tipo != ""): ?>
          <a href="index.php">Inicio</a> <span class="diviver"></span>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] > 1): ?>
          <a href="pending.php">Pendientes</a> <span class="diviver"></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-lg-8 col-md-8">
        <h3>SERVICIOS / PROVEEDORES</h3>

        <?php if (!empty($servicios)): ?>
          <?php foreach ($servicios as $servicio): ?>
            <div class="post">
              <div class="wrap-ut pull-left">
                <div class="userinfo pull-left">
                  <div class="circle"><?php echo htmlspecialchars($servicio->vars['info']->vars['filial'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <?php echo htmlspecialchars($servicio->vars['info']->vars['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                  <div class="icons">
                    <?php if (!empty($categorias_servicios[$servicio->vars['id_categoria']])): ?>
                      <span class="label label-info">
                        <?php echo htmlspecialchars($categorias_servicios[$servicio->vars['id_categoria']]->vars['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    <?php endif; ?>
                    <span class="label label-<?php echo ($servicio->vars['tipo'] === 'ext') ? 'warning' : 'default'; ?>" style="margin-left:4px;">
                      <?php echo ($servicio->vars['tipo'] === 'ext') ? 'Local' : 'Vecinos'; ?>
                    </span>
                  </div>
                </div>
                <div class="posttext pull-left">
                  <h2>
                    <a href="topic.php?t=<?php echo (int)$servicio->vars['id']; ?>">
                      <?php echo htmlspecialchars($servicio->vars['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <a href="topic.php?t=<?php echo (int)$servicio->vars['id']; ?>"
                       title="Ver más"
                       style="margin-left:8px; color:#aaa; font-size:16px; vertical-align:middle; text-decoration:none;"
                       onmouseover="this.style.color='#428bca'" onmouseout="this.style.color='#aaa'">
                      <i class="fa fa-eye"></i>
                    </a>
                  </h2>
                  <p><?php echo htmlspecialchars(mb_substr($servicio->vars['detalle'], 0, 200), ENT_QUOTES, 'UTF-8'); ?><?php if (mb_strlen($servicio->vars['detalle']) > 200) echo '...'; ?></p>
                  <?php if (!empty($servicio->vars['telefono'])): ?>
                    <?php $wa_idx = preg_replace('/\D/', '', $servicio->vars['telefono']); ?>
                    <a href="https://wa.me/506<?php echo $wa_idx; ?>" target="_blank" rel="noopener"
                       style="display:inline-flex; align-items:center; gap:5px; color:#25D366;
                              font-size:13px; font-weight:600; text-decoration:none; margin-top:4px;">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                        <path fill="#25D366" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                      </svg>
                      <?php echo htmlspecialchars($servicio->vars['telefono'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  <?php endif; ?>
                </div>
                <div class="clearfix"></div>
              </div>

              <div class="postinfo pull-left">
                <div class="comments">
                  <div class="commentbg">
                    <?php echo count($servicio->vars['respuestas']); ?>
                    <div class="mark"></div>
                  </div>
                </div>
                <div class="time"><i class="fa fa-clock-o"></i> <?php echo htmlspecialchars($servicio->vars['fecha'], ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div class="clearfix"></div>
            </div>
          <?php endforeach; ?>

        <?php else: ?>
          <div class="post">
            <div class="wrap-ut pull-left">
              <div class="posttext pull-left">
                <h2>No hay servicios disponibles</h2>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
          </div>
        <?php endif; ?>

      </div>

      <?php include('../ui/partials/side.php'); ?>

    </div>
  </div>

</section>

<script>
function filtrarServicios() {
  var cat  = document.getElementById('filtroCategoria').value;
  var tipo = document.getElementById('filtroTipo').value;
  var s    = (document.getElementById('buscar') || {}).value || '';
  var url  = 'index.php?';
  if (s)    url += 's='    + encodeURIComponent(s)    + '&';
  if (cat)  url += 'c='    + encodeURIComponent(cat)  + '&';
  if (tipo) url += 'tipo=' + encodeURIComponent(tipo) + '&';
  window.location.href = url.replace(/[&?]$/, '');
}
</script>

<?php include('../ui/partials/footer.php'); ?>
