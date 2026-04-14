// ============================
// INACTIVIDAD
// ============================
let inactividadTiempo;
let tiempoRestante = 300000; // 5 minutos
let contadorIntervalo;

function cerrarSesion() {
  fetch("logout.php")
    .then(() => {
      window.location.href = "https://lab.lacallecr.com/VV/index.php";
    })
    .catch(() => {});
}

function mostrarAlerta() {
  if (!window.Swal) {
    cerrarSesion();
    return;
  }
  Swal.fire({
    title: "Inactividad detectada",
    text: "Serás redirigido al inicio por inactividad",
    icon: "warning",
    confirmButtonText: "Ok",
  }).then((result) => {
    if (result.isConfirmed) cerrarSesion();
  });
}

function reiniciarTiempo() {
  clearTimeout(inactividadTiempo);
  tiempoRestante = 450000; // 7.5 min
  inactividadTiempo = setTimeout(mostrarAlerta, tiempoRestante);
  iniciarContador();
}

function iniciarContador() {
  clearInterval(contadorIntervalo);
  contadorIntervalo = setInterval(() => {
    tiempoRestante -= 1000;
    if (tiempoRestante <= 0) clearInterval(contadorIntervalo);
  }, 1000);
}

window.addEventListener("load", reiniciarTiempo);
document.addEventListener("mousemove", reiniciarTiempo, { passive: true });
document.addEventListener("keydown", reiniciarTiempo, { passive: true });
document.addEventListener("scroll", reiniciarTiempo, { passive: true });
document.addEventListener("click", reiniciarTiempo, { passive: true });

// ============================
// APP CONTEXT (Forum / Services)
// ============================
function vvDetectApp() {
  const p = (window.location.pathname || "").toLowerCase();

  if (p.includes("/vv/apps/forum/")) {
    return {
      app: "Forum",
      base: "https://lab.lacallecr.com/VV/apps/Forum/",
      api: "https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php",
    };
  }

  if (p.includes("/vv/apps/services/")) {
    return {
      app: "Services",
      base: "https://lab.lacallecr.com/VV/apps/Services/",
      api: "https://lab.lacallecr.com/VV/apps/Services/proccess/actions.php",
    };
  }

  // fallback seguro (no rompe)
  return {
    app: "Unknown",
    base: "https://lab.lacallecr.com/VV/apps/Forum/",
    api: "https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php",
  };
}

const VV = vvDetectApp();

// ============================
// HELPERS
// ============================
function safeTrim(v) {
  return v === undefined || v === null ? "" : String(v).trim();
}

function parseMaybeJSON(resp) {
  if (resp === null || resp === undefined) return null;
  if (typeof resp === "object") return resp;
  const t = String(resp).trim();
  if (!t) return null;
  try {
    return JSON.parse(t);
  } catch (e) {
    return null;
  }
}

function swalError(msg) {
  if (!window.Swal) {
    alert(msg || "Hubo un error");
    return;
  }
  Swal.fire({
    icon: "error",
    title: "Oops...",
    text: msg || "Hubo un error",
  });
}

function swalOk(title, text) {
  if (!window.Swal) return;
  Swal.fire({
    title: title || "¡Listo!",
    text: text || "",
    icon: "success",
    confirmButtonText: "Aceptar",
  });
}

// ============================
// DROPZONE (config global)
// ============================
if (typeof window.Dropzone !== "undefined") {
  Dropzone.autoDiscover = false;
}

