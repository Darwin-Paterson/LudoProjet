const express = require('express');
const http = require('http');
const { Server } = require("socket.io");
const TournamentSystem = require('./tournament-manager');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: { origin: "*" }
});

// Mock Session/DB middleware would go here
const getPlayerFromSocket = (socket) => {
    // In real app, verify JWT token here
    const { userId, username } = socket.handshake.query;
    return { id: userId, username };
};

io.on('connection', (socket) => {
    const player = getPlayerFromSocket(socket);
    console.log(`Player connected: ${player.username}`);

    // --- Tournament Events ---

    socket.on('create_tournament', ({ name, maxPlayers }) => {
        const id = TournamentSystem.createTournament(name, maxPlayers);
        socket.emit('tournament_created', { id });
    });

    socket.on('join_tournament', ({ tournamentId }) => {
        const res = TournamentSystem.joinTournament(tournamentId, player);
        if (res.error) socket.emit('error', res.error);
        else {
            socket.join(`tourn_${tournamentId}`);
            socket.emit('joined_tournament', { tournamentId });
        }
    });

    // --- Game Match Events ---

    socket.on('join_game', ({ roomId }) => {
        const game = TournamentSystem.getMatch(roomId);
        if (!game) return socket.emit('error', 'Match not found');

        // Verify player is in this match
        const isPlayer = game.players.find(p => p.id == player.id);
        if (!isPlayer) return socket.emit('error', 'Not a player in this match');

        socket.join(roomId);
        socket.emit('game_start', game.getPublicState());
    });

    socket.on('roll_dice', ({ roomId }) => {
        const game = TournamentSystem.getMatch(roomId);
        if (!game) return;

        const result = game.rollDice(player.id);
        if (result.error) return socket.emit('error', result.error);

        // Broadcast Roll
        io.to(roomId).emit('dice_rolled', { 
            value: result.value, 
            playerId: player.id 
        });

        // If turn skipped (3x6) or auto-skip
        if (result.event === 'turn_skipped' || result.autoSkip) {
             io.to(roomId).emit('turn_change', { turnIndex: game.turnIndex });
        }
    });

    socket.on('move_token', ({ roomId, tokenIndex }) => {
        const game = TournamentSystem.getMatch(roomId);
        if (!game) return;

        const result = game.moveToken(player.id, tokenIndex);
        if (result.error) return socket.emit('error', result.error);

        if (result.event === 'game_over') {
            io.to(roomId).emit('game_over', result.state);
            // Report to Tournament Manager
            // Find which tournament this match belongs to (omitted for brevity, requires lookup)
            // TournamentSystem.handleMatchWin(tournId, roomId, result.winner);
        } else {
            io.to(roomId).emit('board_update', result.state);
        }
    });

    socket.on('disconnect', () => {
        // Handle Disconnection (Timeout logic needed here)
        console.log("Player disconnected");
    });
});

server.listen(3000, () => {
    console.log('Ludo Server running on *:3000');
});