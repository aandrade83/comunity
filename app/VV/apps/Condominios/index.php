<?php
include('../ui/main_head.php');

// Solo rol 3
if (!$superadmin) {
    header('Location: ' . BASE_URL . '/apps/Forum/index.php');
    exit;
}

db_connect('master');
$db = $GLOBALS['conn_db']->mysqli_connector;

$rows = [];
$result = $db->query(
    'SELECT
        c.id,
        c.nombre,
        c.apellido,
        c.email,
        c.telefono,
        c.rol,
        c.filial,
        c.email_flag,
        c.created_at,
        MAX(l.date) AS last_login
     FROM condominos c
     LEFT JOIN usuarios u ON u.correo = c.email AND u.filial = c.filial
     LEFT JOIN logs l ON l.user = u.id AND l.data LIKE "Login Succesfully%"
     GROUP BY c.id
     ORDER BY c.filial ASC'
);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
#condo-table-wrap {
    overflow-x: auto;
}
#condo-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
#condo-table th, #condo-table td {
    padding: 8px 10px;
    border: 1px solid #ddd;
    white-space: nowrap;
    vertical-align: middle;
}
#condo-table thead th {
    background: #f5f5f5;
    font-weight: 700;
    cursor: pointer;
    user-select: none;
    position: relative;
}
#condo-table thead th:hover { background: #e8e8e8; }
#condo-table thead th .sort-icon {
    font-size: 10px;
    margin-left: 4px;
    opacity: 0.4;
}
#condo-table thead th.sort-asc .sort-icon::after  { content: " ▲"; opacity: 1; }
#condo-table thead th.sort-desc .sort-icon::after { content: " ▼"; opacity: 1; }
#condo-table thead th:not(.sort-asc):not(.sort-desc) .sort-icon::after { content: " ⇅"; }
#condo-table tbody tr:nth-child(even) { background: #fafafa; }
#condo-table tbody tr:hover { background: #f0f6ff; }
.email-ok  { color: #27ae60; font-size: 16px; font-weight: bold; }
.email-no  { color: #e74c3c; font-size: 16px; font-weight: bold; }
.btn-edit-row {
    background: none;
    border: none;
    color: #888;
    cursor: pointer;
    font-size: 15px;
    padding: 2px 6px;
}
.btn-edit-row:hover { color: #3498db; }
.editing-row td { background: #fffbe6 !important; }
.editing-row input, .editing-row select {
    width: 100%;
    padding: 2px 4px;
    font-size: 12px;
    border: 1px solid #bbb;
    border-radius: 3px;
    box-sizing: border-box;
}
.btn-save-row, .btn-cancel-row {
    padding: 2px 8px;
    font-size: 12px;
    border-radius: 3px;
    border: none;
    cursor: pointer;
    margin: 1px;
}
.btn-save-row   { background: #27ae60; color: #fff; }
.btn-cancel-row { background: #aaa;    color: #fff; }

@media print {
    .headernav, .breadcrumbf, #btn-export-pdf, .no-print,
    th:last-child, td:last-child { display: none !important; }
    body { font-size: 11px; }
    #condo-table th, #condo-table td { padding: 4px 6px; }
}
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-12 breadcrumbf no-print">
                <a href="<?= BASE_URL ?>/apps/Forum/index.php">Inicio</a>
                <span class="diviver"></span>
                CONDOMINIOS
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;" class="no-print">
                    <h3 style="margin:0;">Condominos</h3>
                    <button id="btn-export-pdf" class="btn btn-danger btn-sm">
                        <i class="fa fa-file-pdf-o"></i> Exportar PDF
                    </button>
                </div>

                <div id="condo-table-wrap">
                    <table id="condo-table">
                        <thead>
                            <tr>
                                <th data-col="0">  # <span class="sort-icon"></span></th>
                                <th data-col="1">  Nombre <span class="sort-icon"></span></th>
                                <th data-col="2">  Apellido <span class="sort-icon"></span></th>
                                <th data-col="3">  Email <span class="sort-icon"></span></th>
                                <th data-col="4">  Teléfono <span class="sort-icon"></span></th>
                                <th data-col="5">  Rol <span class="sort-icon"></span></th>
                                <th data-col="6">  Filial <span class="sort-icon"></span></th>
                                <th data-col="7">  Email <i class="fa fa-envelope"></i><span class="sort-icon"></span></th>
                                <th data-col="8">  Last Login <span class="sort-icon"></span></th>
                                <th class="no-print" style="cursor:default;">  Editar</th>
                            </tr>
                        </thead>
                        <tbody id="condo-tbody">
                        <?php foreach ($rows as $r): ?>
                            <tr data-id="<?= (int)$r['id'] ?>">
                                <td><?= (int)$r['id'] ?></td>
                                <td><?= htmlspecialchars($r['nombre'],   ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['apellido'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['email'],    ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($r['rol'],      ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)$r['filial'] ?></td>
                                <td style="text-align:center;">
                                    <?php if ((int)$r['email_flag'] === 1): ?>
                                        <span class="email-ok" title="Activo">✓</span>
                                    <?php else: ?>
                                        <span class="email-no" title="Inactivo">✗</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $r['last_login'] ? htmlspecialchars($r['last_login'], ENT_QUOTES, 'UTF-8') : '<span style="color:#bbb;">—</span>' ?></td>
                                <td class="no-print" style="text-align:center;">
                                    <button class="btn-edit-row" title="Editar"><i class="fa fa-pencil"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</section>

<script>
(function () {

  // ── Sorting ────────────────────────────────────────────────────────────────
  var tbody = document.getElementById('condo-tbody');
  var headers = document.querySelectorAll('#condo-table thead th[data-col]');
  var sortCol = -1, sortAsc = true;

  headers.forEach(function (th) {
    th.addEventListener('click', function () {
      var col = parseInt(this.dataset.col, 10);
      if (sortCol === col) {
        sortAsc = !sortAsc;
      } else {
        sortCol = col;
        // Last Login defaults to descending (most recent first) on first click
        sortAsc = (col === DATE_COL) ? false : true;
      }
      headers.forEach(function (h) { h.classList.remove('sort-asc', 'sort-desc'); });
      this.classList.add(sortAsc ? 'sort-asc' : 'sort-desc');
      sortTable(col, sortAsc);
    });
  });

  var DATE_COL = 8; // Last Login column index

  function sortTable(col, asc) {
    var rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort(function (a, b) {
      var at = a.cells[col] ? a.cells[col].textContent.trim() : '';
      var bt = b.cells[col] ? b.cells[col].textContent.trim() : '';

      if (col === DATE_COL) {
        // Empty / dash always goes to the bottom regardless of sort direction
        var aEmpty = (at === '' || at === '—' || at === '-');
        var bEmpty = (bt === '' || bt === '—' || bt === '-');
        if (aEmpty && bEmpty) return 0;
        if (aEmpty) return 1;
        if (bEmpty) return -1;
        var da = new Date(at), db = new Date(bt);
        var cmp = da - db;
        return asc ? cmp : -cmp;
      }

      var an = parseFloat(at), bn = parseFloat(bt);
      var cmp = (!isNaN(an) && !isNaN(bn)) ? (an - bn) : at.localeCompare(bt, 'es', { sensitivity: 'base' });
      return asc ? cmp : -cmp;
    });
    rows.forEach(function (r) { tbody.appendChild(r); });
  }

  // ── Inline editing ─────────────────────────────────────────────────────────
  // Editable cols: 1=nombre, 2=apellido, 3=email, 4=telefono, 5=rol, 6=filial, 7=email_flag
  var EDIT_COLS = {
    1: { type: 'text',   field: 'nombre' },
    2: { type: 'text',   field: 'apellido' },
    3: { type: 'email',  field: 'email' },
    4: { type: 'text',   field: 'telefono' },
    5: { type: 'select', field: 'rol',        options: ['Dueño', 'Inquilino'] },
    6: { type: 'number', field: 'filial' },
    7: { type: 'select', field: 'email_flag', options: [{ val: '1', lbl: '✓ Activo' }, { val: '0', lbl: '✗ Inactivo' }] }
  };

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-edit-row');
    if (btn) { startEdit(btn.closest('tr')); return; }

    var save = e.target.closest('.btn-save-row');
    if (save) { saveRow(save.closest('tr')); return; }

    var cancel = e.target.closest('.btn-cancel-row');
    if (cancel) { cancelEdit(cancel.closest('tr')); return; }
  });

  function startEdit(tr) {
    if (tr.classList.contains('editing-row')) return;
    tr.classList.add('editing-row');

    // Store original HTML
    tr.dataset.origHtml = tr.innerHTML;

    var cells = tr.cells;
    Object.keys(EDIT_COLS).forEach(function (ci) {
      var i = parseInt(ci, 10);
      var def = EDIT_COLS[i];
      var cell = cells[i];
      var val = cell.textContent.trim();

      if (def.type === 'select') {
        var sel = document.createElement('select');
        def.options.forEach(function (opt) {
          var o = document.createElement('option');
          if (typeof opt === 'object') {
            o.value = opt.val;
            o.textContent = opt.lbl;
            // match current display
            if (val === '✓' || val === '✗') {
              o.selected = (val === '✓') ? (opt.val === '1') : (opt.val === '0');
            } else {
              o.selected = (opt.val === val || opt.lbl === val);
            }
          } else {
            o.value = opt;
            o.textContent = opt;
            o.selected = (opt === val);
          }
          sel.appendChild(o);
        });
        cell.textContent = '';
        cell.appendChild(sel);
      } else {
        var inp = document.createElement('input');
        inp.type = def.type;
        inp.value = val;
        cell.textContent = '';
        cell.appendChild(inp);
      }
    });

    // Replace edit button with save/cancel
    var lastCell = cells[cells.length - 1];
    lastCell.innerHTML =
      '<button class="btn-save-row">Guardar</button>' +
      '<button class="btn-cancel-row">Cancelar</button>';
  }

  function cancelEdit(tr) {
    tr.innerHTML = tr.dataset.origHtml;
    tr.classList.remove('editing-row');
    delete tr.dataset.origHtml;
  }

  function saveRow(tr) {
    var id = parseInt(tr.dataset.id, 10);
    var cells = tr.cells;
    var data = { id: id };

    Object.keys(EDIT_COLS).forEach(function (ci) {
      var i = parseInt(ci, 10);
      var def = EDIT_COLS[i];
      var cell = cells[i];
      var inp = cell.querySelector('input, select');
      data[def.field] = inp ? inp.value : cell.textContent.trim();
    });

    var fd = new FormData();
    Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });

    var saveBtn = tr.querySelector('.btn-save-row');
    if (saveBtn) saveBtn.disabled = true;

    fetch('actions.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (resp) {
        if (resp.ok) {
          // Rebuild read-only cells
          cancelEdit(tr); // restore structure
          // Now update displayed values
          var cells2 = tr.cells;
          cells2[1].textContent = data.nombre;
          cells2[2].textContent = data.apellido;
          cells2[3].textContent = data.email;
          cells2[4].textContent = data.telefono;
          cells2[5].textContent = data.rol;
          cells2[6].textContent = data.filial;
          var flag = data.email_flag;
          cells2[7].innerHTML = (flag === '1' || flag === 1)
            ? '<span class="email-ok" title="Activo">✓</span>'
            : '<span class="email-no" title="Inactivo">✗</span>';
        } else {
          if (saveBtn) saveBtn.disabled = false;
          alert(resp.error || 'Error al guardar');
        }
      })
      .catch(function () {
        if (saveBtn) saveBtn.disabled = false;
        alert('Error de comunicación');
      });
  }

  // ── Export PDF (print) ─────────────────────────────────────────────────────
  document.getElementById('btn-export-pdf').addEventListener('click', function () {
    window.print();
  });

})();
</script>

<?php include('../ui/partials/footer.php'); ?>
