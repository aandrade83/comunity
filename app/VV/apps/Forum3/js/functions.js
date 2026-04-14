// ============================
// INACTIVIDAD
// ============================
let inactividadTiempo;
let tiempoRestante = 300000; // 5 minutos en milisegundos
let contadorIntervalo;

function cerrarSesion() {
  fetch('logout.php')
  .then(() => {
    window.location.href = 'https://lab.lacallecr.com/VV/index.php';
  })
  .catch(error => console.error('Error al cerrar sesión:', error));
}

function mostrarAlerta() {
  Swal.fire({
    title: 'Inactividad detectada',
    text: "Serás redirigido al inicio por inactividad",
    icon: 'warning',
    confirmButtonText: 'Ok'
  }).then((result) => {
    if (result.isConfirmed) cerrarSesion();
  });
}

function reiniciarTiempo() {
  clearTimeout(inactividadTiempo);
  tiempoRestante = 450000; // 7.5 min (ajusta si quieres)
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

window.addEventListener('load', reiniciarTiempo);
window.addEventListener('load', () => console.log('The page has fully loaded'));

document.addEventListener('mousemove', reiniciarTiempo, { passive: true });
document.addEventListener('keydown', reiniciarTiempo, { passive: true });
document.addEventListener('scroll', reiniciarTiempo, { passive: true });
document.addEventListener('click', reiniciarTiempo, { passive: true });


// ============================
// DOCUMENT READY
// ============================
$(document).ready(function () {

  // ============================
  // Helpers
  // ============================
  function safeTrim(v) {
    return (v === undefined || v === null) ? "" : String(v).trim();
  }

  function parseMaybeJSON(resp) {
    if (resp === null || resp === undefined) return null;
    if (typeof resp === "object") return resp;
    const t = String(resp).trim();
    if (!t) return null;
    try { return JSON.parse(t); } catch (e) { return null; }
  }

  function swalError(msg) {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: msg || "Hubo un error"
    });
  }

  // ============================
  // DROPZONE INIT (general)
  // ============================
  Dropzone && (Dropzone.autoDiscover = false);

  // ---------- NEW TOPIC ----------
  let dzTopic = null;
  let dzTopicLastOk = null;

  if (typeof Dropzone !== "undefined") {
    const hasFrmNew = document.getElementById("frm_new") !== null;
    const dzEl = document.querySelector("#dzAdjuntos");
    if (hasFrmNew && dzEl) {
      dzTopic = new Dropzone("#dzAdjuntos", {
        url: "https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=topic",
        method: "post",
        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 10,
        addRemoveLinks: true,
        timeout: 120000,
        paramName: "adjuntos", // ✅ SIN [] (evita estructura rara en PHP)
        maxFilesize: 12,
        acceptedFiles: "image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt",
        dictDefaultMessage: "Arrastra archivos aquí o haz click para seleccionar",
        dictRemoveFile: "Quitar",
      });

      dzTopic.on("sending", function (file, xhr, formData) {
        formData.append("t", safeTrim($("#title").val()));
        formData.append("c", safeTrim($("#category").val()));
        formData.append("desc", safeTrim($("#desc").val()));
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
        // message puede venir como string o como objeto
        const data = parseMaybeJSON(message) || parseMaybeJSON(xhr && xhr.responseText);
        const msg = (data && (data.error || data.message)) ? (data.error || data.message) :
        (typeof message === "string" ? message : "Error al subir");
        swalError(msg);
      });

      dzTopic.on("queuecomplete", function () {
        if (!dzTopicLastOk) return;

        $("#btn_post").prop("disabled", true);

        Swal.fire({
          title: "¡PERFECTO!",
          text: "Tu Tema fue creado, Una vez aprobado por la comisión va a ser publicado",
          icon: "success",
          confirmButtonText: "Aceptar"
        });

        setTimeout(function () {
          window.location.href = "https://lab.lacallecr.com/VV/apps/Forum/index.php";
        }, 5000);
      });
    }
  }

  // Submit NEW TOPIC
  $("#frm_new").off("submit").on("submit", function (e) {
    e.preventDefault();

    const title = safeTrim($("#title").val());
    const cat = safeTrim($("#category").val());
    const desc = safeTrim($("#desc").val());

    if (!(title.length > 1 && cat && desc.length > 1)) {
      swalError("Todos los campos son obligatorios");
      return;
    }

    // ✅ Con archivos
    if (dzTopic && dzTopic.getQueuedFiles().length > 0) {
      dzTopicLastOk = null;
      dzTopic.processQueue();
      return;
    }

    // ✅ Sin archivos
    const fd = new FormData();
    fd.append("t", title);
    fd.append("c", cat);
    fd.append("desc", desc);

    fetch("https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=topic", {
      method: "POST",
      body: fd
    })
    .then(async (r) => {
      const text = await r.text();
      const data = parseMaybeJSON(text);
      if (!data) throw new Error("Respuesta NO es JSON: " + text);
      return data;
    })
    .then(function (data) {
      if (data && String(data.control) === "1") {

        $("#btn_post").prop("disabled", true);

        Swal.fire({
          title: "¡PERFECTO!",
          text: "Tu Tema fue creado, Una vez aprobado por la comisión va a ser publicado",
          icon: "success",
          confirmButtonText: "Aceptar"
        });

        setTimeout(function () {
          window.location.href = "https://lab.lacallecr.com/VV/apps/Forum/index.php";
        }, 5000);

      } else {
        swalError((data && data.error) ? data.error : "Hubo un error");
      }
    })
    .catch(function (err) {
      console.error(err);
      swalError("A system error was detected");
    });
  });

  // ---------- TOPIC PENDING (APROBADO / RECHAZADO + adjuntos) ----------
  let dzPending = null;
  let dzPendingLastOk = null;

  if (typeof Dropzone !== "undefined") {
    const hasFrmNew = document.getElementById("frm_new") !== null;
    const hasFrmPending = document.getElementById("frm_pending") !== null;

    let dzSelector = null;
    if (document.querySelector("#dzPendingAdjuntos")) dzSelector = "#dzPendingAdjuntos";
    // fallback: si en pending reutilizaste #dzAdjuntos
    else if (hasFrmPending && !hasFrmNew && document.querySelector("#dzAdjuntos")) dzSelector = "#dzAdjuntos";

    if (dzSelector) {
      dzPending = new Dropzone(dzSelector, {
        url: "https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=topicPending",
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

      dzPending.on("sending", function (file, xhr, formData) {
        formData.append("t", safeTrim($("#topic").val()));
        formData.append("r", safeTrim($("#revision").val()));
        formData.append("reply", safeTrim($("#reply").val()));

          // ✅ si hubo un batch anterior y ya existe reply_id, lo reutilizamos
        if (dzPendingLastOk && dzPendingLastOk.reply_id) {
          formData.append("reply_id", dzPendingLastOk.reply_id);
        }
      });

      dzPending.on("sendingmultiple", function (files, xhr, formData) {
  formData.append("t", safeTrim($("#topic").val()));
  formData.append("r", safeTrim($("#revision").val()));
  formData.append("reply", safeTrim($("#reply").val()));

  if (dzPendingLastOk && dzPendingLastOk.reply_id) {
    formData.append("reply_id", dzPendingLastOk.reply_id);
  }
});

      dzPending.on("success", function (file, resp) {
        const data = parseMaybeJSON(resp);
        if (data && String(data.control) === "1") dzPendingLastOk = data;
      });

      dzPending.on("successmultiple", function (files, resp) {
        const data = parseMaybeJSON(resp);
        if (data && String(data.control) === "1") dzPendingLastOk = data;
      });

      dzPending.on("error", function (file, message, xhr) {
        const data = parseMaybeJSON(message) || parseMaybeJSON(xhr && xhr.responseText);
        const msg = (data && (data.error || data.message)) ? (data.error || data.message) :
        (typeof message === "string" ? message : "Error al subir");
        swalError(msg);
      });

      dzPending.on("queuecomplete", function () {
        if (!dzPendingLastOk) return;

        Swal.fire({
          title: "¡Genial!",
          text: "La Revisión del Tema ha sido exitosa",
          icon: "success",
          confirmButtonText: "Aceptar"
        });

        setTimeout(function () {
          window.location.href = "https://lab.lacallecr.com/VV/apps/Forum/pending.php";
        }, 5000);
      });
    }
  }

  $("#frm_pending").off("submit").on("submit", function (e) {
    e.preventDefault();

    const tema = safeTrim($("#topic").val());
    const rev = safeTrim($("#revision").val());
    const reply = safeTrim($("#reply").val());

    // Regla original: si rev == 1 (Aprobado) puede ir sin reply. Si no, reply requerido.
    if (!(tema && rev) || (rev !== "1" && reply.length < 1)) {
      swalError("Todos los campos son obligatorios");
      return;
    }

    if (dzPending && dzPending.getQueuedFiles().length > 0) {
      dzPendingLastOk = null;
      dzPending.processQueue();
      return;
    }

    // Sin archivos -> POST normal
    const fd = new FormData();
    fd.append("t", tema);
    fd.append("r", rev);
    fd.append("reply", reply);

    fetch("https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=topicPending", {
      method: "POST",
      body: fd
    })
    .then(async (r) => {
      const text = await r.text();
      const data = parseMaybeJSON(text);
      if (!data) throw new Error("Respuesta NO es JSON: " + text);
      return data;
    })
    .then(function (data) {
      if (data && String(data.control) === "1") {

        Swal.fire({
          title: "¡Genial!",
          text: "La Revisión del Tema ha sido exitosa",
          icon: "success",
          confirmButtonText: "Aceptar"
        });

        setTimeout(function () {
          window.location.href = "https://lab.lacallecr.com/VV/apps/Forum/pending.php";
        }, 5000);

      } else {
        swalError((data && data.error) ? data.error : "Hubo un error");
      }
    })
    .catch(function (err) {
      console.error(err);
      swalError("A system error was detected");
    });
  });

  // ---------- REPLY (RESPUESTA EN TOPIC) ----------
  let dzReply = null;
  let dzReplyLastOk = null;

  if (typeof Dropzone !== "undefined") {
    const hasFrmNew = document.getElementById("frm_new") !== null;
    const hasFrmPending = document.getElementById("frm_pending") !== null;
    const hasReplyForm = (document.getElementById("frm_reply") !== null) || (document.getElementById("frm_topic") !== null && document.getElementById("reply") !== null);

    let dzSelector = null;
    if (document.querySelector("#dzReplyAdjuntos")) dzSelector = "#dzReplyAdjuntos";
    // fallback: si en topic reutilizaste #dzAdjuntos
    else if (hasReplyForm && !hasFrmNew && !hasFrmPending && document.querySelector("#dzAdjuntos")) dzSelector = "#dzAdjuntos";

    if (dzSelector) {
      dzReply = new Dropzone(dzSelector, {
        url: "https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=reply",
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

      dzReply.on("sending", function (file, xhr, formData) {
        formData.append("t", safeTrim($("#topic").val()));
        formData.append("reply", safeTrim($("#reply").val()));
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
        const data = parseMaybeJSON(message) || parseMaybeJSON(xhr && xhr.responseText);
        const msg = (data && (data.error || data.message)) ? (data.error || data.message) :
        (typeof message === "string" ? message : "Error al subir");
        swalError(msg);
      });

      dzReply.on("queuecomplete", function () {
        if (!dzReplyLastOk) return;

        $("#btn_post").prop("disabled", true);

        Swal.fire({
          title: "¡Genial!",
          text: "Su Respuesta ha sido Guardada",
          icon: "success",
          confirmButtonText: "Aceptar"
        });

        setTimeout(function () {
          window.location.href = "https://lab.lacallecr.com/VV/apps/Forum/index.php";
        }, 5000);
      });
    }
  }

  // Importante: algunas páginas usan #frm_topic para reply, otras #frm_reply
  $("#frm_reply, #frm_topic").off("submit").on("submit", function (e) {
    // Si NO existe el textarea #reply, no tocamos nada
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

    // Sin archivos -> POST normal
    const fd = new FormData();
    fd.append("t", tema);
    fd.append("reply", reply);

    fetch("https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=reply", {
      method: "POST",
      body: fd
    })
    .then(async (r) => {
      const text = await r.text();
      const data = parseMaybeJSON(text);
      if (!data) throw new Error("Respuesta NO es JSON: " + text);
      return data;
    })
    .then(function (data) {
      if (data && String(data.control) === "1") {

        $("#btn_post").prop("disabled", true);

        Swal.fire({
          title: "¡Genial!",
          text: "Su Respuesta ha sido Guardada",
          icon: "success",
          confirmButtonText: "Aceptar"
        });

        setTimeout(function () {
          window.location.href = "https://lab.lacallecr.com/VV/apps/Forum/index.php";
        }, 5000);

      } else {
        swalError((data && data.error) ? data.error : "Hubo un error 22");
      }
    })
    .catch(function (err) {
      console.error(err);
      swalError("A system error was detected");
    });
  });

});

// ✅ FIN document.ready



// ============================
// FUNCIONES EXISTENTES
// ============================
function logout(){
  fetch('https://lab.lacallecr.com/VV/apps/login/proccess/actions.php?ac=logout')
  .then(r => r.json())
  .then(function(data){
    if (data.login == 1) window.location.href = 'https://lab.lacallecr.com/VV/';
  })
  .catch(() => alert("A system error was detected"));
}

function closeTopicControl(){
  var tema = $("#topic").val();
  var reply = $("#reply").val();

  if (reply.length > 1 && reply !== null) {
    Swal.fire({
      title: '¿Estás seguro de Cerrar el caso?',
      text: "¡No podrás revertir esto!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        closeTopic();
        Swal.fire('¡Confirmado!','La acción ha sido confirmada.','success');
      } else {
        Swal.fire('Cancelado','La acción ha sido cancelada.','error');
      }
    });
  } else {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "Todos los campos son obligatorios",
      footer: '<a href="#">Favor corregir y volver a enviar</a>'
    });
  }
}

