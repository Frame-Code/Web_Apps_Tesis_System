// Carga datos en localStorage la primera vez que se abre la app.
function cargarDatosEjemplo() {
    // "tesis_ok" es una bandera: si ya existe, no cargamos de nuevo.
    if (localStorage.getItem('tesis_ok')) 
        return;

    var usuarios = [
        { id: 1, nombre: 'Ana García',    email: 'coordinador@tesis.edu', rol: 'coordinador' },
        { id: 2, nombre: 'Carlos Mendoza',email: 'tutor@tesis.edu',       rol: 'tutor'        },
        { id: 3, nombre: 'Daniel Mora',   email: 'estudiante@tesis.edu',  rol: 'estudiante'   },
        { id: 4, nombre: 'María López',   email: 'estudiante2@tesis.edu', rol: 'estudiante'   }
    ];

    var proyectos = [
        {
            id: 1,
            titulo: 'Sistema de Gestión de Inventario con IA',
            descripcion: 'Sistema para control de inventarios en PYMES usando predicción de demanda.',
            estudiante_id: 3,
            tutor_id: 2,
            estado: 'en-curso',
            fecha_inicio: '2026-02-01',
            fecha_fin:    '2026-07-01',
            progreso: 35
        },
        {
            id: 2,
            titulo: 'Plataforma E-learning para Zonas Rurales',
            descripcion: 'Plataforma web accesible sin conexión para estudiantes de zonas rurales.',
            estudiante_id: 4,
            tutor_id: 0,
            estado: 'aprobado',
            fecha_inicio: '2026-03-01',
            fecha_fin:    '2026-08-01',
            progreso: 0
        }
    ];

    var avances = [
        { id: 1, proyecto_id: 1, titulo: 'Análisis de Requerimientos', porcentaje: 20, estado: 'aprobado',  fecha: '2026-02-15', descripcion: 'Análisis detallado de los requerimientos del sistema' },
        { id: 2, proyecto_id: 1, titulo: 'Diseño de Arquitectura',     porcentaje: 35, estado: 'revision', fecha: '2026-03-10', descripcion: 'Diseño de la arquitectura del sistema' }
    ];

    var tutorias = [
        { id: 1, proyecto_id: 1, tutor_id: 2, estudiante_id: 3, fecha: '2026-03-05', duracion: 2,   modalidad: 'presencial', tema: 'Revisión de requerimientos', estado: 'realizada'  },
        { id: 2, proyecto_id: 1, tutor_id: 2, estudiante_id: 3, fecha: '2026-03-20', duracion: 1.5, modalidad: 'virtual',    tema: 'Revisión de arquitectura',   estado: 'programada' }
    ];

    var asistencia = [
        { id: 1, tutoria_id: 1, estudiante_id: 3, asistio: true, tipo: 'presencial', fecha: '2026-03-05' }
    ];

    // JSON.stringify convierte el array de objetos a texto para guardarlo
    localStorage.setItem('tesis_usuarios',   JSON.stringify(usuarios));
    localStorage.setItem('tesis_proyectos',  JSON.stringify(proyectos));
    localStorage.setItem('tesis_avances',    JSON.stringify(avances));
    localStorage.setItem('tesis_tutorias',   JSON.stringify(tutorias));
    localStorage.setItem('tesis_asistencia', JSON.stringify(asistencia));
    localStorage.setItem('tesis_ok', 'true');
}
cargarDatosEjemplo(); 


// LEER Y GUARDAR DATOS
function getUsuarios() {
    return JSON.parse(localStorage.getItem('tesis_usuarios')) || [];
}

function getProyectos() {
    return JSON.parse(localStorage.getItem('tesis_proyectos')) || [];
}
function guardarProyectos(lista) {
    localStorage.setItem('tesis_proyectos', JSON.stringify(lista));
}

function getAvances() {
    return JSON.parse(localStorage.getItem('tesis_avances')) || [];
}

function getTutorias() {
    return JSON.parse(localStorage.getItem('tesis_tutorias')) || [];
}
function guardarTutorias(lista) {
    localStorage.setItem('tesis_tutorias', JSON.stringify(lista));
}

function guardarAvances(lista) {
    localStorage.setItem('tesis_avances', JSON.stringify(lista));
}

function getAsistencia() {
    return JSON.parse(localStorage.getItem('tesis_asistencia')) || [];
}

function guardarAsistencia(lista) {
    localStorage.setItem('tesis_asistencia', JSON.stringify(lista));
}


// UTILIDADES

// Genera el próximo ID sumando 1 al ID más alto de la lista
function nuevoId(lista) {
    if (lista.length === 0) return 1;
    var ids = lista.map(function(x) { return x.id; });
    return Math.max.apply(null, ids) + 1;
}

// Busca un usuario por su id
function getUsuarioPorId(id) {
    return getUsuarios().find(function(u) { return u.id == id; }) || null;
}

function formatearFecha(fecha) {
    if (!fecha) 
        return '—';
    var p = fecha.split('-');
    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : fecha;
}

// Muestra una alerta de éxito y la oculta sola después de 3 segundos
function mostrarExito(idElemento, mensaje) {
    var el = document.getElementById(idElemento);
    el.textContent = mensaje;
    el.className = 'alerta alerta-exito alerta-visible';
    setTimeout(function() {
        el.className = 'alerta alerta-exito';
    }, 3000);
}

// Muestra una alerta de error
function mostrarError(idElemento, mensaje) {
    var el = document.getElementById(idElemento);
    el.textContent = mensaje;
    el.className = 'alerta alerta-error alerta-visible';
}

// Devuelve el objeto del usuario logueado (desde tesis_sesion_usuario) o null
function getUsuarioActual() {
    return JSON.parse(localStorage.getItem('tesis_sesion_usuario')) || null;
}

// Devuelve el rol del usuario logueado: 'tutor', 'coordinador', 'estudiante' o null
function getRolActual() {
    var u = getUsuarioActual();
    return u ? u.rol : null;
}

// Cierra la sesión y redirige al login (funciona desde cualquier nivel de carpeta)
function cerrarSesion() {
    localStorage.removeItem('tesis_sesion_usuario');
    var path      = window.location.pathname;
    var pagesIdx  = path.indexOf('/pages/');
    if (pagesIdx === -1) {
        window.location.href = 'pages/login.html';
        return;
    }
    var afterPages = path.substring(pagesIdx + 7); // texto después de '/pages/'
    var depth      = afterPages.split('/').length - 1; // subdirectorios dentro de pages/
    var prefix     = depth > 0 ? '../' : '';
    window.location.href = prefix + 'login.html';
}
