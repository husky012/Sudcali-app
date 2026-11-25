/**
 * Acceso: pestañas, validación en el navegador (el servidor vuelve a validar) y mostrar contraseña.
 */
(() => {
    // ---------- Pestañas (teclado: flechas izquierda/derecha) ----------
    const pestanas = [...document.querySelectorAll('.pestana-acceso')];

    function activar(pestana, enfocar = true) {
        pestanas.forEach((otra) => {
            const activa = otra === pestana;
            otra.setAttribute('aria-selected', String(activa));
            otra.tabIndex = activa ? 0 : -1;
            document.getElementById(otra.getAttribute('aria-controls')).hidden = !activa;
        });

        history.replaceState(null, '', pestana.href);
        if (enfocar) pestana.focus();
    }

    pestanas.forEach((pestana, indice) => {
        pestana.addEventListener('click', (evento) => {
            evento.preventDefault();
            activar(pestana, false);
            document.querySelector(`#${pestana.getAttribute('aria-controls')} input[type="email"]`).focus();
        });

        pestana.addEventListener('keydown', (evento) => {
            if (evento.key !== 'ArrowRight' && evento.key !== 'ArrowLeft') return;
            evento.preventDefault();
            const paso = evento.key === 'ArrowRight' ? 1 : -1;
            activar(pestanas[(indice + paso + pestanas.length) % pestanas.length]);
        });
    });

    // ---------- Validación en el navegador (mismos mensajes que el servidor) ----------
    const formatoCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function mostrar(campo, mensaje) {
        document.getElementById(campo.getAttribute('aria-describedby')).textContent = mensaje;
        if (mensaje) {
            campo.setAttribute('aria-invalid', 'true');
        } else {
            campo.removeAttribute('aria-invalid');
        }
        return mensaje === '';
    }

    function validarCorreo(campo) {
        const valor = campo.value.trim();
        if (valor === '') return 'Escribe tu correo.';
        if (!formatoCorreo.test(valor)) return 'Escribe un correo válido, por ejemplo nombre@sudcali.test.';
        return '';
    }

    function validarPassword(campo) {
        return campo.value === '' ? 'Escribe tu contraseña.' : '';
    }

    document.querySelectorAll('[data-validar-acceso]').forEach((formulario) => {
        const correo = formulario.querySelector('input[name="correo"]');
        const password = formulario.querySelector('input[name="password"]');
        const boton = formulario.querySelector('button[type="submit"]');

        correo.addEventListener('blur', () => mostrar(correo, validarCorreo(correo)));
        password.addEventListener('blur', () => mostrar(password, validarPassword(password)));
        correo.addEventListener('input', () => correo.hasAttribute('aria-invalid') && mostrar(correo, validarCorreo(correo)));
        password.addEventListener('input', () => password.hasAttribute('aria-invalid') && mostrar(password, validarPassword(password)));

        formulario.addEventListener('submit', (evento) => {
            const correoValido = mostrar(correo, validarCorreo(correo));
            const passwordValida = mostrar(password, validarPassword(password));

            if (!correoValido || !passwordValida) {
                evento.preventDefault();
                (correoValido ? password : correo).focus();
                return;
            }

            boton.disabled = true;
            boton.textContent = 'Entrando…';
        });
    });

    document.querySelectorAll('[data-ver-password]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const campo = document.getElementById(boton.getAttribute('aria-controls'));
            const visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            boton.textContent = visible ? 'Mostrar' : 'Ocultar';
            boton.setAttribute('aria-pressed', String(!visible));
            campo.focus();
        });
    });

    // Enfoca el campo con error o el correo de la pestaña activa.
    const panelActivo = document.querySelector('.panel-acceso:not([hidden])');
    (panelActivo.querySelector('[aria-invalid="true"]') ?? panelActivo.querySelector('input[type="email"]')).focus();
})();
