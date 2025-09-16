import bcrypt from 'bcryptjs';
import { users } from './storage.js';

const mockEmails = [
  'alice@example.com',
  'bob@example.com',
  'charlie@example.com',
  'diana@example.com',
  'edward@example.com',
  'fiona@example.com',
  'george@example.com',
  'helen@example.com',
  'ivan@example.com',
  'julia@example.com'
];

const mockNames = [
  'Alice Johnson',
  'Bob Smith',
  'Charlie Brown',
  'Diana Prince',
  'Edward Norton',
  'Fiona Apple',
  'George Washington',
  'Helen Keller',
  'Ivan Petrov',
  'Julia Roberts'
];

export const generateMockUsers = async (count = 5) => {
  const mockUsers = [];
  const hashedPassword = await bcrypt.hash('password123', 10);
  
  for (let i = 0; i < Math.min(count, mockEmails.length); i++) {
    const user = {
      id: Date.now().toString() + i,
      email: mockEmails[i],
      name: mockNames[i],
      password: hashedPassword,
      createdAt: new Date().toISOString()
    };
    
    // Only add if user doesn't already exist
    if (!users.find(u => u.email === user.email)) {
      users.push(user);
      mockUsers.push(user);
    }
  }
  
  return mockUsers;
};

export const clearAllUsers = () => {
  users.length = 0;
};