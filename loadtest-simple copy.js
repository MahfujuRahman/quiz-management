import http from 'k6/http';
import { sleep, check } from 'k6';
import { SharedArray } from 'k6/data';

// Simple test configuration for quick testing
export let options = {
  vus: 100,           // 100 virtual users
  duration: '2m',     // Run for 2 minutes
  thresholds: {
    http_req_duration: ['p(95)<3000'], // 95% of requests must complete within 3s
    http_req_failed: ['rate<0.1'],     // Error rate must be less than 10%
  },
};

// Simple IP simulation
const ipAddresses = ['192.168.1.10', '192.168.1.11', '192.168.1.12', '10.0.0.10', '10.0.0.11'];

// Simple user data
const userData = new SharedArray('users', function () {
  const users = [];
  for (let i = 1; i <= 200; i++) {
    users.push({
      name: `User${i}`,
      email: `user${i}@test.com`,
      phone: `01${String(Math.floor(Math.random() * 900000000) + 100000000)}`,
      organization: `Org${Math.floor(Math.random() * 10) + 1}`,
    });
  }
  return users;
});

export default function () {
  const baseUrl = 'http://127.0.0.1:8000/api/v1';
  const userIndex = (__VU - 1) % userData.length;
  const user = userData[userIndex];
  const clientIP = ipAddresses[__VU % ipAddresses.length];
  
  const headers = {
    'Content-Type': 'application/json',
    'X-Forwarded-For': clientIP,
    'X-Real-IP': clientIP,
    'User-Agent': `Mozilla/5.0 (LoadTest-${__VU})`,
  };

  try {
    // Step 1: Register for quiz
    const registrationPayload = {
      quiz_id: 2, // Use quiz ID 2
      name: user.name,
      email: user.email,
      phone: user.phone,
      organization: user.organization
    };

    const registrationResponse = http.post(
      `${baseUrl}/public-quizzes/register`,
      JSON.stringify(registrationPayload),
      { headers }
    );

    if (!check(registrationResponse, { 'registration successful': (r) => r.status === 200 || r.status === 201 })) {
      return;
    }

    const regData = JSON.parse(registrationResponse.body);
    const sessionToken = regData.data.session_token;

    sleep(1); // Brief pause

    // Step 2: Submit quiz
    const answers = {
      15: [57, 58], // Multiple choice question
      16: [61, 62]  // Multiple choice question
    };

    const submissionPayload = {
      quiz_id: 2,
      answers: answers,
      duration: Math.floor(Math.random() * 300) + 60, // 1-5 minutes
      submit_reason: "Normal completion",
      security_data: {
        tabSwitchCount: Math.floor(Math.random() * 3),
        fullscreenExitCount: Math.floor(Math.random() * 2),
        screenshotAttempts: 0,
        keyboardViolations: Math.floor(Math.random() * 5),
        copyPasteAttempts: 0,
        networkRequestCount: Math.floor(Math.random() * 10),
        windowBlurCount: Math.floor(Math.random() * 5),
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

    check(submissionResponse, {
      'submission successful': (r) => r.status === 200 || r.status === 202,
    });

  } catch (error) {
    console.log(`Error for user ${user.email}: ${error.message}`);
  }

  sleep(0.5);
}