// ============================
// DOCUMENT READY
// ============================
if (typeof window.jQuery !== "undefined") {
  jQuery(function ($) {
    // ============================
    // NEW TOPIC (frm_new + dzAdjuntos)
    // ============================
    let dzTopic = null;
    let dzTopicLastOk = null;

    const hasFrmNew = document.getElementById("frm_new") !== null;
    const dzTopicEl = document.querySelector("#dzAdjuntos");

    if (hasFrmNew && dzTopicEl && typeof window.Dropzone !== "undefined") {
      dzTopic = new Dropzone("#dzAdjuntos", {
        url: VV.api + "?ac=topic",
        method: "post",
        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 10,
        addRemoveLinks: true,
        timeout: 120000,
        paramName: "adjuntos",
        maxFilesize: 12,
        acceptedFiles: "image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt",
        dictDefaultMessage: "Arrastra archivos aquí o haz click para seleccionar",
        dictRemoveFile: "Quitar",
      });

      function appendNewTopicFields(fd) {
          fd.append("t", safeTrim($("#title").val()));
          fd.append("c", safeTrim($("#category").val()));
          fd.append("desc", safeTrim($("#desc").val()));

          // ✅ Tipo: soporta radio (name="tipo") y fallback a selects/inputs viejos
          let tipo = "";
          const tipoRadio = $('input[name="tipo"]:checked').val();
          if (tipoRadio !== undefined && tipoRadio !== null) tipo = String(tipoRadio);

          if (!tipo) tipo = safeTrim($("#tipo").val() || "");
          if (!tipo) tipo = safeTrim($("#type").val() || "");

          if (tipo) fd.append("tipo", tipo);
      }

      dzTopic.on("sending", function (file, xhr, formData) {
        appendNewTopicFields(formData);
     
               // ✅ DEBUG: ver qué va al server (solo consola)
        try {
          const dbg = {};
          for (const pair of formData.entries()) {
            const k = pair[0];
            const v = pair[1];
            dbg[k] = (v instanceof File) ? `[File] ${v.name} (${v.size} bytes)` : v;
          }
          console.log("SENDING FormData =>", dbg);
        } catch (e) {
          console.warn("No se pudo leer FormData", e);
        }






      });

      dzTopic.on("sendingmultiple", function (files, xhr, formData) {
        appendNewTopicFields(formData);
      });

      dzTopic.on("success", function (file, resp) {
        const data = parseMaybeJSON(resp);
        if (data && String(data.control) === "1") dzTopicLastOk = data;
      });

      dzTopic.on("successmultiple", function (files, resp) {
        const data = parseMaybeJSON(resp);
        if (data && String(data.control) === "1") dzTopicLastOk = data;
      });

      dzTopic.on("error", function (file, message, xhr) {
        const data =
          parseMaybeJSON(message) || parseMaybeJSON(xhr && xhr.responseText);
        const msg =
          data && (data.error || data.message)
            ? data.error || data.message
            : typeof message === "string"
            ? message
            : "Error al subir";
        swalError(msg);
      });

      dzTopic.on("queuecomplete", function () {
        if (!dzTopicLastOk) return;

        // deshabilitar botón submit si existe
        $("#btn_post").prop("disabled", true);

        swalOk(
          "¡PERFECTO!",
          "Tu Tema fue creado. Una vez aprobado por la comisión va a ser publicado."
        );

        setTimeout(function () {
          window.location.href = VV.base + "index.php";
        }, 1500);
      });
    }

    $("#frm_new")
      .off("submit")
      .on("submit", async function (e) {
        e.preventDefault();

        const title = safeTrim($("#title").val());
        const cat = safeTrim($("#category").val());
        const desc = safeTrim($("#desc").val());

        // opcional (Services)
        const hasTipo = $("#tipo").length > 0 || $("#type").length > 0;
        const tipo = safeTrim($("#tipo").val() || $("#type").val());

        if (!(title.length > 1 && cat && desc.length > 1)) {
          swalError("Todos los campos son obligatorios");
          return;
        }
        if (hasTipo && !tipo) {
          swalError("Debe seleccionar un Tipo");
          return;
        }

        if (dzTopic && dzTopic.getQueuedFiles().length > 0) {
          dzTopicLastOk = null;
          dzTopic.processQueue();
          return;
        }

        // sin archivos
        try {
          const fd = new FormData();
          fd.append("t", title);
          fd.append("c", cat);
          fd.append("desc", desc);
          if (hasTipo && tipo) fd.append("tipo", tipo);

          const r = await fetch(VV.api + "?ac=topic", { method: "POST", body: fd });
          const text = await r.text();
          const data = parseMaybeJSON(text);
          if (!data) throw new Error("Respuesta NO es JSON: " + text);

          if (String(data.control) === "1") {
            $("#btn_post").prop("disabled", true);
            swalOk(
              "¡PERFECTO!",
              "Tu Tema fue creado. Una vez aprobado por la comisión va a ser publicado."
            );
            setTimeout(function () {
              window.location.href = VV.base + "index.php";
            }, 1500);
          } else {
            swalError(data.error || "Hubo un error");
          }
        } catch (err) {
          swalError("A system error was detected");
        }
      });

   
    // ============================
    // REPLY (respuesta en topic)  frm_reply o frm_topic + dzReplyAdjuntos
    // ============================
    let dzReply = null;
    let dzReplyLastOk = null;

    const hasReplyForm =
      document.getElementById("frm_reply") !== null ||
      document.getElementById("frm_topic") !== null;

    let dzReplySelector = null;
    if (document.querySelector("#dzReplyAdjuntos")) dzReplySelector = "#dzReplyAdjuntos";
    else if (hasReplyForm && document.querySelector("#dzAdjuntos")) dzReplySelector = "#dzAdjuntos";

    if (dzReplySelector && typeof window.Dropzone !== "undefined") {
      dzReply = new Dropzone(dzReplySelector, {
        url: VV.api + "?ac=reply",
        method: "post",
        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 10,
        addRemoveLinks: true,
        timeout: 120000,
        paramName: "adjuntos",
        maxFilesize: 12,
        acceptedFiles: "image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt",
        dictDefaultMessage: "Arrastra archivos aquí o haz click para seleccionar",
        dictRemoveFile: "Quitar",
      });

      function appendReplyFields(fd) {
        fd.append("t", safeTrim($("#topic").val()));
        fd.append("reply", safeTrim($("#reply").val()));
      }

      dzReply.on("sending", function (file, xhr, formData) {
        appendReplyFields(formData);
      });

      dzReply.on("sendingmultiple", function (files, xhr, formData) {
        appendReplyFields(formData);
      });

      dzReply.on("success", function (file, resp) {
        const data = parseMaybeJSON(resp);
        if (data && String(data.control) === "1") dzReplyLastOk = data;
      });

      dzReply.on("successmultiple", function (files, resp) {
        const data = parseMaybeJSON(resp);
        if (data && String(data.control) === "1") dzReplyLastOk = data;
      });

      dzReply.on("error", function (file, message, xhr) {
        const data =
          parseMaybeJSON(message) || parseMaybeJSON(xhr && xhr.responseText);
        const msg =
          data && (data.error || data.message)
            ? data.error || data.message
            : typeof message === "string"
            ? message
            : "Error al subir";
        swalError(msg);
      });

      dzReply.on("queuecomplete", function () {
        if (!dzReplyLastOk) return;

        swalOk("¡Genial!", "Su Respuesta ha sido Guardada");

        setTimeout(function () {
          // mejor UX: recargar el mismo topic
          window.location.reload();
        }, 1200);
      });
    }

    $("#frm_reply, #frm_topic")
      .off("submit")
      .on("submit", async function (e) {
        // si no existe textarea reply, no tocamos
        if (!document.getElementById("reply")) return;

        e.preventDefault();

        const tema = safeTrim($("#topic").val());
        const reply = safeTrim($("#reply").val());

        if (!tema || reply.length < 1) {
          swalError("Todos los campos son obligatorios");
          return;
        }

        if (dzReply && dzReply.getQueuedFiles().length > 0) {
          dzReplyLastOk = null;
          dzReply.processQueue();
          return;
        }

        try {
          const fd = new FormData();
          fd.append("t", tema);
          fd.append("reply", reply);

          const r = await fetch(VV.api + "?ac=reply", { method: "POST", body: fd });
          const text = await r.text();
          const data = parseMaybeJSON(text);
          if (!data) throw new Error("Respuesta NO es JSON: " + text);

          if (String(data.control) === "1") {
            swalOk("¡Genial!", "Su Respuesta ha sido Guardada");
            setTimeout(function () {
              window.location.reload();
            }, 1200);
          } else {
            swalError(data.error || "Hubo un error");
          }
        } catch (err) {
          swalError("A system error was detected");
        }
      });

    // =======================
    // SELECT2: Categoría editable (solo Services)
    // =======================
    function initEditableCategorySelect2(selectId, createUrl) {
      if (!$(selectId).length) return;
      if (!$.fn.select2) return;

      const $sel = $(selectId);

      function existsByText(txt) {
        const t = (txt || "").trim().toLowerCase();
        let exists = false;
        $sel.find("option").each(function () {
          if (($(this).text() || "").trim().toLowerCase() === t) exists = true;
        });
        return exists;
      }

      async function createCategory(nombre) {
        const fd = new FormData();
        fd.append("nombre", nombre);

        const r = await fetch(createUrl, { method: "POST", body: fd });
        const text = await r.text();

        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          throw new Error("Respuesta NO es JSON: " + text);
        }

        // esperado: {control:1,id:123} o parecido
        const id = data.id || data.ID || data.Id;
        if ((String(data.control) === "1" || data.control === 1) && id) return id;
        if (id) return id;

        throw new Error(data.error || data.message || "No se pudo crear la categoría");
      }

      $sel.select2({
        placeholder: "Seleccione Categoría",
        width: "100%",
        tags: true,
        createTag: function (params) {
          const term = (params.term || "").trim();
          if (!term) return null;
          if (existsByText(term)) return null;

          return {
            id: "__new__:" + term,
            text: 'Crear: "' + term + '"',
            newTag: true,
            term: term,
          };
        },
        templateSelection: function (data) {
          return data && data.newTag ? data.term : data.text;
        },
      });

      $sel.on("select2:select", async function (e) {
        const data = e.params.data;
        if (!data || !data.newTag) return;

        const nombre = (data.term || "").trim();
        if (!nombre) return;

        try {
          const newId = await createCategory(nombre);

          // quitar option temporal y agregar real con ID real como value
          $sel.find('option[value="' + data.id.replace(/"/g, '\\"') + '"]').remove();
          const opt = new Option(nombre, newId, true, true);
          $sel.append(opt).trigger("change");

          swalOk("Listo", "Categoría creada");
        } catch (err) {
          // limpiar selección si falló
          $sel.find('option[value="' + data.id.replace(/"/g, '\\"') + '"]').remove();
          $sel.val(null).trigger("change");
          swalError(err.message || "No se pudo crear la categoría");
        }
      });
    }

    if (VV.app === "Services") {
      initEditableCategorySelect2(
        "#category",
        "https://lab.lacallecr.com/VV/apps/Services/proccess/actions.php?ac=newCat"
      );
    }
  });
}

// ============================
// FUNCIONES EXISTENTES / GLOBALES
// ============================
function logout() {
  fetch("https://lab.lacallecr.com/VV/apps/login/proccess/actions.php?ac=logout")
    .then((r) => r.json())
    .then(function (data) {
      if (data.login == 1) window.location.href = "https://lab.lacallecr.com/VV/";
    })
    .catch(() => alert("A system error was detected"));
}

function closeTopicControl() {
  var tema = jQuery("#topic").val();
  var reply = jQuery("#reply").val();

  if (reply && reply.length > 1) {
    Swal.fire({
      title: "¿Estás seguro de Cerrar el caso?",
      text: "¡No podrás revertir esto!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí",
      cancelButtonText: "No",
    }).then((result) => {
      if (result.isConfirmed) {
        closeTopic();
        Swal.fire("¡Confirmado!", "La acción ha sido confirmada.", "success");
      } else {
        Swal.fire("Cancelado", "La acción ha sido cancelada.", "error");
      }
    });
  } else {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "Todos los campos son obligatorios",
      footer: '<a href="#">Favor corregir y volver a enviar</a>',
    });
  }
}

function closeTopic() {
  jQuery(".active").hide();
  jQuery(".closed").show();

  var tema = jQuery("#topic").val();
  var reply = jQuery("#reply").val();

  const VV2 = vvDetectApp();

  if (reply && reply.length > 1) {
    fetch(VV2.api + "?ac=topicClose&t=" + encodeURIComponent(tema) + "&reply=" + encodeURIComponent(reply))
      .then((r) => r.json())
      .then(function (data) {
        if (data.control > 0) {
          Swal.fire({
            title: "¡Genial!",
            text: "El Tema ha sido Cerrado",
            icon: "success",
            confirmButtonText: "Aceptar",
          });
          setTimeout(function () {
            window.location.href = VV2.base + "index.php";
          }, 1500);
        } else {
          Swal.fire({ icon: "error", title: "Oops...", text: "Hubo un error" });
        }
      })
      .catch(() => alert("A system error was detected"));
  }
}

function viewControl() {
  const VV2 = vvDetectApp();
  var tema = jQuery("#topic").val();
  var user = jQuery("#user").val();
  jQuery.get(VV2.api + "?ac=view&tema=" + encodeURIComponent(tema) + "&user=" + encodeURIComponent(user), function () {});
}

function SearchTema() {
  const VV2 = vvDetectApp();
  var tema = jQuery("#buscar").val();
  window.location.href = VV2.base + "index.php?s=" + encodeURIComponent(tema);
}
