<?php
/**
 * Renderizado seguro del contenido de temas y respuestas del foro.
 *
 * El contenido se guarda en BD como texto plano (textarea), a veces con
 * Markdown (**negrita**, listas con "-", etc.). Aquí se convierte a HTML
 * sin modificar lo almacenado.
 *
 * Seguridad: Parsedown en modo seguro escapa TODO el HTML del usuario y
 * filtra URLs peligrosas (javascript:, data:, ...). Nada introducido por
 * usuarios se ejecuta como HTML/JS.
 */

require_once __DIR__ . '/Parsedown.php';

function vv_render_post($text)
{
    $text = str_replace(["\r\n", "\r"], "\n", (string)$text);

    // Compatibilidad con publicaciones antiguas que pudieran traer etiquetas
    // simples de formato: se traducen a su equivalente de texto/Markdown en
    // lugar de mostrarse como "<br>" literal. Cualquier otra etiqueta se
    // escapa y se muestra como texto.
    $text = preg_replace('~<br\s*/?>[ \t]*\n?~i', "\n", $text);
    $text = preg_replace('~</p>\s*<p[^>]*>~i', "\n\n", $text);
    $text = preg_replace('~</?p\s*>~i', "\n", $text);
    $text = preg_replace('~</?(?:b|strong)\s*>~i', '**', $text);
    $text = preg_replace('~</?(?:i|em)\s*>~i', '*', $text);

    // Evitar doble escape: si el texto ya trae entidades (&aacute;, &amp;, &lt;)
    // se decodifican aquí y Parsedown las vuelve a escapar una sola vez.
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    static $parser = null;
    if ($parser === null) {
        $parser = new Parsedown();
        $parser->setSafeMode(true);      // escapa HTML y sanea URLs
        $parser->setBreaksEnabled(true); // un salto de línea = <br>
    }

    return $parser->text(trim($text));
}
