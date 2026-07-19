const API = "../../backend/index.php";

const formulario = document.getElementById("formAsistencia");
const tabla = document.getElementById("tablaAsistencia");

let editando = false;
let idEditar = null;

window.onload = () => {
    cargarProyectos();
    cargarAsistencias();
};

async function cargarProyectos() {

    const res = await fetch(API + "?accion=listar_proyectos", {
        credentials: "include"
    });

    const json = await res.json();

    const select = document.getElementById("proyecto_id");

    select.innerHTML =
        '<option value="">Seleccione un proyecto</option>';

    json.data.forEach(p => {

        select.innerHTML +=
            `<option value="${p.id}"
                data-estudiante="${p.estudiante_nombre}">
                ${p.titulo}
            </option>`;

    });

}

document
.getElementById("proyecto_id")
.addEventListener("change", function(){

    const opcion = this.options[this.selectedIndex];

    document.getElementById("estudiante").value =
        opcion.dataset.estudiante || "";

});

formulario.addEventListener("submit", async function(e){

    e.preventDefault();

    const datos = {

        proyecto_id:
            document.getElementById("proyecto_id").value,

        fecha:
            document.getElementById("fecha").value,

        estado:
            document.getElementById("estado").value,

        modalidad:
            document.getElementById("modalidad").value,

        observaciones:
            document.getElementById("observaciones").value

    };

    let accion =
        "crear_asistencia";

    let url =
        API + "?accion=" + accion;

    if(editando){

        accion = "editar_asistencia";

        url =
        API + "?accion=editar_asistencia&id=" + idEditar;

    }

    const res = await fetch(url,{

        method:"POST",

        credentials:"include",

        headers:{
            "Content-Type":"application/json"
        },

        body:JSON.stringify(datos)

    });

    const json = await res.json();

    if(!res.ok){

        alert(json.error);

        return;

    }

    formulario.reset();

    editando = false;

    idEditar = null;

    cargarAsistencias();

});

async function cargarAsistencias(){

    const res = await fetch(

        API + "?accion=listar_asistencias",

        {
            credentials:"include"
        }

    );

    const json = await res.json();

    tabla.innerHTML = "";

    json.data.forEach(a=>{

        tabla.innerHTML += `

        <tr>

            <td>${a.estudiante_nombre}</td>

            <td>${a.fecha}</td>

            <td>${a.estado}</td>

            <td>${a.modalidad}</td>

            <td>${a.observaciones ?? ""}</td>

            <td>

                <button
                    class="btn btn-warning btn-sm"
                    onclick="editar(${a.id})">

                    Editar

                </button>

                <button
                    class="btn btn-danger btn-sm"
                    onclick="eliminarAsistencia(${a.id})">

                    Eliminar

                </button>

            </td>

        </tr>

        `;

    });

}

async function editar(id){

    const res = await fetch(

        API + "?accion=ver_asistencia&id="+id,

        {
            credentials:"include"
        }

    );

    const json = await res.json();

    const a = json.data;

    document.getElementById("proyecto_id").value =
        a.proyecto_id;

    document.getElementById("fecha").value =
        a.fecha;

    document.getElementById("estado").value =
        a.estado;

    document.getElementById("modalidad").value =
        a.modalidad;

    document.getElementById("observaciones").value =
        a.observaciones;

    editando = true;

    idEditar = id;

}

async function eliminarAsistencia(id){

    if(!confirm("¿Eliminar asistencia?"))
        return;

    await fetch(

        API+"?accion=eliminar_asistencia&id="+id,

        {
            method:"POST",
            credentials:"include"
        }

    );

    cargarAsistencias();

}