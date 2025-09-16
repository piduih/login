import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import { authRoutes } from './routes/auth.js';
import { devRoutes } from './routes/dev.js';
import { errorHandler } from './middleware/errorHandler.js';

// Load environment variables
dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Routes
app.use('/api/auth', authRoutes);

// Development utilities (only in development mode)
if (process.env.NODE_ENV === 'development') {
  app.use('/api/dev', devRoutes);
}

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({
    status: 'OK',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    environment: process.env.NODE_ENV || 'development'
  });
});

// Root endpoint with API information
app.get('/', (req, res) => {
  res.json({
    name: 'Login Module API',
    version: '1.0.0',
    description: 'A modular login system for faster development',
    endpoints: {
      auth: '/api/auth',
      health: '/health',
      ...(process.env.NODE_ENV === 'development' && { dev: '/api/dev' })
    }
  });
});

// Error handling middleware
app.use(errorHandler);

// Start server
app.listen(PORT, () => {
  console.log(`🚀 Login Module API running on port ${PORT}`);
  console.log(`📊 Health check: http://localhost:${PORT}/health`);
  console.log(`🔐 Auth endpoints: http://localhost:${PORT}/api/auth`);
  if (process.env.NODE_ENV === 'development') {
    console.log(`🛠️  Dev utilities: http://localhost:${PORT}/api/dev`);
  }
});

export default app;