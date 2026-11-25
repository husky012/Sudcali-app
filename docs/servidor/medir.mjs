// Mediciones de la Práctica 5 con Apache Bench.
//
//   node docs/servidor/medir.mjs comprobar   reparto entre nodos, sesión y salud
//   node docs/servidor/medir.mjs medir       los escenarios; llena la tabla de docs/practica5.md
//   node docs/servidor/medir.mjs leer a.txt  interpreta una salida de ab ya guardada
//
// Requiere el montaje levantado (docs/practica5.md). Todo lo que se escribe viene de ab: nada se estima.
import { spawn, spawnSync } from 'node:child_process';
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { cpus, totalmem } from 'node:os';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const PROYECTO = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const SALIDAS = join(PROYECTO, 'docs', 'mediciones');
const INFORME = join(PROYECTO, 'docs', 'practica5.md');
const NODO = 'http://172.28.0.11:8000';
const BALANCEADOR = 'http://172.28.0.10';
const PUBLICO = process.env.URL_BALANCEADOR ?? 'http://127.0.0.1:8080';
const RUTA = process.env.RUTA ?? '/tienda';
const N = Number(process.env.N ?? 1000);
const C = Number(process.env.C ?? 20);
const N_CAIDA = Number(process.env.N_CAIDA ?? 20000);
const REPETICIONES = 3;

const esperar = (ms) => new Promise((listo) => setTimeout(listo, ms));

function docker(args, entorno = {}) {
    const r = spawnSync('docker', ['compose', ...args], { cwd: PROYECTO, encoding: 'utf8', env: { ...process.env, ...entorno } });
    if (r.error) throw new Error('No se encontró Docker. Instala Docker Desktop y ábrelo.');
    if (r.status !== 0) throw new Error(`docker compose ${args.join(' ')}\n${r.stderr || r.stdout}`);
    return r.stdout;
}

function ab(url, peticiones) {
    return new Promise((resolver, rechazar) => {
        const proceso = spawn('docker', ['compose', 'run', '--rm', '-T', 'ab', '-l', '-n', String(peticiones), '-c', String(C), url], { cwd: PROYECTO });
        let texto = '';
        proceso.stdout.on('data', (d) => (texto += d));
        proceso.stderr.on('data', (d) => (texto += d));
        proceso.on('error', rechazar);
        proceso.on('close', () => (texto.includes('Requests per second') ? resolver(texto) : rechazar(new Error(`ab no terminó:\n${texto}`))));
    });
}

export function leer(texto) {
    const numero = (patron) => {
        const m = texto.match(patron);
        return m ? Number(m[1]) : null;
    };
    return {
        n: numero(/Complete requests:\s+(\d+)/),
        c: numero(/Concurrency Level:\s+(\d+)/),
        rps: numero(/Requests per second:\s+([\d.]+)/),
        p50: numero(/^\s*50%\s+(\d+)/m),
        p90: numero(/^\s*90%\s+(\d+)/m),
        p99: numero(/^\s*99%\s+(\d+)/m),
        p100: numero(/^\s*100%\s+(\d+)/m),
        fallidas: numero(/Failed requests:\s+(\d+)/),
        // ab no cuenta como fallidas las respuestas 3xx, 4xx y 5xx: se revisan aparte.
        no2xx: numero(/Non-2xx responses:\s+(\d+)/) ?? 0,
    };
}

async function esperarSalud() {
    for (let i = 0; i < 60; i++) {
        try {
            if ((await fetch(`${PUBLICO}/salud`)).ok) return;
        } catch {
            // Todavía no contesta.
        }
        await esperar(1000);
    }
    throw new Error(`${PUBLICO}/salud no contesta`);
}

function nodosSanos() {
    return ['app1', 'app2'].every((s) => docker(['ps', '--format', '{{.Health}}', s]).trim() === 'healthy');
}

async function esperarNodos() {
    for (let i = 0; i < 60 && !nodosSanos(); i++) await esperar(1000);
    if (!nodosSanos()) throw new Error('Los dos nodos no están sanos: docker compose ps');
    // Más que fail_timeout, para que Nginx vuelva a usar los dos.
    await esperar(11000);
}

async function usarAlgoritmo(algoritmo) {
    docker(['up', '-d', 'balanceador'], { ALGORITMO: algoritmo });
    await esperarSalud();
}

