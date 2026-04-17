// ludo-engine.js
const SAFE_CELLS = [1, 9, 14, 22, 27, 35, 40, 48]; // Standard safe spots (Global Indices)
// Starting positions on Global Path (1-52)
const START_POS = {
    red: 1,
    blue: 14,
    green: 27,
    yellow: 40
};

class LudoGame {
    constructor(matchId, players, config = {}) {
        this.matchId = matchId;
        this.players = players; // Array of { id, color, username }
        this.turnIndex = 0; // Index in players array
        this.diceValue = null;
        this.sixCount = 0; // Track consecutive 6s
        this.gameState = 'WAITING'; // WAITING, PLAYING, FINISHED
        this.winner = null;
        
        // 4 tokens per player. State: 'HOME', 'PATH', 'HOME_STRETCH', 'WIN'
        // position: 0 (if Home), 1-52 (Global), 1-6 (Home Stretch)
        this.tokens = {}; 
        this.players.forEach(p => {
            this.tokens[p.color] = [
                { id: 0, state: 'HOME', pos: 0 },
                { id: 1, state: 'HOME', pos: 0 },
                { id: 2, state: 'HOME', pos: 0 },
                { id: 3, state: 'HOME', pos: 0 }
            ];
        });
    }

    startGame() {
        this.gameState = 'PLAYING';
        this.turnIndex = 0;
        return this.getPublicState();
    }

    rollDice(playerId) {
        if (this.players[this.turnIndex].id !== playerId) return { error: "Not your turn" };
        if (this.diceValue !== null) return { error: "Dice already rolled" };

        this.diceValue = Math.floor(Math.random() * 6) + 1;
        
        // Rule: 3 consecutive 6s cancels turn
        if (this.diceValue === 6) {
            this.sixCount++;
            if (this.sixCount === 3) {
                this._nextTurn();
                return { event: 'turn_skipped', reason: '3_consecutive_sixes', value: 6 };
            }
        } else {
            this.sixCount = 0;
        }

        // Check if player has valid moves
        if (!this._hasValidMoves(this.players[this.turnIndex].color, this.diceValue)) {
            setTimeout(() => this._nextTurn(), 1000); // Auto skip if no moves
            return { event: 'dice_rolled', value: this.diceValue, autoSkip: true };
        }

        return { event: 'dice_rolled', value: this.diceValue, autoSkip: false };
    }

    moveToken(playerId, tokenIndex) {
        if (this.players[this.turnIndex].id !== playerId) return { error: "Not your turn" };
        if (!this.diceValue) return { error: "Roll dice first" };

        const color = this.players[this.turnIndex].color;
        const token = this.tokens[color][tokenIndex];

        // 1. Validate Move Logic
        if (token.state === 'HOME' && this.diceValue !== 6) return { error: "Need 6 to start" };
        if (token.state === 'WIN') return { error: "Token already finished" };

        // 2. Calculate New Position
        let moveResult = this._calculateNewPosition(color, token, this.diceValue);
        
        if (!moveResult.valid) return { error: "Invalid move" };

        // 3. Apply Move
        token.state = moveResult.newState;
        token.pos = moveResult.newPos;

        let extraTurn = (this.diceValue === 6);
        let killedOpponent = false;

        // 4. Check Collisions (Kill)
        if (token.state === 'PATH') {
            const killResult = this._checkKill(color, token.pos);
            if (killResult) {
                killedOpponent = true;
                extraTurn = true; // Kill grants extra turn
            }
        }

        // 5. Check Win Condition for Token
        if (token.state === 'WIN') {
            extraTurn = true; // Reaching center grants extra turn
            if (this._checkPlayerWin(color)) {
                this.gameState = 'FINISHED';
                this.winner = this.players[this.turnIndex];
                return { event: 'game_over', winner: this.winner, state: this.getPublicState() };
            }
        }

        // 6. Conclude Turn
        this.diceValue = null; // Reset dice
        if (!extraTurn) {
            this._nextTurn();
        }

        return { event: 'move_made', state: this.getPublicState() };
    }

    // --- Internal Logic Helpers ---

    _calculateNewPosition(color, token, steps) {
        if (token.state === 'HOME') {
            return { valid: true, newState: 'PATH', newPos: START_POS[color] };
        }

        if (token.state === 'PATH') {
            let currentGlobal = token.pos;
            let target = currentGlobal + steps;

            // Check if entering Home Stretch
            // Logic: Calculate distance from start, if > 50, enter local path
            // Simplified relative logic for this example:
            let endOfBoard = (START_POS[color] - 2 + 52) % 52; 
            if (endOfBoard === 0) endOfBoard = 52;

            // Complex logic omitted for brevity: Check if passing home entrance
            // For now, simple wrap around 52
            if (target > 52) target -= 52;
            
            // TODO: Implement exact check for entering home column based on color
            // If passes home entrance -> switch state to HOME_STRETCH
            
            return { valid: true, newState: 'PATH', newPos: target };
        }
        
        // Handle HOME_STRETCH logic (1-6)
        if (token.state === 'HOME_STRETCH') {
            if (token.pos + steps === 6) return { valid: true, newState: 'WIN', newPos: 6 };
            if (token.pos + steps < 6) return { valid: true, newState: 'HOME_STRETCH', newPos: token.pos + steps };
            return { valid: false }; // Overshoot
        }

        return { valid: false };
    }

    _checkKill(attackerColor, pos) {
        if (SAFE_CELLS.includes(pos)) return false;

        let killed = false;
        this.players.forEach(p => {
            if (p.color !== attackerColor) {
                this.tokens[p.color].forEach(t => {
                    if (t.state === 'PATH' && t.pos === pos) {
                        t.state = 'HOME';
                        t.pos = 0;
                        killed = true;
                    }
                });
            }
        });
        return killed;
    }

    _checkPlayerWin(color) {
        return this.tokens[color].every(t => t.state === 'WIN');
    }

    _hasValidMoves(color, dice) {
        // Loop through tokens, pretend to move, see if valid
        return this.tokens[color].some(t => {
            if (t.state === 'WIN') return false;
            if (t.state === 'HOME' && dice !== 6) return false;
            const res = this._calculateNewPosition(color, t, dice);
            return res.valid;
        });
    }

    _nextTurn() {
        this.diceValue = null;
        this.turnIndex = (this.turnIndex + 1) % this.players.length;
    }

    getPublicState() {
        return {
            matchId: this.matchId,
            players: this.players,
            currentTurn: this.players[this.turnIndex].id,
            diceValue: this.diceValue,
            tokens: this.tokens,
            winner: this.winner
        };
    }
}

module.exports = LudoGame;