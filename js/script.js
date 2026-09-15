// ============================================================
// SolVolt Energía — JavaScript general del sitio
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // Confirmación antes de borrar (usuarios, citas, noticias)
    document.querySelectorAll('.borrar-confirm').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const mensaje = btn.getAttribute('data-mensaje') || '¿Seguro que deseas borrar este elemento?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });

    // Validación básica en cliente para el formulario de registro
    const formRegistro = document.getElementById('form-registro');
    if (formRegistro) {
        formRegistro.addEventListener('submit', function (e) {
            const pass = document.getElementById('password');
            const pass2 = document.getElementById('password2');
            if (pass && pass2 && pass.value !== pass2.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
            }
        });
    }

    // Resalta el enlace activo de la barra de navegación (por si el atributo no viene del servidor)
    const rutaActual = window.location.pathname.split('/').pop();
    document.querySelectorAll('.navbar ul li a').forEach(function (enlace) {
        if (enlace.getAttribute('href') === rutaActual) {
            enlace.classList.add('activo');
        }
    });
});
