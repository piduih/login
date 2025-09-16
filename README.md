# Login Module

A modular, fast-development login system with JWT authentication, built for rapid prototyping and development.

## Features

- 🚀 **Fast Setup**: Get authentication running in minutes
- 🔐 **JWT Authentication**: Secure token-based authentication
- 🛠️ **Development Tools**: Built-in utilities for testing and development
- 📦 **Modular Design**: Easy to integrate into existing projects
- 🔄 **Hot Reload**: Development server with automatic restart
- 🧪 **Mock Data**: Generate test users instantly
- 📊 **Health Monitoring**: Built-in health checks and system info

## Quick Start

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Set up environment:**
   ```bash
   cp .env.example .env
   ```

3. **Start development server:**
   ```bash
   npm run dev
   ```

4. **Test the API:**
   ```bash
   curl http://localhost:3000/health
   ```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login user
- `GET /api/auth/profile` - Get user profile (requires auth)
- `PUT /api/auth/profile` - Update user profile (requires auth)
- `POST /api/auth/verify` - Verify JWT token

### Development Utilities (development mode only)
- `GET /api/dev/users` - List all users
- `POST /api/dev/generate-mock-users` - Generate test users
- `DELETE /api/dev/users` - Clear all users
- `GET /api/dev/info` - System information
- `GET /api/dev/test-auth` - Authentication examples

### System
- `GET /health` - Health check
- `GET /` - API information

## Usage Examples

### Register a new user
```bash
curl -X POST http://localhost:3000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "name": "John Doe"
  }'
```

### Login
```bash
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

### Access protected endpoint
```bash
curl -X GET http://localhost:3000/api/auth/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Generate mock users for testing
```bash
curl -X POST http://localhost:3000/api/dev/generate-mock-users \
  -H "Content-Type: application/json" \
  -d '{"count": 5}'
```

## Configuration

Environment variables can be set in `.env` file:

- `JWT_SECRET` - Secret key for JWT tokens (change in production!)
- `JWT_EXPIRES_IN` - Token expiration time (default: 1h)
- `PORT` - Server port (default: 3000)
- `NODE_ENV` - Environment mode (development/production)

## Development Features

### Hot Reload
```bash
npm run dev
```

### Mock Data Generation
Access `/api/dev/generate-mock-users` to create test users with password `password123`.

### System Monitoring
Visit `/api/dev/info` for system information and `/health` for health checks.

## Scripts

- `npm start` - Start production server
- `npm run dev` - Start development server with hot reload
- `npm test` - Run tests
- `npm run lint` - Lint code
- `npm run format` - Format code with Prettier

## Security Notes

⚠️ **Important for Production:**
- Change `JWT_SECRET` to a strong, random value
- Use HTTPS in production
- Implement rate limiting
- Add input sanitization
- Use a proper database instead of in-memory storage
- Add password strength requirements
- Implement account lockout mechanisms

## Integration

This module can be easily integrated into existing Node.js applications:

```javascript
import express from 'express';
import { authRoutes } from './src/routes/auth.js';

const app = express();
app.use('/auth', authRoutes);
```

## License

MIT
