// k6 run --vus 3 --duration 30s .\loadtest-configurable.js

import http from 'k6/http';
import { sleep, check } from 'k6';
import { SharedArray } from 'k6/data';

// 🎯 CONFIGURABLE LOAD TEST SETTINGS
const TARGET_REQUESTS = 10000;  // 👈 CHANGE THIS NUMBER TO YOUR DESIRED REQUEST COUNT
const RATE_LIMIT_PER_IP = 60;  // Max requests per minute per IP (server limitation)
const SAFETY_MARGIN = 0.9;     // Use 90% of rate limit for safety

// Calculate optimal test parameters
const effectiveRatePerIP = Math.floor(RATE_LIMIT_PER_IP * SAFETY_MARGIN); // 54 req/min per IP
const minIPs = Math.ceil(TARGET_REQUESTS / effectiveRatePerIP); // Minimum IPs needed
const testDurationMinutes = Math.ceil(TARGET_REQUESTS / (effectiveRatePerIP * minIPs)); // Duration in minutes
const totalIPs = Math.max(minIPs, 20); // Use at least 20 IPs for better distribution
const requestsPerIP = Math.ceil(TARGET_REQUESTS / totalIPs);
const vusNeeded = Math.min(TARGET_REQUESTS, totalIPs * 3); // 3 VUs per IP max

// Dynamic test configuration
export let options = {
    stages: [
    { duration: '2m', target: 1000 },   // Ramp up to 1000 users over 2 minutes
    { duration: '5m', target: 5000 },   // Ramp up to 5000 users over 5 minutes
    { duration: '10m', target: 10000 }, // Ramp up to 10,000 users over 10 minutes
    { duration: '5m', target: 15000 },  // Ramp up to 15,000 users over 5 minutes
    { duration: '3m', target: 20000 },  // Spike to 20,000 users
    { duration: '10m', target: 20000 }, // Stay at 20,000 users for 10 minutes
    { duration: '5m', target: 0 },     // Ramp down to 0 users
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000'],
    http_req_failed: ['rate<0.05'],
    checks: ['rate>0.95'],
  },
};

// Generate enough IP addresses for the load
const ipAddresses = new SharedArray('ips', function () {
  const ips = [];
  
  // Generate different IP ranges to simulate realistic traffic
  const ranges = [
    '192.168.1',   // Local network
    '10.0.0',      // Private network 
    '172.16.0',    // Private network
    '203.112.218', // Public IP range
    '123.45.67',   // Public IP range
    '198.51.100',  // Test IP range
    '102.54.94',   // Another range
    '185.199.108', // CDN range
  ];
  
  let ipCount = 0;
  for (let range of ranges) {
    for (let i = 1; i <= 254 && ipCount < totalIPs; i++) {
      ips.push(`${range}.${i}`);
      ipCount++;
    }
  }
  
  console.log(`📊 Load Test Configuration:`);
  console.log(`   Target Requests: ${TARGET_REQUESTS}`);
  console.log(`   Generated IPs: ${ips.length}`);
  console.log(`   Requests per IP: ~${requestsPerIP}`);
  console.log(`   Test Duration: ~${testDurationMinutes + 2} minutes`);
  console.log(`   Virtual Users: ${vusNeeded}`);
  
  return ips;
});

// Real user data from database (cycle through for more users)
const userData = new SharedArray('users', function () {
  const realUsers = [
    {
      "id": 29,
      "quiz_id": 2,
      "session_token": "H1ymCs6xp8b1Ssrj8KAKfXad7pDCcawT3cMqquXX9QNOANk9DGD2wbj1ZdW0LMfW",
      "name": "User82",
      "email": "user82@test.com"
    },
    {
      "id": 30,
      "quiz_id": 2,
      "session_token": "D2CpZZ0PNyXsyfaEvRw9PZdd9sQn4vJGpbXmEuSB3Qxwh8CXFHpAVn4PVlVTeFye",
      "name": "User12",
      "email": "user12@test.com"
    },
    {
      "id": 31,
      "quiz_id": 2,
      "session_token": "CBnWbyao70nQ1HF5IXxkfelyDPrJ1N0IXTz9YYRDeKR1yVtjxV8HbiqEazGB3VqA",
      "name": "User67",
      "email": "user67@test.com"
    },
    {
      "id": 32,
      "quiz_id": 2,
      "session_token": "XenDIbG1PKlBYGhoLE4pAOvDWbBUZbQV6nbdIaVHXiRvzklLUtofMh6GL0EJUTHY",
      "name": "User40",
      "email": "user40@test.com"
    },
    {
      "id": 33,
      "quiz_id": 2,
      "session_token": "Vz17iGoGHA1FDI4IxdIewmBj9mjI08TTvEEF0hCxFnw1wiYqx8Nu1h6K1WXiDceo",
      "name": "User15",
      "email": "user15@test.com"
    }
  ];
  
  // Extend the user pool by cycling through the base users
  const extendedUsers = [];
  for (let i = 0; i < TARGET_REQUESTS; i++) {
    const baseUser = realUsers[i % realUsers.length];
    extendedUsers.push({
      ...baseUser,
      id: baseUser.id + i,
      name: `${baseUser.name}_${i}`,
      email: `${baseUser.email.split('@')[0]}_${i}@${baseUser.email.split('@')[1]}`
    });
  }
  
  console.log(`👥 Generated ${extendedUsers.length} unique users for testing`);
  return extendedUsers;
});

