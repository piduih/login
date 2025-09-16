const LoginModule = require('./loginModule');

// Create instance of login module
const auth = new LoginModule();

console.log('=== Login Module Demo ===\n');

try {
    // Demonstrate user registration
    console.log('1. Registering new user...');
    auth.registerUser('testuser', 'password123');
    console.log('✓ User "testuser" registered successfully\n');

    // Demonstrate login
    console.log('2. Logging in with testuser...');
    const loginResult = auth.login('testuser', 'password123');
    console.log('✓ Login successful!');
    console.log(`   Session token: ${loginResult.sessionToken.substring(0, 16)}...`);
    console.log(`   Username: ${loginResult.username}\n`);

    // Demonstrate session verification
    console.log('3. Verifying session...');
    const sessionCheck = auth.verifySession(loginResult.sessionToken);
    console.log('✓ Session is valid');
    console.log(`   User: ${sessionCheck.username}`);
    console.log(`   Login time: ${sessionCheck.loginTime}\n`);

    // Demonstrate admin functionality
    console.log('4. Admin login and user listing...');
    const adminLogin = auth.login('admin', 'admin123');
    const users = auth.getUsers(adminLogin.sessionToken);
    console.log('✓ Users in system:');
    users.forEach(user => {
        console.log(`   - ${user.username} (created: ${user.createdAt})`);
    });
    console.log('');

    // Demonstrate password change
    console.log('5. Changing password...');
    auth.changePassword(loginResult.sessionToken, 'password123', 'newpassword456');
    console.log('✓ Password changed successfully\n');

    // Demonstrate logout
    console.log('6. Logging out...');
    auth.logout(loginResult.sessionToken);
    console.log('✓ Logged out successfully\n');

    // Verify session is now invalid
    console.log('7. Verifying session after logout...');
    const sessionCheckAfterLogout = auth.verifySession(loginResult.sessionToken);
    console.log(`✓ Session validity: ${sessionCheckAfterLogout.valid}`);
    console.log(`   Message: ${sessionCheckAfterLogout.message}\n`);

    console.log('=== Demo completed successfully! ===');

} catch (error) {
    console.error('❌ Error:', error.message);
}