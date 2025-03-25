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
// WebSocket connection event
io.on('connection', (socket) => {
  console.log('A user connected.');

  // Handle incoming messages
  socket.on('sendMessage', (message) => {
    console.log('Message received:', message); // Check the message content
    io.emit('newMessage', message); // Broadcast the message
  });

  // Handle disconnections
  socket.on('disconnect', () => {
    console.log('A user disconnected.');
  });
});



// Start the server
const PORT = 3000; // Or any port you prefer
server.listen(PORT, () => {
  console.log(`WebSocket server running on ws://localhost:${PORT}`);
});