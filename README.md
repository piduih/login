# Login Module

A simple, complete login module with authentication functionality, built with Node.js and vanilla JavaScript.

## Features

- User registration and login
- Secure password hashing (SHA-256)
- Session management with tokens
- Password change functionality
- Admin user management
- HTML/CSS/JavaScript frontend interface
- Comprehensive test suite
- Input validation and error handling

## Files Overview

- `loginModule.js` - Core backend authentication logic
- `index.js` - Demo application showing usage
- `test.js` - Comprehensive test suite
- `index.html` - Frontend login interface
- `style.css` - Styling for the frontend
- `script.js` - Frontend JavaScript logic
- `package.json` - Node.js project configuration

## Quick Start

### Backend Usage (Node.js)

```bash
# Install dependencies (none required for basic functionality)
npm install

# Run demo
npm start

# Run tests
npm test
```

### Frontend Usage

Open `index.html` in a web browser to use the login interface.

**Demo Credentials:**
- Username: `admin`, Password: `admin123`
- Username: `demo`, Password: `demo123`

## API Reference

### LoginModule Class

#### Constructor
```javascript
const auth = new LoginModule();
```

#### Methods

**registerUser(username, password)**
- Registers a new user
- Returns: `true` on success
- Throws: Error if user exists or invalid input

**login(username, password)**
- Authenticates user login
- Returns: Object with `{success, sessionToken, username, message}`
- Throws: Error for invalid credentials

**logout(sessionToken)**
- Logs out user and invalidates session
- Returns: `true` on success
- Throws: Error for invalid session

**verifySession(sessionToken)**
- Checks if session is valid
- Returns: Object with `{valid, username, loginTime}` or `{valid: false, message}`

**changePassword(sessionToken, oldPassword, newPassword)**
- Changes user password
- Returns: `true` on success
- Throws: Error for invalid session or incorrect old password

**getUsers(sessionToken)** *(Admin only)*
- Lists all registered users
- Returns: Array of user objects
- Throws: Error for unauthorized access

## Usage Examples

### Basic Authentication Flow

```javascript
const LoginModule = require('./loginModule');
const auth = new LoginModule();

// Register a new user
try {
    auth.registerUser('john', 'securepassword');
    console.log('User registered successfully');
} catch (error) {
    console.error('Registration failed:', error.message);
}

// Login
try {
    const result = auth.login('john', 'securepassword');
    console.log('Login successful:', result.sessionToken);
    
    // Verify session
    const session = auth.verifySession(result.sessionToken);
    if (session.valid) {
        console.log('User is authenticated:', session.username);
    }
    
    // Logout
    auth.logout(result.sessionToken);
    console.log('Logged out successfully');
    
} catch (error) {
    console.error('Authentication failed:', error.message);
}
```

### Password Management

```javascript
// Change password
try {
    const loginResult = auth.login('john', 'securepassword');
    auth.changePassword(loginResult.sessionToken, 'securepassword', 'newsecurepassword');
    console.log('Password changed successfully');
} catch (error) {
    console.error('Password change failed:', error.message);
}
```

### Admin Functions

```javascript
// Admin login and user management
try {
    const adminLogin = auth.login('admin', 'admin123');
    const users = auth.getUsers(adminLogin.sessionToken);
    console.log('Registered users:', users);
} catch (error) {
    console.error('Admin operation failed:', error.message);
}
```

## Security Features

- **Password Hashing**: Passwords are hashed using SHA-256
- **Session Tokens**: Secure random session tokens for authentication
- **Input Validation**: Validates all user inputs
- **Error Handling**: Comprehensive error messages without exposing sensitive data
- **Admin Protection**: Restricted access to admin functions

## Frontend Interface

The HTML interface provides:

- **Login Form**: Username/password authentication
- **Registration Form**: New user signup
- **Dashboard**: Post-login user interface
- **Password Change**: Secure password updates
- **Responsive Design**: Mobile-friendly interface
- **Real-time Feedback**: Success/error messages

## Testing

The module includes comprehensive tests covering:

- User registration (valid/invalid inputs)
- Authentication (success/failure cases)
- Session management
- Password changes
- Admin functionality
- Error handling

Run tests with:
```bash
npm test
```

## Integration

### As a Node.js Module

```javascript
const LoginModule = require('./loginModule');
const auth = new LoginModule();

// Use in your application
app.post('/login', (req, res) => {
    try {
        const result = auth.login(req.body.username, req.body.password);
        res.json(result);
    } catch (error) {
        res.status(401).json({ error: error.message });
    }
});
```

### In a Web Application

Include the files in your web project:
- Copy `index.html`, `style.css`, and `script.js`
- Modify the frontend to connect to your backend API
- Replace the client-side storage with server communication

## Production Considerations

For production use, consider these enhancements:

1. **Database Storage**: Replace in-memory storage with a database
2. **Stronger Hashing**: Use bcrypt or argon2 instead of SHA-256
3. **Session Expiry**: Implement session timeouts
4. **Rate Limiting**: Add login attempt rate limiting
5. **HTTPS**: Ensure all communication is encrypted
6. **Input Sanitization**: Add additional input validation
7. **Logging**: Implement security event logging

## License

MIT License - Feel free to use in your projects.