/** Calienta una vez (se descarta), mide tres y se queda con la mediana por segundo. */
async function escenario(clave, titulo, url) {
    console.log(`\n${titulo}`);
    await ab(url, N);
    console.log('  calentamiento descartado');
    const corridas = [];
    for (let i = 1; i <= REPETICIONES; i++) {
        const texto = await ab(url, N);
        writeFileSync(join(SALIDAS, `${clave}-${i}.txt`), texto);
        const r = { ...leer(texto), archivo: `${clave}-${i}.txt` };
        corridas.push(r);
        console.log(`  corrida ${i}: ${r.rps}/s  50%=${r.p50}  90%=${r.p90}  99%=${r.p99}  fallidas=${r.fallidas}  no-2xx=${r.no2xx}`);
    }
    const ordenadas = [...corridas].sort((a, b) => a.rps - b.rps);
    return { titulo, corridas, ...ordenadas[1] };
}

async function caida(rps) {
    const titulo = 'Apagando un nodo (least_conn)';
    const mitad = Math.round(((N_CAIDA / rps) * 1000) * 0.4);
    console.log(`\n${titulo}: ${N_CAIDA} peticiones; app1 se apaga a los ${Math.round(mitad / 1000)} s`);
    let terminada = false;
    const prueba = ab(BALANCEADOR + RUTA, N_CAIDA).finally(() => (terminada = true));
    // El arranque del contenedor de ab tarda un poco; con 20000 peticiones sigue cayendo a media prueba.
    await esperar(mitad);
    const apagadoDurante = !terminada;
    docker(['stop', 'app1']);
    console.log('  app1 apagado');
    try {
        const texto = await prueba;
        writeFileSync(join(SALIDAS, 'caida-1.txt'), texto);
        const r = { titulo, ...leer(texto), archivo: 'caida-1.txt', apagadoDurante };
        console.log(`  ${r.rps}/s  99%=${r.p99}  100%=${r.p100}  fallidas=${r.fallidas}  no-2xx=${r.no2xx}`);
        return r;
    } finally {
        docker(['start', 'app1']);
    }
}

const cambio = (antes, despues) => `${despues >= antes ? '+' : ''}${(((despues - antes) / antes) * 100).toFixed(1)} %`;

function escribirInforme(solo, rr, lc, apagado) {
    const fila = (r, nombre) => `| ${nombre} | ${r.n} / ${r.c} | ${r.rps} | ${r.p50} ms | ${r.p90} ms | ${r.p99} ms | ${r.fallidas} | ${r.no2xx} | [salida](mediciones/${r.archivo}) |`;
    const corridas = [solo, rr, lc].map((e) => `| ${e.titulo} | ${e.corridas.map((r) => `${r.rps}`).join(' | ')} |`).join('\n');
    const mejor = rr.p99 === lc.p99 ? 'los dos tuvieron el mismo 99 %' : `${rr.p99 < lc.p99 ? 'round robin' : 'least_conn'} tuvo el menor 99 %`;
    const texto = `Medido el ${new Date().toLocaleString('es-MX')} en un solo equipo: ${cpus()[0].model.trim()}, ${cpus().length} hilos, ${Math.round(totalmem() / 2 ** 30)} GB. Página: \`${RUTA}\`. Cada fila es la mediana de ${REPETICIONES} corridas, después de una de calentamiento.

| Escenario | -n / -c | Por segundo | 50 % | 90 % | 99 % | Fallidas | No 2xx | Evidencia |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
${fila(solo, 'Un nodo solo')}
${fila(rr, 'Con balanceador (round robin)')}
${fila(lc, 'Con balanceador (least_conn)')}
${fila(apagado, 'Apagando un nodo')}

Peticiones por segundo de cada corrida:

| Escenario | 1 | 2 | 3 |
| --- | --- | --- | --- |
${corridas}

Lo que dicen los números:

- **¿Mejoró con el balanceador?** Frente a un nodo solo (${solo.rps}/s): round robin ${cambio(solo.rps, rr.rps)} y least_conn ${cambio(solo.rps, lc.rps)} en peticiones por segundo. El 99 % pasó de ${solo.p99} ms a ${rr.p99} ms y ${lc.p99} ms.
- **¿Qué algoritmo se comportó mejor?** Por el 99 %, ${mejor} (round robin ${rr.p99} ms, least_conn ${lc.p99} ms).
- **¿Cuánto tardó en sacar el nodo caído?** La petición más lenta (100 %) tardó ${apagado.p100} ms. Fallidas: ${apagado.fallidas}. Respuestas que no fueron 2xx: ${apagado.no2xx}.${apagado.apagadoDurante ? '' : ' **Ojo: la prueba ya había terminado cuando se apagó el nodo; repetirla con un N_CAIDA mayor.**'}
`;
    const informe = readFileSync(INFORME, 'utf8');
    const marcas = /(<!-- resultados:inicio -->)[\s\S]*?(<!-- resultados:fin -->)/;
    if (!marcas.test(informe)) throw new Error('docs/practica5.md no tiene las marcas de resultados');
    writeFileSync(INFORME, informe.replace(marcas, (_, a, b) => `${a}\n${texto}${b}`));
}

