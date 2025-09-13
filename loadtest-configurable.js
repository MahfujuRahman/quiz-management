// k6 run --vus 3 --duration 30s .\loadtest-configurable.js
//  k6 run --vus 3 --iterations 500 .\loadtest-configurable.js

import http from 'k6/http';
import { sleep, check } from 'k6';
import { SharedArray } from 'k6/data';
import { exportedUsers } from './users-data.js'; // Import real database users
import exec from 'k6/execution';

// 🎯 CONFIGURABLE LOAD TEST SETTINGS
const TARGET_REQUESTS = 10000;  // 👈 CHANGE THIS NUMBER TO YOUR DESIRED REQUEST COUNT

// Calculate other settings based on target requests
// Ensure we have MORE IPs than users to guarantee uniqueness
const totalIPs = exportedUsers.length + 500; // More IPs than users to ensure uniqueness
const effectiveRatePerIP = 1; // Each IP will be used by only one user
const testDurationMinutes = Math.ceil(exportedUsers.length / 500); // Conservative duration

export const options = {
  // Use iterations instead of duration to ensure exact number of requests
  scenarios: {
    quiz_submissions: {
      executor: 'shared-iterations',
      vus: Math.min(exportedUsers.length, 50), // Limit concurrent users to avoid overwhelming
      iterations: exportedUsers.length, // Exactly one iteration per user
      maxDuration: `${testDurationMinutes + 5}m`, // Safety timeout
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<3000'],
    http_req_failed: ['rate<0.05'],
    checks: ['rate>0.95'],
  },
};

// Generate enough unique IP addresses - more than users to ensure no conflicts
const ipAddresses = new SharedArray('ips', function () {
  const ips = [];
  
  // Generate different IP ranges to simulate realistic traffic
  const ranges = [
    '192.168', '10.0', '172.16', '203.112', '123.45', '198.51', '102.54', '185.199',
    '8.8', '1.1', '208.67', '64.6', '77.88', '156.154', '176.103', '91.239',
    '74.82', '84.200', '168.95', '193.183', '200.134', '212.27', '195.46', '80.80'
  ];
  
  let ipCount = 0;
  
  // Generate IPs from multiple ranges to ensure uniqueness
  for (let rangeIndex = 0; rangeIndex < ranges.length && ipCount < totalIPs; rangeIndex++) {
    const baseRange = ranges[rangeIndex];
    
    for (let subnet = 0; subnet <= 255 && ipCount < totalIPs; subnet++) {
      for (let host = 1; host <= 254 && ipCount < totalIPs; host++) {
        ips.push(`${baseRange}.${subnet}.${host}`);
        ipCount++;
      }
    }
  }
  
  console.log(`📊 Load Test Configuration:`);
  console.log(`   Available Users: ${exportedUsers.length}`);
  console.log(`   Iterations: ${exportedUsers.length} (one per user)`);
  console.log(`   Generated IPs: ${ips.length}`);
  console.log(`   IP Guarantee: Each user gets unique IP (${ips.length} IPs for ${exportedUsers.length} users)`);
  console.log(`   Test Duration: ~${testDurationMinutes + 5} minutes max`);
  console.log(`   Mode: One submission per user with unique IP - NO RATE LIMITING`);
  
  return ips;
});

// Real user data from database (ensure we have enough unique users)
const userData = new SharedArray('users', function () {
  const realUsers = exportedUsers;
  
  console.log(`👥 Using ${realUsers.length} real database users for testing`);
  console.log(`   Each user will submit exactly once with unique IP`);
  
  return realUsers;
});

export default function () {
  // Use k6's built-in scenario iteration index for unique user selection
  const userIndex = exec.scenario.iterationInTest;
  
  // Make sure we don't go beyond available users
  if (userIndex >= userData.length) {
    console.log(`⚠️  Iteration ${userIndex} exceeds available users (${userData.length}), skipping...`);
    return;
  }
  
  const user = userData[userIndex];
  
  // Assign UNIQUE IP to each user (direct 1:1 mapping, NO modulo)
  const ip = ipAddresses[userIndex]; // Direct assignment ensures uniqueness
  
  console.log(`🔄 User ${userIndex + 1}/${userData.length}: ${user.name} using UNIQUE IP ${ip}`);
  
  const payload = {
    participant_id: user.participant_id,
    participation_id: user.participation_id,
    quiz_id: user.quiz_id,
    duration: Math.floor(Math.random() * 300) + 60, // 1-5 minutes
    submit_reason: "Load test completion",
    
    // Use actual answers or generate random ones
    answers: user.sample_answers || {
      "1": "B",
      "2": "A", 
      "3": "C",
      "4": "B",
      "5": "A"
    }
  };
  
  const params = {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Forwarded-For': ip,
      'X-Real-IP': ip,
      'Authorization': `Bearer ${user.session_token}`,
      'User-Agent': `LoadTester-${userIndex}-${ip}`,
    },
    timeout: '30s',
  };
  
  // Submit quiz answers
  const response = http.post('http://127.0.0.1:8000/api/v1/quizzes/submit_exam', JSON.stringify(payload), params);
  
  // Debug output for troubleshooting
  if (response.status !== 200) {
    console.log(`❌ Error ${response.status} for user ${user.name} (${user.email}): ${response.body}`);
    console.log(`   Payload: ${JSON.stringify(payload)}`);
    console.log(`   Token: ${user.session_token.substring(0, 20)}...`);
  } else {
    console.log(`✅ Success for user ${user.name} with UNIQUE IP ${ip} (never used before)`);
  }
  
  // Validate response
  const success = check(response, {
    'status is 200': (r) => r.status === 200,
    'response has success': (r) => {
      try {
        const body = JSON.parse(r.body);
        return body.success === true || body.message || body.data;
      } catch (e) {
        return false;
      }
    },
    'response time OK': (r) => r.timings.duration < 5000,
  });
  
  if (!success) {
    console.log(`🚨 Check failed for user ${user.name}: Status ${response.status}, Body: ${response.body.substring(0, 200)}`);
  }
  
  // No sleep needed since each user has unique IP (no rate limiting concerns)
}
