#!/usr/bin/env node

// Quick script to set target requests and run load test
const fs = require('fs');
const { execSync } = require('child_process');

const targetRequests = process.argv[2] || 1000;

console.log(`🎯 Setting up load test for ${targetRequests} requests...`);

try {
    // Read the configurable load test template
    let loadTestContent = fs.readFileSync('loadtest-configurable.js', 'utf8');
    
    // Replace the TARGET_REQUESTS value
    loadTestContent = loadTestContent.replace(
        /const TARGET_REQUESTS = \d+;/,
        `const TARGET_REQUESTS = ${targetRequests};`
    );
    
    // Write to a temporary file
    const tempFile = `temp-loadtest-${targetRequests}.js`;
    fs.writeFileSync(tempFile, loadTestContent);
    
    console.log(`✅ Created ${tempFile}`);
    
    // Calculate some stats
    const ratePerIP = 54;
    const minIPs = Math.ceil(targetRequests / ratePerIP);
    const duration = Math.ceil(targetRequests / (ratePerIP * Math.max(minIPs, 20))) + 2;
    
    console.log(`📊 Configuration:`);
    console.log(`   Target Requests: ${targetRequests}`);
    console.log(`   Estimated Duration: ~${duration} minutes`);
    console.log(`   Estimated IPs needed: ${minIPs}`);
    console.log('');
    
    console.log(`🚀 Running: k6 run ${tempFile}`);
    console.log(`⏰ Started at: ${new Date().toLocaleTimeString()}`);
    console.log('');
    
    // Run k6
    execSync(`k6 run ${tempFile}`, { stdio: 'inherit' });
    
    // Cleanup
    fs.unlinkSync(tempFile);
    console.log(`\n🧹 Cleaned up ${tempFile}`);
    
} catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
}