async function medir() {
    mkdirSync(SALIDAS, { recursive: true });
    await esperarSalud();
    await esperarNodos();
    const solo = await escenario('nodo-solo', 'Un nodo solo', NODO + RUTA);
    await usarAlgoritmo('round_robin');
    const rr = await escenario('round-robin', 'Round robin', BALANCEADOR + RUTA);
    await usarAlgoritmo('least_conn');
    const lc = await escenario('least-conn', 'least_conn', BALANCEADOR + RUTA);
    const apagado = await caida(lc.rps);
    escribirInforme(solo, rr, lc, apagado);
    console.log('\nListo: la tabla quedó en docs/practica5.md y las salidas en docs/mediciones/.');
}

/** Cookies de una respuesta, en el formato que se envía de vuelta. */
function cookies(respuesta, anteriores = {}) {
    for (const c of respuesta.headers.getSetCookie()) {
        const [par] = c.split(';');
        const i = par.indexOf('=');
        anteriores[par.slice(0, i)] = par.slice(i + 1);
    }
    return anteriores;
}
const enviar = (c) => Object.entries(c).map(([k, v]) => `${k}=${v}`).join('; ');

async function comprobar() {
    const salud = await fetch(`${PUBLICO}/salud`);
    console.log('salud      ', salud.status, await salud.text());

    const reparto = {};
    for (let i = 0; i < 20; i++) {
        const r = await fetch(`${PUBLICO}/salud`);
        const nodo = r.headers.get('x-servidor') ?? 'sin nombre';
        reparto[nodo] = (reparto[nodo] ?? 0) + 1;
    }
    console.log('reparto    ', JSON.stringify(reparto));

    // Sesión: se agrega un producto al carrito y se lee varias veces; debe seguir ahí en los dos nodos.
    const catalogo = await (await fetch(`${PUBLICO}/catalogo`)).text();
    const producto = catalogo.match(/\/producto\/[a-z0-9-]+/)?.[0];
    if (!producto) throw new Error('El catálogo no tiene productos: docker compose run --rm preparar');
    const pagina = await fetch(PUBLICO + producto);
    const galleta = cookies(pagina);
    const html = await pagina.text();
    const token = html.match(/name="_token" value="([^"]+)"/)[1];
    const variante = html.match(/name="inventario_id" value="(\d+)"/)[1];
    const alta = await fetch(`${PUBLICO}/carrito`, {
        method: 'POST',
        redirect: 'manual',
        headers: { Cookie: enviar(galleta), 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ _token: token, inventario_id: variante, cantidad: '1' }),
    });
    cookies(alta, galleta);
    const vistos = {};
    for (let i = 0; i < 10; i++) {
        const r = await fetch(`${PUBLICO}/carrito`, { headers: { Cookie: enviar(galleta) } });
        cookies(r, galleta);
        const nodo = r.headers.get('x-servidor') ?? 'sin nombre';
        vistos[nodo] ??= { conCarrito: 0, vacio: 0 };
        vistos[nodo][/contador-carrito">\s*1\s*</.test(await r.text()) ? 'conCarrito' : 'vacio']++;
    }
    console.log('sesión     ', JSON.stringify(vistos));
    const bien = Object.values(vistos).every((v) => v.vacio === 0);
    console.log(bien ? 'La sesión sobrevive al cambio de nodo.' : 'La sesión SE PIERDE al cambiar de nodo.');
    process.exitCode = bien ? 0 : 1;
}

const [orden, archivo] = process.argv.slice(2);
try {
    if (orden === 'medir') await medir();
    else if (orden === 'comprobar') await comprobar();
    else if (orden === 'leer') console.log(leer(readFileSync(archivo, 'utf8')));
    else console.log('Uso: node docs/servidor/medir.mjs comprobar | medir | leer archivo.txt');
} catch (error) {
    console.error(error.message);
    process.exitCode = 1;
}