// Request counter per IP to respect rate limits
let requestCounters = {};

export default function () {
  const baseUrl = 'http://127.0.0.1:8000/api/v1';
  
  // Select user and IP with proper distribution
  const userIndex = (__VU - 1) % userData.length;
  const user = userData[userIndex];
  const ipIndex = (__VU - 1) % ipAddresses.length;
  const clientIP = ipAddresses[ipIndex];
  
  // Initialize counter for this IP if not exists
  if (!requestCounters[clientIP]) {
    requestCounters[clientIP] = 0;
  }
  
  // Check if we've hit the rate limit for this IP this minute
  if (requestCounters[clientIP] >= effectiveRatePerIP) {
    // Wait for the next minute window
    sleep(60);
    requestCounters[clientIP] = 0;
  }
  
  const headers = {
    'Content-Type': 'application/json',
    'X-Forwarded-For': clientIP,
    'X-Real-IP': clientIP,
    'X-Client-IP': clientIP,
    'User-Agent': `Mozilla/5.0 (LoadTest-VU${__VU}) AppleWebKit/537.36`,
  };

  try {
    // Use existing session token from database user
    const sessionToken = user.session_token;
    const quizId = user.quiz_id;

    // Generate realistic quiz answers
    const answers = {
      15: [57, 58], // Multiple choice question
      16: [61, 62]  // Multiple choice question
    };

    const submissionPayload = {
      quiz_id: quizId,
      answers: answers,
      duration: Math.floor(Math.random() * 300) + 60, // 1-5 minutes
      submit_reason: "Load test submission",
      security_data: {
        tabSwitchCount: Math.floor(Math.random() * 2),
        fullscreenExitCount: Math.floor(Math.random() * 1),
        screenshotAttempts: 0,
        keyboardViolations: Math.floor(Math.random() * 3),
        copyPasteAttempts: 0,
        networkRequestCount: Math.floor(Math.random() * 5),
        windowBlurCount: Math.floor(Math.random() * 3),
        violations: []
      }
    };

    const submissionHeaders = {
      ...headers,
      'Authorization': `Bearer ${sessionToken}`,
    };

    // Increment request counter for this IP
    requestCounters[clientIP]++;

    const submissionResponse = http.post(
      `${baseUrl}/quizzes/submit_exam`,
      JSON.stringify(submissionPayload),
      { headers: submissionHeaders, timeout: '30s' }
    );

    const submissionSuccess = check(submissionResponse, {
      'submission successful': (r) => r.status === 200 || r.status === 202,
      'submission has response': (r) => r.body && r.body.length > 0,
      'not rate limited': (r) => r.status !== 429,
    });

    if (!submissionSuccess) {
      console.log(`❌ Submission failed for ${user.name} from IP ${clientIP}: ${submissionResponse.status}`);
      
      // If rate limited, wait longer
      if (submissionResponse.status === 429) {
        sleep(5);
      }
    }

  } catch (error) {
    console.log(`❌ Error for user ${user.name} from IP ${clientIP}: ${error.message}`);
  }

  // Dynamic pacing based on target requests and time
  const pacingSleep = (60 / effectiveRatePerIP) + (Math.random() * 0.5); // Base pacing + jitter
  sleep(pacingSleep);
}

// Print configuration on load
console.log(`🚀 Starting load test for ${TARGET_REQUESTS} requests`);
console.log(`📊 Estimated completion time: ${testDurationMinutes + 2} minutes`);
console.log(`💡 To change target requests, modify TARGET_REQUESTS at the top of this file`);
