const crypto = require('crypto');

/**
 * Simple Login Module
 * Provides basic authentication functionality
 */
class LoginModule {
    constructor() {
        // In-memory user storage (in production, use a proper database)
        this.users = new Map();
        this.sessions = new Map();
        
        // Add default admin user
        this.registerUser('admin', 'admin123');
    }

    /**
     * Hash password using SHA-256
     * @param {string} password - Plain text password
     * @returns {string} Hashed password
     */
    hashPassword(password) {
        return crypto.createHash('sha256').update(password).digest('hex');
    }

    /**
     * Register a new user
     * @param {string} username - Username
     * @param {string} password - Plain text password
     * @returns {boolean} Success status
     */
    registerUser(username, password) {
        if (!username || !password) {
            throw new Error('Username and password are required');
        }
        
        if (this.users.has(username)) {
            throw new Error('User already exists');
        }

        const hashedPassword = this.hashPassword(password);
        this.users.set(username, {
            username,
            password: hashedPassword,
            createdAt: new Date()
        });

        return true;
    }

    /**
     * Authenticate user login
     * @param {string} username - Username
     * @param {string} password - Plain text password
     * @returns {object} Login result with session token
     */
    login(username, password) {
        if (!username || !password) {
            throw new Error('Username and password are required');
        }

        const user = this.users.get(username);
        if (!user) {
            throw new Error('Invalid credentials');
        }

        const hashedPassword = this.hashPassword(password);
        if (user.password !== hashedPassword) {
            throw new Error('Invalid credentials');
        }

        // Generate session token
        const sessionToken = crypto.randomBytes(32).toString('hex');
        this.sessions.set(sessionToken, {
            username,
            loginTime: new Date(),
            isActive: true
        });

        return {
            success: true,
            sessionToken,
            username,
            message: 'Login successful'
        };
    }

    /**
     * Logout user and invalidate session
     * @param {string} sessionToken - Session token
     * @returns {boolean} Success status
     */
    logout(sessionToken) {
        if (!sessionToken) {
            throw new Error('Session token is required');
        }

        const session = this.sessions.get(sessionToken);
        if (!session) {
            throw new Error('Invalid session');
        }

        session.isActive = false;
        this.sessions.delete(sessionToken);
        return true;
    }

    /**
     * Verify if session is valid
     * @param {string} sessionToken - Session token
     * @returns {object} Session validation result
     */
    verifySession(sessionToken) {
        if (!sessionToken) {
            return { valid: false, message: 'No session token provided' };
        }

        const session = this.sessions.get(sessionToken);
        if (!session || !session.isActive) {
            return { valid: false, message: 'Invalid or expired session' };
        }

        return {
            valid: true,
            username: session.username,
            loginTime: session.loginTime
        };
    }

    /**
     * Get all registered users (admin function)
     * @param {string} sessionToken - Admin session token
     * @returns {array} List of users
     */
    getUsers(sessionToken) {
        const session = this.verifySession(sessionToken);
        if (!session.valid) {
            throw new Error('Invalid session');
        }

        // Only admin can view users
        if (session.username !== 'admin') {
            throw new Error('Unauthorized access');
        }

        return Array.from(this.users.values()).map(user => ({
            username: user.username,
            createdAt: user.createdAt
        }));
    }

    /**
     * Change user password
     * @param {string} sessionToken - User session token
     * @param {string} oldPassword - Current password
     * @param {string} newPassword - New password
     * @returns {boolean} Success status
     */
    changePassword(sessionToken, oldPassword, newPassword) {
        const session = this.verifySession(sessionToken);
        if (!session.valid) {
            throw new Error('Invalid session');
        }

        const user = this.users.get(session.username);
        const hashedOldPassword = this.hashPassword(oldPassword);
        
        if (user.password !== hashedOldPassword) {
            throw new Error('Current password is incorrect');
        }

        const hashedNewPassword = this.hashPassword(newPassword);
        user.password = hashedNewPassword;
        
        return true;
    }
}

module.exports = LoginModule;