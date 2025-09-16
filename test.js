const LoginModule = require('./loginModule');

/**
 * Simple test runner for the login module
 */
class TestRunner {
    constructor() {
        this.passedTests = 0;
        this.failedTests = 0;
        this.totalTests = 0;
    }

    test(description, testFunction) {
        this.totalTests++;
        console.log(`\n🧪 Testing: ${description}`);
        
        try {
            testFunction();
            console.log('✅ PASSED');
            this.passedTests++;
        } catch (error) {
            console.log(`❌ FAILED: ${error.message}`);
            this.failedTests++;
        }
    }

    assert(condition, message) {
        if (!condition) {
            throw new Error(message || 'Assertion failed');
        }
    }

    assertEqual(actual, expected, message) {
        if (actual !== expected) {
            throw new Error(message || `Expected ${expected}, but got ${actual}`);
        }
    }

    assertThrows(fn, expectedMessage) {
        let threw = false;
        try {
            fn();
        } catch (error) {
            threw = true;
            if (expectedMessage && !error.message.includes(expectedMessage)) {
                throw new Error(`Expected error message to contain "${expectedMessage}", but got "${error.message}"`);
            }
        }
        if (!threw) {
            throw new Error('Expected function to throw an error, but it did not');
        }
    }

    summary() {
        console.log('\n' + '='.repeat(50));
        console.log('📊 TEST SUMMARY');
        console.log('='.repeat(50));
        console.log(`Total tests: ${this.totalTests}`);
        console.log(`✅ Passed: ${this.passedTests}`);
        console.log(`❌ Failed: ${this.failedTests}`);
        console.log(`Success rate: ${((this.passedTests / this.totalTests) * 100).toFixed(1)}%`);
        
        if (this.failedTests === 0) {
            console.log('\n🎉 All tests passed!');
        } else {
            console.log('\n⚠️  Some tests failed!');
        }
    }
}

// Run tests
const runner = new TestRunner();

console.log('🚀 Starting Login Module Tests...');

// Test user registration
runner.test('User registration with valid data', () => {
    const auth = new LoginModule();
    const result = auth.registerUser('testuser', 'password123');
    runner.assert(result === true, 'Registration should return true');
});

runner.test('User registration with duplicate username', () => {
    const auth = new LoginModule();
    auth.registerUser('testuser', 'password123');
    runner.assertThrows(() => {
        auth.registerUser('testuser', 'differentpassword');
    }, 'User already exists');
});

runner.test('User registration with empty username', () => {
    const auth = new LoginModule();
    runner.assertThrows(() => {
        auth.registerUser('', 'password123');
    }, 'Username and password are required');
});

runner.test('User registration with empty password', () => {
    const auth = new LoginModule();
    runner.assertThrows(() => {
        auth.registerUser('testuser', '');
    }, 'Username and password are required');
});

// Test user login
runner.test('User login with valid credentials', () => {
    const auth = new LoginModule();
    auth.registerUser('testuser', 'password123');
    const result = auth.login('testuser', 'password123');
    
    runner.assert(result.success === true, 'Login should be successful');
    runner.assert(result.username === 'testuser', 'Username should match');
    runner.assert(typeof result.sessionToken === 'string', 'Session token should be a string');
    runner.assert(result.sessionToken.length > 0, 'Session token should not be empty');
});

runner.test('User login with invalid username', () => {
    const auth = new LoginModule();
    runner.assertThrows(() => {
        auth.login('nonexistent', 'password123');
    }, 'Invalid credentials');
});

runner.test('User login with invalid password', () => {
    const auth = new LoginModule();
    auth.registerUser('testuser', 'password123');
    runner.assertThrows(() => {
        auth.login('testuser', 'wrongpassword');
    }, 'Invalid credentials');
});

// Test session verification
runner.test('Session verification with valid token', () => {
    const auth = new LoginModule();
    auth.registerUser('testuser', 'password123');
    const loginResult = auth.login('testuser', 'password123');
    const sessionCheck = auth.verifySession(loginResult.sessionToken);
    
    runner.assert(sessionCheck.valid === true, 'Session should be valid');
    runner.assert(sessionCheck.username === 'testuser', 'Username should match');
});

runner.test('Session verification with invalid token', () => {
    const auth = new LoginModule();
    const sessionCheck = auth.verifySession('invalid_token');
    
    runner.assert(sessionCheck.valid === false, 'Session should be invalid');
});

// Test logout
runner.test('User logout with valid session', () => {
    const auth = new LoginModule();
    auth.registerUser('testuser', 'password123');
    const loginResult = auth.login('testuser', 'password123');
    const logoutResult = auth.logout(loginResult.sessionToken);
    
    runner.assert(logoutResult === true, 'Logout should return true');
    
    // Verify session is now invalid
    const sessionCheck = auth.verifySession(loginResult.sessionToken);
    runner.assert(sessionCheck.valid === false, 'Session should be invalid after logout');
});

// Test password change
runner.test('Password change with valid credentials', () => {
    const auth = new LoginModule();
    auth.registerUser('testuser', 'password123');
    const loginResult = auth.login('testuser', 'password123');
    
    const changeResult = auth.changePassword(loginResult.sessionToken, 'password123', 'newpassword456');
    runner.assert(changeResult === true, 'Password change should return true');
    
    // Verify new password works
    auth.logout(loginResult.sessionToken);
    const newLoginResult = auth.login('testuser', 'newpassword456');
    runner.assert(newLoginResult.success === true, 'Login with new password should work');
});

// Test admin functionality
runner.test('Admin can view user list', () => {
    const auth = new LoginModule();
    const adminLogin = auth.login('admin', 'admin123');
    const users = auth.getUsers(adminLogin.sessionToken);
    
    runner.assert(Array.isArray(users), 'Users should be an array');
    runner.assert(users.length >= 1, 'Should have at least admin user');
});

runner.test('Non-admin cannot view user list', () => {
    const auth = new LoginModule();
    auth.registerUser('testuser', 'password123');
    const userLogin = auth.login('testuser', 'password123');
    
    runner.assertThrows(() => {
        auth.getUsers(userLogin.sessionToken);
    }, 'Unauthorized access');
});

// Test default admin user
runner.test('Default admin user exists and can login', () => {
    const auth = new LoginModule();
    const adminLogin = auth.login('admin', 'admin123');
    
    runner.assert(adminLogin.success === true, 'Admin login should be successful');
    runner.assert(adminLogin.username === 'admin', 'Username should be admin');
});

// Show test results
runner.summary();