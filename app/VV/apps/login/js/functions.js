
window.onload = (event) => {

  console.log('The page has fully loaded');

  $("#loginBtn").click(function(){
    var pass = $("#pass").val();
    var user = $("#user").val();
    $("#loginMsg").hide();
    login(user, pass);
    $(this).css("transform", "translateX(10px)");
  });

  // ── Ojito toggle password ──────────────────────────────────────
  $("#togglePass").on("click", function() {
    var p = document.getElementById("pass");
    if (p.type === "password") {
      p.type = "text";
      $(this).text("🙈");
    } else {
      p.type = "password";
      $(this).text("👁");
    }
  });

  // ── Modal Registrarse / Resetear Password ──────────────────────
  $("#openResetModal").on("click", function(e) {
    e.preventDefault();
    // Resetear estado
    $("#resetStep1").show();
    $("#resetStep2").hide();
    $("#rFilial,#rEmail,#rNombre,#rApellido,#rTelefono").val("");
    $("#resetMsg,#resetMsg2").hide().text("");
    $("#resetModal").css("display", "flex");
  });

  $("#closeResetModal").on("click", function(e) {
    e.preventDefault();
    $("#resetModal").hide();
  });

  // Cerrar al hacer click fuera del cuadro
  $("#resetModal").on("click", function(e) {
    if (e.target === this) $("#resetModal").hide();
  });

  // Submit Paso 1: verificar correo
  $("#btnResetSubmit").on("click", function() {
    var filial = $("#rFilial").val().trim();
    var email  = $("#rEmail").val().trim();
    if (!filial || !email) {
      showResetMsg("Todos los campos son requeridos.", "red");
      return;
    }
    var $btn = $(this).prop("disabled", true).text("Procesando...");
    $.get("/VV/apps/login/proccess/actions.php",
          { ac: "resetPass", filial: filial, email: email },
          function(data) {
            if (data.status === "sent") {
              showResetMsg("✅ " + data.msg, "green");
              setTimeout(function() { $("#resetModal").hide(); }, 3500);
            } else if (data.status === "not_found") {
              $("#resetStep1").hide();
              $("#resetStep2").show();
            } else {
              showResetMsg(data.msg || "Error inesperado.", "red");
            }
          }, "json")
      .fail(function() { showResetMsg("Error de conexión.", "red"); })
      .always(function() { $btn.prop("disabled", false).text("Enviar"); });
  });

  // Submit Paso 2: solicitar nuevo acceso
  $("#btnSolicitarAcceso").on("click", function() {
    var nombre   = $("#rNombre").val().trim();
    var apellido = $("#rApellido").val().trim();
    var telefono = $("#rTelefono").val().trim();
    if (!nombre || !apellido) {
      showResetMsg2("Nombre y Apellido son requeridos.", "red");
      return;
    }
    var $btn = $(this).prop("disabled", true).text("Enviando...");
    $.get("/VV/apps/login/proccess/actions.php", {
      ac:       "newUserRequest",
      filial:   $("#rFilial").val().trim(),
      email:    $("#rEmail").val().trim(),
      nombre:   nombre,
      apellido: apellido,
      telefono: telefono
    }, function(data) {
      if (data.status === "ok") {
        $("#resetStep2").html(
          '<p style="color:#27ae60;text-align:center;font-size:14px;line-height:1.7;">' +
          '✅ Solicitud enviada.<br>El Comité la revisará pronto.</p>'
        );
      } else {
        showResetMsg2(data.msg || "Error al enviar.", "red");
        $btn.prop("disabled", false).text("Solicitar Acceso");
      }
    }, "json")
    .fail(function() {
      showResetMsg2("Error de conexión.", "red");
      $btn.prop("disabled", false).text("Solicitar Acceso");
    });
  });

}

function showResetMsg(msg, color) {
  $("#resetMsg").text(msg).css("color", color || "red")
    .css("border-color", color === "green" ? "#a5d6a7" : "#f5c6cb")
    .css("background", color === "green" ? "#f0f7f0" : "#fef9f9")
    .show();
}
function showResetMsg2(msg, color) {
  $("#resetMsg2").text(msg).css("color", color || "red").show();
}

function login(user, pass) {

  var loginUrl = '/VV/apps/login/proccess/actions.php?ac=login&user=' + user + '&pass=' + pass;
  console.log(loginUrl);

  fetch(loginUrl)
    .then(function(response) {
      return response.json();
    }).then(function(data) {
      console.log(data.login);

      if (data.login == 1) {
        if (data.total >= 1) {
          window.location.href = '/VV/apps/Forum/pending.php';
        } else {
          window.location.href = '/VV/apps/Forum/index.php';
        }
      }
      if (data.login == 2) {
        $("#loginMsg").show();
      }
      if (data.login == 3) {
        $("#loginMsg").show();
      }

    }).catch(function(error) {
      alert("A system error was detected");
    });

}
