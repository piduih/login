import express from 'express';
import { users } from '../utils/storage.js';
import { generateMockUsers, clearAllUsers } from '../utils/mockData.js';

const router = express.Router();

// Get all users (development only)
router.get('/users', (req, res) => {
  const sanitizedUsers = users.map(user => ({
    id: user.id,
    email: user.email,
    name: user.name,
    createdAt: user.createdAt,
    updatedAt: user.updatedAt
  }));

  res.json({
    count: sanitizedUsers.length,
    users: sanitizedUsers
  });
});

// Generate mock users for testing
router.post('/generate-mock-users', async (req, res) => {
  try {
    const { count = 5 } = req.body;
    const mockUsers = await generateMockUsers(count);
    
    res.json({
      message: `Generated ${mockUsers.length} mock users`,
      users: mockUsers.map(user => ({
        id: user.id,
        email: user.email,
        name: user.name,
        createdAt: user.createdAt
      }))
    });
  } catch (error) {
    res.status(500).json({ error: 'Failed to generate mock users' });
  }
});

// Clear all users (development only)
router.delete('/users', (req, res) => {
  const count = users.length;
  clearAllUsers();
  
  res.json({
    message: `Deleted ${count} users`,
    remainingUsers: users.length
  });
});

// Get system info
router.get('/info', (req, res) => {
  res.json({
    environment: process.env.NODE_ENV,
    nodeVersion: process.version,
    uptime: process.uptime(),
    memory: process.memoryUsage(),
    totalUsers: users.length,
    endpoints: {
      'GET /api/dev/users': 'List all users',
      'POST /api/dev/generate-mock-users': 'Generate mock users for testing',
      'DELETE /api/dev/users': 'Clear all users',
      'GET /api/dev/info': 'Get system information',
      'GET /api/dev/test-auth': 'Test authentication endpoints'
    }
  });
});

// Test authentication endpoints
router.get('/test-auth', (req, res) => {
  res.json({
    message: 'Authentication test endpoints',
    examples: {
      register: {
        method: 'POST',
        url: '/api/auth/register',
        body: {
          email: 'test@example.com',
          password: 'password123',
          name: 'Test User'
        }
      },
      login: {
        method: 'POST',
        url: '/api/auth/login',
        body: {
          email: 'test@example.com',
          password: 'password123'
        }
      },
      profile: {
        method: 'GET',
        url: '/api/auth/profile',
        headers: {
          'Authorization': 'Bearer YOUR_JWT_TOKEN'
        }
      },
      verify: {
        method: 'POST',
        url: '/api/auth/verify',
        headers: {
          'Authorization': 'Bearer YOUR_JWT_TOKEN'
        }
      }
    }
  });
});

export { router as devRoutes };