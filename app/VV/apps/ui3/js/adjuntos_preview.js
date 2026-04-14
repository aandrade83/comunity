(function () {
  function ensureJq(fn) {
    if (typeof window.jQuery === "undefined") return;
    fn(window.jQuery);
  }

  ensureJq(function ($) {
    // Si magnific no está cargado, no hacemos nada (no rompemos la página)
    if (!$.fn.magnificPopup) return;

    // =========================
    // IMÁGENES (delegado)
    // =========================
    $(document).magnificPopup({
      delegate: ".vv-attach-img",
      type: "image",
      gallery: { enabled: true },
      image: {
        titleSrc: function (item) {
          const title = item.el.attr("title") || item.el.data("title") || "";
          const dl = item.el.attr("data-download") || item.el.attr("href") || item.src;
          const safeTitle = $("<div/>").text(title).html();

          return (
            safeTitle +
            ' <a href="' + dl + '" download style="margin-left:10px; color:#fff; text-decoration:underline;">Descargar</a>'
          );
        }
      },
      callbacks: {
        // Si querés evitar que se mezclen galerías entre bloques distintos,
        // necesitás envolver cada set en un contenedor y filtrar aquí.
        // (Por ahora, habilita galería global de todas las .vv-attach-img en la página.)
      }
    });

    // =========================
    // PDF (delegado)
    // =========================
    $(document).magnificPopup({
      delegate: ".vv-attach-pdf",
      type: "iframe",
      iframe: {
        patterns: {
          pdf: {
            index: ".pdf",
            src: "%id%"
          }
        },
        srcAction: "iframe_src"
      }
    });

    // =========================
    // OTROS ARCHIVOS (fallback)
    // =========================
    $(document).on("click", ".vv-attach-file", function (e) {
      // Para otros tipos, solo abrimos en otra pestaña (o dejás el link normal)
      // Si querés forzar descarga, mantené el atributo download en el <a>.
       e.preventDefault(); window.open($(this).attr("href"), "_blank");
    });
  });
})();