function closeTopic(){
  $('.active').hide();
  $('.closed').show();

  var tema = $("#topic").val();
  var reply = $("#reply").val();

  if (reply.length > 1) {
    fetch('https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=topicClose&t='+tema+'&reply='+reply)
    .then(r => r.json())
    .then(function(data){
      if (data.control > 0) {
        Swal.fire({
          title: '¡Genial!',
          text: 'El Tema ha sido Cerrado',
          icon: 'success',
          confirmButtonText: 'Aceptar'
        });
        setTimeout(function(){
          window.location.href = 'https://lab.lacallecr.com/VV/apps/Forum/index.php';
        }, 5000);
      } else {
        Swal.fire({ icon:"error", title:"Oops...", text:"Hubo un error 22" });
      }
    })
    .catch(() => alert("A system error was detected"));
  }
}

function saveChanges(id,company){
  var element = $("#check_"+id+'_'+company);
  var value = element.is(':checked') ? 1 : 0;

  $.get("https://lab.lacallecr.com/VV/apps/leagues/proccess/actions.php?ac=update&id="+id+"&c="+company+"&value="+value, function(){});
  show_saving_message();
}

function viewControl(){
  var tema = $("#topic").val();
  var user = $("#user").val();
  $.get("https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=view&tema="+tema+"&user="+user, function(){});
}

