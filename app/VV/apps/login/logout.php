<?php
// logout.php — Destruye la sesión y redirige al login.
// Se llama directamente (window.location.href), sin JSON, sin fetch.
session_start();
session_unset();
session_destroy();

header("Location: /VV/");
exit;
