// Formatea "YYYY-MM-DD" a "DD/MM/YYYY"
function formatearFecha(fecha) {
    if (!fecha) return '—';
    var p = fecha.split('-');
    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : fecha;
}

// Cierra la sesión en el servidor y redirige al login
// (funciona desde cualquier nivel de carpeta dentro de /pages/)
function cerrarSesion() {
    var path     = window.location.pathname;
    var pagesIdx = path.indexOf('/pages/');

    var loginPath, backendPath;

    if (pagesIdx === -1) {
        loginPath   = 'pages/login.html';
        backendPath = 'backend/index.php';
    } else {
        var afterPages = path.substring(pagesIdx + 7);
        var depth      = afterPages.split('/').length - 1;

        var prefix  = depth > 0 ? '../'.repeat(depth) : '';
        loginPath   = prefix + 'login.html';
        backendPath = prefix + '../backend/index.php';
    }

    fetch(backendPath + '?accion=logout', { method: 'POST', credentials: 'include' })
        .catch(function() { /* ignorar errores de red al cerrar sesión */ })
        .finally(function() {
            window.location.href = loginPath;
        });
}
