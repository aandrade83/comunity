<?php
// Test sin sesión — solo para diagnóstico
?>
<!DOCTYPE html>
<html>
<head><title>Test JS</title></head>
<body>
<button onclick="testFn()">Click test</button>
<div id="out"></div>
<script>
function testFn() {
    document.getElementById('out').textContent = 'SCRIPT RUNNING OK - ' + new Date();
}
document.getElementById('out').textContent = 'Script ran at load: ' + new Date();
</script>
</body>
</html>
