import { WebSocketServer, WebSocket } from 'ws';

const PORT = process.env.PORT || 3000;
const wss = new WebSocketServer({ port: PORT });

console.log(`Servidor WebSocket corriendo en el puerto ${PORT}`);

wss.on('connection', (ws) => {
    console.log('Nuevo cliente conectado desde el grupo');

    ws.on('message', (message) => {
        console.log(`Mensaje recibido: ${message}`);
        
        wss.clients.forEach((client) => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(message.toString());
            }
        });
    });

    ws.on('close', () => {
        console.log('Cliente desconectado');
    });
});