function SearchTema(){
  var tema = $("#buscar").val();
  window.location.href = 'https://lab.lacallecr.com/VV/apps/Forum/index.php?s='+tema;
}

function likes(res){
  var tema = $("#topic").val();
  var user = $("#user").val();
  $.get("https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=like&tema="+tema+"&user="+user+"&like="+res, function(){});

  if(res){
    var contenido = $("#slu_"+tema).text();
    $("#slu_"+tema).text(parseInt(contenido) + 1);
    $('#lu_'+tema).addClass('disabled-link');
    $('#ld_'+tema).addClass('disabled-link');
  } else {
    var contenido = $("#sld_"+tema).text();
    $("#sld_"+tema).text(parseInt(contenido) + 1);
    $('#ld_'+tema).addClass('disabled-link');
    $('#lu_'+tema).addClass('disabled-link');
  }
}

function Rlikes(res,rid){
  var user = $("#user").val();
  $.get("https://lab.lacallecr.com/VV/apps/Forum/proccess/actions.php?ac=Rlike&res="+rid+"&user="+user+"&like="+res, function(){});

  if(res){
    var contenido = $("#rslu_"+rid).text();
    $("#rslu_"+rid).text(parseInt(contenido) + 1);
    $('#rlu_'+rid).addClass('disabled-link');
    $('#rld_'+rid).addClass('disabled-link');
  } else {
    var contenido = $("#rsld_"+rid).text();
    $("#rsld_"+rid).text(parseInt(contenido) + 1);
    $('#rld_'+rid).addClass('disabled-link');
    $('#rlu_'+rid).addClass('disabled-link');
  }
}
