// Formatea "YYYY-MM-DD" a "DD/MM/YYYY"
function formatearFecha(fecha) {
    if (!fecha) return '—';
    var p = fecha.split('-');
    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : fecha;
}

// Obtiene de manera asíncrona el usuario actual logueado en el backend
async function getUsuarioActual() {
    var path = window.location.pathname;
    var pagesIdx = path.indexOf('/pages/');
    var backendPath;

    if (pagesIdx === -1) {
        backendPath = 'backend/index.php';
    } else {
        var afterPages = path.substring(pagesIdx + 7);
        var depth = afterPages.split('/').length - 1;
        var prefix = depth > 0 ? '../'.repeat(depth) : '';
        backendPath = prefix + '../backend/index.php';
    }

    try {
        const res = await fetch(backendPath + '?accion=sesion', { method: 'GET', credentials: 'include' });
        
        // Si el servidor dice 401, no hay sesión activa. Devuelve null de forma controlada.
        if (res.status === 401) {
            return null;
        }
        
        if (!res.ok) return null;
        
        const json = await res.json();
        
        // CORRECCIÓN: Validamos tanto 'json.usuario' (esperado en login) como 'json.data' por compatibilidad
        if (json.success) {
            return json.usuario || json.data || null;
        }
        return null;
    } catch (e) {
        return null; // Captura errores de red silenciosamente
    }
}

// Cierra la sesión en el servidor y redirige al login
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