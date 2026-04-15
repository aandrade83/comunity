/**
 * VV Lightbox — vanilla JS, sin dependencias
 * Activa en cualquier <a class="vv-attach-img" href="URL"> de la página
 * Flechas ← → para navegar, ESC para cerrar, click en fondo para cerrar
 */
(function () {
  var _imgs = [];
  var _idx  = 0;
  var _lb   = null;

  function buildDOM() {
    if (document.getElementById('vv-lb')) return;
    var html = [
      '<div id="vv-lb" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;',
      'background:rgba(0,0,0,0.92);z-index:99999;align-items:center;justify-content:center;flex-direction:column;">',

        // Botón cerrar
        '<button id="vv-lb-close" title="Cerrar" style="position:absolute;top:14px;right:18px;',
        'background:none;border:none;color:#fff;font-size:34px;line-height:1;cursor:pointer;z-index:2;',
        'padding:4px 10px;opacity:.8;">&times;</button>',

        // Flecha izquierda
        '<button id="vv-lb-prev" title="Anterior" style="position:absolute;left:12px;top:50%;',
        'transform:translateY(-50%);background:rgba(255,255,255,.15);border:none;color:#fff;',
        'font-size:38px;line-height:1;cursor:pointer;padding:8px 16px;border-radius:4px;',
        'display:none;z-index:2;">&#8249;</button>',

        // Flecha derecha
        '<button id="vv-lb-next" title="Siguiente" style="position:absolute;right:12px;top:50%;',
        'transform:translateY(-50%);background:rgba(255,255,255,.15);border:none;color:#fff;',
        'font-size:38px;line-height:1;cursor:pointer;padding:8px 16px;border-radius:4px;',
        'display:none;z-index:2;">&#8250;</button>',

        // Contenido
        '<div id="vv-lb-inner" style="max-width:92vw;max-height:92vh;text-align:center;pointer-events:none;">',
          '<img id="vv-lb-img" src="" alt="" style="max-width:92vw;max-height:82vh;',
          'object-fit:contain;border-radius:4px;display:block;margin:auto;pointer-events:auto;">',
          '<div id="vv-lb-caption" style="color:#ddd;margin-top:8px;font-size:13px;"></div>',
          '<div id="vv-lb-counter" style="color:#888;margin-top:3px;font-size:12px;"></div>',
        '</div>',

      '</div>'
    ].join('');

    var wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    document.body.appendChild(wrapper.firstChild);
  }

  function open(idx) {
    _idx = idx;
    render();
    _lb.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function close() {
    _lb.style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('vv-lb-img').src = '';
  }

  function prev() {
    _idx = (_idx - 1 + _imgs.length) % _imgs.length;
    render();
  }

  function next() {
    _idx = (_idx + 1) % _imgs.length;
    render();
  }

  function render() {
    if (!_imgs.length) return;
    var item = _imgs[_idx];
    var img  = document.getElementById('vv-lb-img');
    img.src  = item.url;
    img.alt  = item.title;
    document.getElementById('vv-lb-caption').textContent = item.title;
    document.getElementById('vv-lb-counter').textContent =
      _imgs.length > 1 ? (_idx + 1) + ' / ' + _imgs.length : '';
    document.getElementById('vv-lb-prev').style.display = _imgs.length > 1 ? 'block' : 'none';
    document.getElementById('vv-lb-next').style.display = _imgs.length > 1 ? 'block' : 'none';
  }

  function gatherAndOpen(clickedUrl) {
    _imgs = [];
    document.querySelectorAll('a.vv-attach-img').forEach(function (el) {
      var url = el.getAttribute('href') || '';
      if (!url) return;
      _imgs.push({ url: url, title: el.getAttribute('title') || '' });
    });
    _idx = 0;
    for (var i = 0; i < _imgs.length; i++) {
      if (_imgs[i].url === clickedUrl) { _idx = i; break; }
    }
    open(_idx);
  }

  function init() {
    buildDOM();
    _lb = document.getElementById('vv-lb');

    // Cerrar con X
    document.getElementById('vv-lb-close').addEventListener('click', function (e) {
      e.stopPropagation();
      close();
    });

    // Flechas
    document.getElementById('vv-lb-prev').addEventListener('click', function (e) {
      e.stopPropagation();
      prev();
    });
    document.getElementById('vv-lb-next').addEventListener('click', function (e) {
      e.stopPropagation();
      next();
    });

    // Click en el fondo oscuro cierra
    _lb.addEventListener('click', function (e) {
      if (e.target === _lb) close();
    });

    // Teclado
    document.addEventListener('keydown', function (e) {
      if (!_lb || _lb.style.display === 'none') return;
      var k = e.key || e.keyCode;
      if (k === 'ArrowLeft'  || k === 37) prev();
      if (k === 'ArrowRight' || k === 39) next();
      if (k === 'Escape'     || k === 27) close();
    });

    // Interceptar clicks en .vv-attach-img (delegado en document)
    document.addEventListener('click', function (e) {
      // Soporta click en el <a> o en el <img> dentro del <a>
      var a = null;
      if (e.target.tagName === 'A' && e.target.classList.contains('vv-attach-img')) {
        a = e.target;
      } else if (e.target.parentElement &&
                 e.target.parentElement.tagName === 'A' &&
                 e.target.parentElement.classList.contains('vv-attach-img')) {
        a = e.target.parentElement;
      }
      if (!a) return;

      e.preventDefault();
      e.stopPropagation();
      gatherAndOpen(a.getAttribute('href') || '');
    });
  }

  // Esperar a que el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
