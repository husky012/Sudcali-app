import { createHmac, timingSafeEqual } from 'node:crypto';
import { WebSocketServer, WebSocket } from 'ws';

// Lee WEBSOCKET_SECRET, WEBSOCKET_ALLOWED_ORIGINS y APP_URL del .env de Laravel.
try {
    process.loadEnvFile('.env');
} catch {
    // Sin .env: se usan las variables del entorno.
}

const PORT = Number(process.env.WEBSOCKET_PORT || process.env.PORT || 3000);
const SECRET = process.env.WEBSOCKET_SECRET;
const ORIGENES_PERMITIDOS = (process.env.WEBSOCKET_ALLOWED_ORIGINS || process.env.APP_URL || 'http://localhost:8000')
    .split(',')
    .map((origen) => origen.trim().replace(/\/$/, ''))
    .filter(Boolean);

// Únicos mensajes válidos. Cualquier otro se descarta.
const EVENTOS_PERMITIDOS = new Set(['actualizar_empleados']);
const MAX_CONEXIONES = 50;
const MAX_MENSAJES = 10;
const VENTANA_MS = 10_000;

if (!SECRET) {
    console.error('Falta WEBSOCKET_SECRET en .env; el servidor no arranca sin él.');
    process.exit(1);
}

/**
 * Valida el token firmado por Laravel (App\Actions\PuntoVenta\FirmarTokenWebSocket):
 * "{empleado_id}.{expira_unix}.{hmac_sha256}".
 */
function empleadoDelToken(token) {
    const partes = String(token || '').split('.');
    if (partes.length !== 3) return null;

    const [empleadoId, expira, firma] = partes;
    if (!/^\d+$/.test(empleadoId) || !/^\d+$/.test(expira) || !/^[a-f0-9]{64}$/.test(firma)) return null;
    if (Number(expira) * 1000 < Date.now()) return null;

    const esperada = createHmac('sha256', SECRET).update(`${empleadoId}.${expira}`).digest();
    const recibida = Buffer.from(firma, 'hex');

    return timingSafeEqual(esperada, recibida) ? Number(empleadoId) : null;
}

/**
 * La bitácora no guarda IP completas: solo la red (último octeto en 0).
 */
function redDe(ip) {
    const limpia = String(ip ?? '').replace(/^::ffff:/, '');
    if (/^\d+\.\d+\.\d+\.\d+$/.test(limpia)) return limpia.replace(/\d+$/, '0');
    return limpia.split(':').slice(0, 3).join(':') + '::';
}

function registrar(evento, datos = {}) {
    console.log(JSON.stringify({ fecha: new Date().toISOString(), evento, ...datos }));
}

const wss = new WebSocketServer({
    port: PORT,
    maxPayload: 1024,
    verifyClient: ({ origin, req }, aceptar) => {
        const red = redDe(req.socket.remoteAddress);

        if (!ORIGENES_PERMITIDOS.includes(origin)) {
            registrar('origen_rechazado', { red, origin });
            return aceptar(false, 403, 'Origen no permitido');
        }

        if (wss.clients.size >= MAX_CONEXIONES) {
            registrar('conexiones_agotadas', { red });
            return aceptar(false, 503, 'Demasiadas conexiones');
        }

        const token = new URL(req.url, 'ws://localhost').searchParams.get('token');
        const empleadoId = empleadoDelToken(token);

        if (empleadoId === null) {
            registrar('token_invalido', { red, origin });
            return aceptar(false, 401, 'Token inválido o vencido');
        }

        req.empleadoId = empleadoId;
        aceptar(true);
    },
});

registrar('servidor_iniciado', { puerto: PORT, origenes: ORIGENES_PERMITIDOS });

wss.on('connection', (ws, req) => {
    const empleadoId = req.empleadoId;
    let mensajesEnVentana = 0;
    let inicioVentana = Date.now();

    registrar('conexion', { empleadoId });

    ws.on('message', (mensaje, esBinario) => {
        if (Date.now() - inicioVentana > VENTANA_MS) {
            inicioVentana = Date.now();
            mensajesEnVentana = 0;
        }

        if (++mensajesEnVentana > MAX_MENSAJES) {
            registrar('limite_mensajes', { empleadoId });
            ws.close(1008, 'Demasiados mensajes');
            return;
        }

        const evento = mensaje.toString();

        if (esBinario || !EVENTOS_PERMITIDOS.has(evento)) {
            registrar('mensaje_rechazado', { empleadoId });
            return;
        }

        wss.clients.forEach((cliente) => {
            if (cliente !== ws && cliente.readyState === WebSocket.OPEN) {
                cliente.send(evento);
            }
        });
    });

    ws.on('close', () => registrar('desconexion', { empleadoId }));
});
