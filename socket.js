import express from 'express';
import { Server } from 'socket.io';
import http from 'http';

const app = express();
const server = http.createServer(app);
const io = new Server(server);

let diagramContents = {}; 

io.on('connection', (socket) => {
    console.log('Nuevo usuario conectado');

    // Unirse a una sala específica
    socket.on('joinRoom', (diagramID) => {
        socket.join(diagramID);
        console.log(`Un usuario se ha unido al diagrama ${diagramID}`);

        // Si ya existe contenido para ese diagrama, enviar el contenido actual al nuevo usuario
        if (diagramContents[diagramID]) {
            socket.emit('updateTextarea', diagramContents[diagramID]);
        }
    });

    // Escuchar cambios en el textarea de un usuario
    socket.on('editTextarea', ({ diagramID, content }) => {
        // Actualizar el contenido del diagrama específico
        diagramContents[diagramID] = content;

        // Enviar el nuevo contenido a todos los usuarios en la misma sala (excepto al remitente)
        socket.to(diagramID).emit('updateTextarea', content);
    });

    // Evento cuando un usuario se desconecta
    socket.on('disconnect', () => {
        console.log('Usuario desconectado');
    });
});

server.listen(3000, (err) => {
    if (err) throw new Error(err);
    console.log('Servidor escuchando en http://localhost:3000');
});