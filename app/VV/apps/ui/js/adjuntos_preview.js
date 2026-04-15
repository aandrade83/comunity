(function () {
  function ensureJq(fn) {
    if (typeof window.jQuery === "undefined") return;
    fn(window.jQuery);
  }

  ensureJq(function ($) {
    if (!$.fn.magnificPopup) return;

    // =========================
    // PDF (delegado via Magnific — iframe)
    // =========================
    $(document).magnificPopup({
      delegate: ".vv-attach-pdf",
      type: "iframe",
      iframe: {
        patterns: {
          pdf: { index: ".pdf", src: "%id%" }
        },
        srcAction: "iframe_src"
      }
    });

    // =========================
    // OTROS ARCHIVOS (fallback — nueva pestaña)
    // =========================
    $(document).on("click", ".vv-attach-file", function (e) {
      e.preventDefault();
      window.open($(this).attr("href"), "_blank");
    });
  });
})();
