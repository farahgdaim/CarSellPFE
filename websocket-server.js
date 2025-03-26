const express = require('express');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
const server = http.createServer(app);

const io = new Server(server, {
    cors: {
      origin: 'http://localhost:4200', // Your Angular app's URL
      methods: ['GET', 'POST'],        // Allowed HTTP methods
      credentials: true                // If using cookies/auth
    }
  });
// On your socket server (example using Node.js + socket.io)
io.on('connection', (socket) => {
  console.log('New socket connected');

  socket.on('joinConversation', (conversationId) => {
    socket.join(conversationId);
    console.log(`Socket joined room: ${conversationId}`);
  });

  socket.on('sendMessage', (messageData) => {
    const room = messageData.conversationId;
    socket.to(room).emit('newMessage', messageData);
    socket.emit('newMessage', messageData);
  });
});


// Start the server
const PORT = 3000; // Or any port you prefer
server.listen(PORT, () => {
  console.log(`WebSocket server running on ws://localhost:${PORT}`);
});