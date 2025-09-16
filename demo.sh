#!/bin/bash
# Demo script showing the login module in action

echo "🚀 Starting Login Module Demo"
echo "================================="

# Start the server in background
echo "Starting server..."
npm start &
SERVER_PID=$!

# Wait for server to start
sleep 3

echo ""
echo "📊 Health Check:"
curl -s http://localhost:3000/health | jq .

echo ""
echo "🛠️ Generating mock users for testing:"
curl -s -X POST http://localhost:3000/api/dev/generate-mock-users \
  -H "Content-Type: application/json" \
  -d '{"count": 3}' | jq .

echo ""
echo "👤 Logging in with mock user (alice@example.com):"
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "alice@example.com",
    "password": "password123"
  }')

echo $LOGIN_RESPONSE | jq .

# Extract token for protected endpoint
TOKEN=$(echo $LOGIN_RESPONSE | jq -r .token)

echo ""
echo "🔐 Accessing protected profile endpoint:"
curl -s -X GET http://localhost:3000/api/auth/profile \
  -H "Authorization: Bearer $TOKEN" | jq .

echo ""
echo "📝 Registering a new user:"
curl -s -X POST http://localhost:3000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "demo@example.com",
    "password": "demopass123",
    "name": "Demo User"
  }' | jq .

echo ""
echo "👥 Current users in system:"
curl -s http://localhost:3000/api/dev/users | jq .

# Clean up
echo ""
echo "🧹 Stopping server..."
kill $SERVER_PID

echo ""
echo "✅ Demo completed! The login module is ready for development."
echo "📚 See README.md for full documentation and API reference."