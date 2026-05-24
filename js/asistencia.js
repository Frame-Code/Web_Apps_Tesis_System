// BASE DE DATOS SIMULADA
let asistencias = [];

const formulario = document.getElementById("formAsistencia");

const tabla = document.getElementById("tablaAsistencia");

let editando = false;

let idEditar = null;


// REGISTRAR Y EDITAR
formulario.addEventListener("submit", function(e){

    e.preventDefault();


    const estudiante =
        document.getElementById("estudiante").value;

    const fecha =
        document.getElementById("fecha").value;

    const estado =
        document.getElementById("estado").value;

    const modalidad =
        document.getElementById("modalidad").value;

    const observaciones =
        document.getElementById("observaciones").value;


    // VALIDACIONES
    if(
        estudiante === "" ||
        fecha === "" ||
        estado === "" ||
        modalidad === ""
    ){

        alert("Complete todos los campos");

        return;
    }


    // CREAR
    if(!editando){

        const asistencia = {

            id: Date.now(),

            estudiante,

            fecha,

            estado,

            modalidad,

            observaciones

        };

        asistencias.push(asistencia);

    }else{

        // EDITAR
        asistencias = asistencias.map(a => {

            if(a.id === idEditar){

                return {

                    ...a,

                    estudiante,

                    fecha,

                    estado,

                    modalidad,

                    observaciones

                };
            }

            return a;
        });

        editando = false;

        idEditar = null;
    }


    mostrarAsistencias();

    formulario.reset();

});


// MOSTRAR ASISTENCIAS
function mostrarAsistencias(){

    tabla.innerHTML = "";


    asistencias.forEach(asistencia => {

        tabla.innerHTML += `

            <tr>

                <td>${asistencia.estudiante}</td>

                <td>${asistencia.fecha}</td>

                <td>${asistencia.estado}</td>

                <td>${asistencia.modalidad}</td>

                <td>${asistencia.observaciones}</td>

                <td>

                    <button onclick="editar(${asistencia.id})">
                        Editar
                    </button>

                    <button onclick="eliminar(${asistencia.id})">
                        Eliminar
                    </button>

                </td>

            </tr>

        `;
    });


    // RESUMEN
    const total = asistencias.length;


    const presentes = asistencias.filter(
        a => a.estado === "Presente"
    ).length;


    const ausentes = asistencias.filter(
        a => a.estado === "Ausente"
    ).length;


    const virtuales = asistencias.filter(
        a => a.modalidad === "Virtual"
    ).length;


    const presenciales = asistencias.filter(
        a => a.modalidad === "Presencial"
    ).length;


    const porcentaje = total > 0

        ? ((presentes / total) * 100).toFixed(2)

        : 0;


    document.getElementById("resumen").innerHTML = `

        <p>
            <strong>Total registros:</strong>
            ${total}
        </p>

        <p>
            <strong>Presentes:</strong>
            ${presentes}
        </p>

        <p>
            <strong>Ausentes:</strong>
            ${ausentes}
        </p>

        <p>
            <strong>Virtuales:</strong>
            ${virtuales}
        </p>

        <p>
            <strong>Presenciales:</strong>
            ${presenciales}
        </p>

        <p>
            <strong>Porcentaje de asistencia:</strong>
            ${porcentaje}%
        </p>

        <p>
            <strong>Estado Final:</strong>
            ${porcentaje >= 70 ? "Aprueba" : "Reprueba"}
        </p>

    `;
}


// ELIMINAR
function eliminar(id){

    asistencias = asistencias.filter(
        a => a.id !== id
    );

    mostrarAsistencias();
}


// EDITAR
function editar(id){

    const asistencia = asistencias.find(
        a => a.id === id
    );


    document.getElementById("estudiante").value =
        asistencia.estudiante;

    document.getElementById("fecha").value =
        asistencia.fecha;

    document.getElementById("estado").value =
        asistencia.estado;

    document.getElementById("modalidad").value =
        asistencia.modalidad;

    document.getElementById("observaciones").value =
        asistencia.observaciones;


    editando = true;

    idEditar = id;
}