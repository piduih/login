// Simple client-side simulation of the login module
// In a real application, this would communicate with a backend server

class ClientLoginModule {
    constructor() {
        this.users = new Map();
        this.currentSession = null;
        this.currentUser = null;
        
        // Add default admin user
        this.users.set('admin', {
            username: 'admin',
            password: this.hashPassword('admin123'),
            createdAt: new Date()
        });
        
        // Add demo user
        this.users.set('demo', {
            username: 'demo',
            password: this.hashPassword('demo123'),
            createdAt: new Date()
        });
    }

    // Simple hash function for demo purposes
    hashPassword(password) {
        let hash = 0;
        for (let i = 0; i < password.length; i++) {
            const char = password.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash).toString();
    }

    register(username, password) {
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

        // Generate simple session token
        this.currentSession = Math.random().toString(36).substring(2, 15);
        this.currentUser = username;

        return {
            success: true,
            sessionToken: this.currentSession,
            username,
            message: 'Login successful'
        };
    }

    logout() {
        this.currentSession = null;
        this.currentUser = null;
        return true;
    }

    isLoggedIn() {
        return this.currentSession !== null;
    }

    changePassword(oldPassword, newPassword) {
        if (!this.isLoggedIn()) {
            throw new Error('Not logged in');
        }

        const user = this.users.get(this.currentUser);
        const hashedOldPassword = this.hashPassword(oldPassword);
        
        if (user.password !== hashedOldPassword) {
            throw new Error('Current password is incorrect');
        }

        const hashedNewPassword = this.hashPassword(newPassword);
        user.password = hashedNewPassword;
        
        return true;
    }
}

// Global instance
const auth = new ClientLoginModule();

// UI Helper Functions
function showMessage(message, type = 'info') {
    const messageArea = document.getElementById('messageArea');
    messageArea.textContent = message;
    messageArea.className = `message-area ${type}`;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageArea.style.display = 'none';
    }, 5000);
}

function hideAllForms() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('dashboard').style.display = 'none';
    document.getElementById('changePasswordForm').style.display = 'none';
}

function showLoginForm() {
    hideAllForms();
    document.getElementById('loginForm').style.display = 'block';
    clearForms();
}

function showRegisterForm() {
    hideAllForms();
    document.getElementById('registerForm').style.display = 'block';
    clearForms();
}

function showDashboard() {
    hideAllForms();
    document.getElementById('dashboard').style.display = 'block';
    document.getElementById('currentUser').textContent = auth.currentUser;
}

function showChangePasswordForm() {
    hideAllForms();
    document.getElementById('changePasswordForm').style.display = 'block';
    clearForms();
}

function clearForms() {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => input.value = '');
}

// Event Handlers
function handleLogin(event) {
    event.preventDefault();
    
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    
    try {
        const result = auth.login(username, password);
        showMessage(result.message, 'success');
        showDashboard();
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

function handleRegister(event) {
    event.preventDefault();
    
    const username = document.getElementById('registerUsername').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (password !== confirmPassword) {
        showMessage('Passwords do not match', 'error');
        return;
    }
    
    try {
        auth.register(username, password);
        showMessage('Registration successful! Please login.', 'success');
        showLoginForm();
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

function handleChangePassword(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmNewPassword = document.getElementById('confirmNewPassword').value;
    
    if (newPassword !== confirmNewPassword) {
        showMessage('New passwords do not match', 'error');
        return;
    }
    
    try {
        auth.changePassword(currentPassword, newPassword);
        showMessage('Password changed successfully!', 'success');
        showDashboard();
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

function handleLogout() {
    auth.logout();
    showMessage('Logged out successfully', 'success');
    showLoginForm();
}

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    showLoginForm();
    
    // Show demo credentials info
    showMessage('Demo: Use "admin/admin123" or "demo/demo123" to login, or register a new account', 'info');
});