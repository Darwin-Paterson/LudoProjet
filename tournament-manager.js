const { v4: uuidv4 } = require('uuid');
const LudoGame = require('./ludo-engine');

class TournamentManager {
    constructor() {
        this.tournaments = new Map(); // id -> tournament data
        this.activeMatches = new Map(); // matchId -> GameInstance
    }

    createTournament(name, maxPlayers, type = 'KNOCKOUT') {
        const id = uuidv4();
        this.tournaments.set(id, {
            id,
            name,
            maxPlayers, // 4, 8, 16
            type,
            players: [],
            status: 'REGISTRATION', // REGISTRATION, ACTIVE, COMPLETED
            rounds: [], // Array of Arrays of matches
            currentRound: 0
        });
        return id;
    }

    joinTournament(tournamentId, player) {
        const t = this.tournaments.get(tournamentId);
        if (!t || t.status !== 'REGISTRATION') return { error: "Closed" };
        if (t.players.length >= t.maxPlayers) return { error: "Full" };

        t.players.push(player);

        // Auto-start if full
        if (t.players.length === t.maxPlayers) {
            this.startTournament(tournamentId);
        }
        return { success: true };
    }

    startTournament(tournamentId) {
        const t = this.tournaments.get(tournamentId);
        t.status = 'ACTIVE';
        
        // Shuffle players
        const shuffled = t.players.sort(() => 0.5 - Math.random());
        
        // Generate Round 1 Matches (Assuming 4 players per match for Ludo)
        // If 16 players -> 4 matches of 4.
        const round1 = [];
        const playersPerMatch = 4; // Ludo Standard
        
        for (let i = 0; i < shuffled.length; i += playersPerMatch) {
            const matchPlayers = shuffled.slice(i, i + playersPerMatch);
            // Assign colors
            const colors = ['red', 'blue', 'green', 'yellow'];
            const gamePlayers = matchPlayers.map((p, idx) => ({ 
                ...p, 
                color: colors[idx] 
            }));

            const matchId = uuidv4();
            const game = new LudoGame(matchId, gamePlayers);
            
            this.activeMatches.set(matchId, game);
            
            round1.push({
                matchId,
                players: gamePlayers,
                status: 'IN_PROGRESS',
                winner: null
            });
        }
        
        t.rounds.push(round1);
        console.log(`Tournament ${t.name} started with ${round1.length} matches.`);
    }

    getMatch(matchId) {
        return this.activeMatches.get(matchId);
    }

    // Called when a GameEngine reports a winner
    handleMatchWin(tournamentId, matchId, winnerData) {
        const t = this.tournaments.get(tournamentId);
        const round = t.rounds[t.currentRound];
        const matchObj = round.find(m => m.matchId === matchId);
        
        if (matchObj) {
            matchObj.status = 'COMPLETED';
            matchObj.winner = winnerData;
            this.activeMatches.delete(matchId); // Cleanup memory
            
            // Check if round is complete
            if (round.every(m => m.status === 'COMPLETED')) {
                this._advanceRound(t);
            }
        }
    }

    _advanceRound(tournament) {
        const prevRound = tournament.rounds[tournament.currentRound];
        const winners = prevRound.map(m => m.winner);
        
        if (winners.length === 1) {
            tournament.status = 'COMPLETED';
            tournament.champion = winners[0];
            console.log("Tournament Winner:", winners[0].username);
            return;
        }

        // Create next round matches
        const nextRound = [];
        const playersPerMatch = 4;
        
        for (let i = 0; i < winners.length; i += playersPerMatch) {
            const matchPlayers = winners.slice(i, i + playersPerMatch);
            // Re-assign colors for new match
            const colors = ['red', 'blue', 'green', 'yellow'];
            const gamePlayers = matchPlayers.map((p, idx) => ({
                id: p.id,
                username: p.username,
                color: colors[idx]
            }));

            const matchId = uuidv4();
            const game = new LudoGame(matchId, gamePlayers);
            this.activeMatches.set(matchId, game);

            nextRound.push({ matchId, players: gamePlayers, status: 'IN_PROGRESS' });
        }

        tournament.rounds.push(nextRound);
        tournament.currentRound++;
        console.log("Advanced to Round", tournament.currentRound + 1);
    }
}

module.exports = new TournamentManager(); // Singleton