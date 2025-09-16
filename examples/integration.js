// Example: Integrating the login module into an existing Express app
import express from 'express';
import { authRoutes } from './src/routes/auth.js';
import { authenticateToken } from './src/middleware/auth.js';

const app = express();
app.use(express.json());

// 1. Add the login module to your app
app.use('/api/auth', authRoutes);

// 2. Protect your own routes with the authentication middleware
app.get('/api/protected-resource', authenticateToken, (req, res) => {
  res.json({
    message: 'This is a protected resource',
    user: req.user // Contains userId and email from JWT
  });
});

// 3. Create your own routes that require authentication
app.get('/api/user-data', authenticateToken, (req, res) => {
  // req.user contains { userId, email } from the JWT token
  const userData = {
    userId: req.user.userId,
    email: req.user.email,
    customData: 'Your app-specific data here'
  };
  res.json(userData);
});

// 4. Optional: Add custom user registration logic
app.post('/api/custom-register', async (req, res) => {
  // You can extend the registration process with your own logic
  // For example, sending welcome emails, setting up user preferences, etc.
  
  // First, use the standard registration
  // Then add your custom logic here
  
  res.json({ message: 'Custom registration logic' });
});

app.listen(3001, () => {
  console.log('Your app with login module running on port 3001');
});