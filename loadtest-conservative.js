import http from 'k6/http';
import { sleep, check } from 'k6';
import { SharedArray } from 'k6/data';
import { exportedUsers } from './users-data.js';

// Conservative test configuration to avoid rate limiting
export let options = {
  stages: [
    { duration: '1m', target: 10 },   // Ramp up to 10 users
    { duration: '2m', target: 20 },   // Ramp up to 20 users  
    { duration: '2m', target: 30 },   // Ramp up to 30 users
    { duration: '2m', target: 20 },   // Scale back down
    { duration: '1m', target: 0 },    // Ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000'],
    http_req_failed: ['rate<0.02'],    // Very low error rate
    'rate_limit_errors': ['rate<0.01'], // Track rate limit errors specifically
  },
};

// Extensive IP pool - 60+ unique IPs
const ipAddresses = [
  // Private network ranges
  '10.0.1.10', '10.0.1.11', '10.0.1.12', '10.0.1.13', '10.0.1.14', '10.0.1.15',
  '10.0.2.10', '10.0.2.11', '10.0.2.12', '10.0.2.13', '10.0.2.14', '10.0.2.15',
  '10.0.3.10', '10.0.3.11', '10.0.3.12', '10.0.3.13', '10.0.3.14', '10.0.3.15',
  '192.168.10.10', '192.168.10.11', '192.168.10.12', '192.168.10.13', '192.168.10.14',
  '192.168.11.10', '192.168.11.11', '192.168.11.12', '192.168.11.13', '192.168.11.14',
  '192.168.12.10', '192.168.12.11', '192.168.12.12', '192.168.12.13', '192.168.12.14',
  '172.20.1.10', '172.20.1.11', '172.20.1.12', '172.20.1.13', '172.20.1.14',
  '172.20.2.10', '172.20.2.11', '172.20.2.12', '172.20.2.13', '172.20.2.14',
  '172.20.3.10', '172.20.3.11', '172.20.3.12', '172.20.3.13', '172.20.3.14',
  // Simulated ISP ranges
  '203.112.200.10', '203.112.200.11', '203.112.200.12', '203.112.200.13',
  '203.112.201.10', '203.112.201.11', '203.112.201.12', '203.112.201.13',
  '45.127.100.10', '45.127.100.11', '45.127.100.12', '45.127.100.13',
  '45.127.101.10', '45.127.101.11', '45.127.101.12', '45.127.101.13',
  '156.213.50.10', '156.213.50.11', '156.213.50.12', '156.213.50.13',
  '156.213.51.10', '156.213.51.11', '156.213.51.12', '156.213.51.13',
];

const userData = new SharedArray('users', function () {
  console.log(`Loaded ${exportedUsers.length} real users from database`);
  return exportedUsers;
});

// Rate limiting tracker (per IP)
let ipRequestCounts = {};

export default function () {
  const baseUrl = 'http://127.0.0.1:8000/api/v1';
  const userIndex = (__VU - 1) % userData.length;
  const user = userData[userIndex];
  
  // Smart IP selection with rate limiting awareness
  const ipIndex = ((__VU * 17 + __ITER * 23) % ipAddresses.length); // Better distribution
  const clientIP = ipAddresses[ipIndex];
  
  // Track requests per IP (simplified for demo)
  if (!ipRequestCounts[clientIP]) {
    ipRequestCounts[clientIP] = 0;
  }
  ipRequestCounts[clientIP]++;
  
  // If this IP has made too many requests recently, wait longer
  if (ipRequestCounts[clientIP] > 15) { // Conservative limit
    sleep(Math.random() * 10 + 5); // Wait 5-15 seconds
    ipRequestCounts[clientIP] = 0; // Reset counter
  }
  
  const headers = {
    'Content-Type': 'application/json',
    'X-Forwarded-For': clientIP,
    'X-Real-IP': clientIP,
    'X-Client-IP': clientIP,
    'CF-Connecting-IP': clientIP,
    'User-Agent': `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36`,
    'Accept': 'application/json',
    'Accept-Language': 'en-US,en;q=0.9',
    'Accept-Encoding': 'gzip, deflate',
    'Cache-Control': 'no-cache',
    'Pragma': 'no-cache',
  };

  try {
    // Staggered start to avoid burst
    const startDelay = Math.random() * 5; // 0-5 seconds
    sleep(startDelay);

    const sessionToken = user.session_token;
    const quizId = user.quiz_id;

    console.log(`[IP: ${clientIP}] User: ${user.name} (Quiz ${quizId}) - Request #${ipRequestCounts[clientIP]}`);

    const submissionPayload = {
      quiz_id: quizId,
      answers: {
        15: [57, 58],
        16: [61, 62]
      },
      duration: Math.floor(Math.random() * 300) + 60,
      submit_reason: "Load test - rate limited",
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

    const submissionResponse = http.post(
      `${baseUrl}/quizzes/submit_exam`,
      JSON.stringify(submissionPayload),
      { headers: submissionHeaders }
    );

    const checks = check(submissionResponse, {
      'submission successful': (r) => r.status === 200 || r.status === 202,
      'submission has response': (r) => r.body && r.body.length > 0,
      'not rate limited': (r) => r.status !== 429,
    });

    // Custom metric for rate limit tracking
    if (submissionResponse.status === 429) {
      check(null, { 'rate_limit_errors': () => false }); // This will increment the counter
      console.log(`🚫 RATE LIMITED: IP ${clientIP} hit rate limit (${ipRequestCounts[clientIP]} requests)`);
      sleep(Math.random() * 15 + 10); // Long backoff on rate limit
      ipRequestCounts[clientIP] = 0; // Reset this IP's counter
    } else if (checks['submission successful']) {
      console.log(`✅ SUCCESS: ${user.name} from IP ${clientIP} (${submissionResponse.status})`);
      
      // Very limited status checking to reduce load
      if (Math.random() < 0.05) { // Only 5% check status
        try {
          sleep(2 + Math.random() * 3); // Wait 2-5 seconds
          
          const responseData = JSON.parse(submissionResponse.body);
          if (responseData.data && responseData.data.submission_id) {
            const statusResponse = http.get(
              `${baseUrl}/quizzes/submission_status/${responseData.data.submission_id}`,
              { headers: submissionHeaders }
            );
            
            if (statusResponse.status === 429) {
              console.log(`🚫 Status check rate limited for IP ${clientIP}`);
            } else {
              console.log(`📊 Status: ${statusResponse.status} for ${user.name}`);
            }
          }
        } catch (e) {
          console.log(`⚠️ Status check error: ${e.message}`);
        }
      }
    } else {
      console.log(`❌ FAILED: ${user.name} from IP ${clientIP}: ${submissionResponse.status}`);
    }

  } catch (error) {
    console.log(`💥 ERROR: ${user.name} from IP ${clientIP}: ${error.message}`);
  }

  // Variable delay between requests to avoid bursts
  const endDelay = Math.random() * 8 + 3; // 3-11 seconds between requests
  sleep(endDelay);
}
