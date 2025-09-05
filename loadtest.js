import http from 'k6/http';
import { sleep, check } from 'k6';
import { SharedArray } from 'k6/data';

// Realistic test configuration with multiple stages
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
    http_req_duration: ['p(95)<2000'], // 95% of requests must complete within 2s
    http_req_failed: ['rate<0.1'],     // Error rate must be less than 10%
  },
};

// Simulate multiple IP addresses - k6 will rotate through these
const ipAddresses = new SharedArray('ips', function () {
  return [
    '192.168.1.10', '192.168.1.11', '192.168.1.12', '192.168.1.13', '192.168.1.14',
    '10.0.0.10', '10.0.0.11', '10.0.0.12', '10.0.0.13', '10.0.0.14',
    '172.16.0.10', '172.16.0.11', '172.16.0.12', '172.16.0.13', '172.16.0.14',
    '203.112.218.10', '203.112.218.11', '203.112.218.12', '203.112.218.13',
    '123.456.789.10', '123.456.789.11', '123.456.789.12', '123.456.789.13',
  ];
});

// Load real users from database export
const userData = new SharedArray('users', function () {
  try {
    // Read the exported users file
    const fs = require('fs');
    const usersJson = fs.readFileSync('users-for-loadtest.json', 'utf8');
    const users = JSON.parse(usersJson);
    console.log(`Loaded ${users.length} real users from database`);
    return users;
  } catch (error) {
    console.error('Failed to load users from database. Run: php export-users.php first');
    // Fallback to dummy data if file doesn't exist
    const fallbackUsers = [];
    for (let i = 1; i <= 500; i++) {
      fallbackUsers.push({
        id: i,
        quiz_id: 2,
        session_token: `fallback-token-${i}`,
        name: `FallbackUser${i}`,
        email: `fallback${i}@test.com`
      });
    }
    return fallbackUsers;
  }
});

// Quiz configurations based on actual data
const quizConfigs = new SharedArray('quizzes', function () {
  return [
    {
      quiz_id: 1,
      questions: [
        { 
          id: 14, 
          options: [53, 54, 55, 56], 
          correct: [54], // Single correct answer
          is_multiple: false 
        },
        { 
          id: 15, 
          options: [57, 58, 59, 60], 
          correct: [57, 58], // Multiple correct answers
          is_multiple: true 
        },
        { 
          id: 16, 
          options: [61, 62, 63, 64], 
          correct: [61, 62, 63], // Multiple correct answers
          is_multiple: true 
        }
      ]
    },
    {
      quiz_id: 2,
      questions: [
        { 
          id: 15, 
          options: [57, 58, 59, 60], 
          correct: [57, 58], // Multiple correct answers
          is_multiple: true 
        },
        { 
          id: 16, 
          options: [61, 62, 63, 64], 
          correct: [61, 62, 63], // Multiple correct answers
          is_multiple: true 
        }
      ]
    }
  ];
});

// Answer generation strategies
function generateAnswers(quiz, strategy = 'mixed') {
  const answers = {};
  
  quiz.questions.forEach(question => {
    switch (strategy) {
      case 'correct':
        // Always give correct answers
        answers[question.id] = question.correct;
        break;
      case 'incorrect':
        // Always give incorrect answers
        const incorrect = question.options.filter(opt => !question.correct.includes(opt));
        answers[question.id] = question.is_multiple 
          ? incorrect.slice(0, Math.min(2, incorrect.length))
          : [incorrect[0] || question.options[0]];
        break;
      case 'partial':
        // Give partial correct answers for multiple choice
        if (question.is_multiple && question.correct.length > 1) {
          answers[question.id] = [question.correct[0]]; // Only first correct answer
        } else {
          answers[question.id] = question.correct;
        }
        break;
      case 'random':
        // Random answers
        const randomCount = question.is_multiple ? Math.floor(Math.random() * 3) + 1 : 1;
        const shuffled = [...question.options].sort(() => 0.5 - Math.random());
        answers[question.id] = shuffled.slice(0, randomCount);
        break;
      default: // 'mixed'
        // Mix of strategies based on random choice
        const strategies = ['correct', 'incorrect', 'partial', 'random'];
        const randomStrategy = strategies[Math.floor(Math.random() * strategies.length)];
        return generateAnswers(quiz, randomStrategy);
    }
  });
  
  return answers;
}

// Security violation scenarios
function generateSecurityData() {
  const scenarios = [
    {
      tabSwitchCount: Math.floor(Math.random() * 5),
      fullscreenExitCount: Math.floor(Math.random() * 3),
      screenshotAttempts: Math.floor(Math.random() * 3),
      keyboardViolations: Math.floor(Math.random() * 10),
      copyPasteAttempts: Math.floor(Math.random() * 5),
      networkRequestCount: Math.floor(Math.random() * 15),
      windowBlurCount: Math.floor(Math.random() * 8),
      violations: [
        { timestamp: new Date().toISOString(), violation: "Tab switch detected" },
        { timestamp: new Date().toISOString(), violation: "Window blur detected" }
      ]
    },
    {
      tabSwitchCount: 0,
      fullscreenExitCount: 0,
      screenshotAttempts: 0,
      keyboardViolations: 0,
      copyPasteAttempts: 0,
      networkRequestCount: 0,
      windowBlurCount: 0,
      violations: []
    }
  ];
  
  return scenarios[Math.floor(Math.random() * scenarios.length)];
}

export default function () {
  const baseUrl = 'http://127.0.0.1:8000/api/v1';
  const userIndex = (__VU - 1) % userData.length;
  const user = userData[userIndex];
  const ipIndex = __VU % ipAddresses.length;
  const clientIP = ipAddresses[ipIndex];
  
  // Select quiz from user data (they're already registered)
  const quizId = user.quiz_id;
  const quiz = quizConfigs.find(q => q.quiz_id === quizId) || quizConfigs[1]; // Fallback to quiz 2
  
  const headers = {
    'Content-Type': 'application/json',
    'X-Forwarded-For': clientIP,
    'X-Real-IP': clientIP,
    'User-Agent': `Mozilla/5.0 (LoadTest-${__VU}) AppleWebKit/537.36`,
  };

  // Use existing session token instead of registering
  const sessionToken = user.session_token;

  try {
    // Skip registration - directly submit quiz using existing session
    const answerStrategy = ['correct', 'incorrect', 'partial', 'random', 'mixed'][Math.floor(Math.random() * 5)];
    const answers = generateAnswers(quiz, answerStrategy);
    const duration = Math.floor(Math.random() * 600) + 60; // 1-10 minutes
    const submitReasons = [
      'Normal completion',
      'Time expired',
      'ডেভেলপার টুলস খোলা হয়েছে',
      'Tab switch detected',
      'Window focus lost',
      'Browser refresh detected'
    ];
    
    const submissionPayload = {
      quiz_id: quizId,
      answers: answers,
      duration: duration,
      submit_reason: submitReasons[Math.floor(Math.random() * submitReasons.length)],
      security_data: generateSecurityData()
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

    const submissionSuccess = check(submissionResponse, {
      'submission accepted': (r) => r.status === 200 || r.status === 202,
      'submission has batch info': (r) => {
        try {
          const data = JSON.parse(r.body);
          return data.data && (data.data.batch_id || data.data.submission_id);
        } catch {
          return false;
        }
      }
    });

    if (submissionSuccess) {
      // Step 2: Optional - Check submission status (simulate some users checking status)
      if (Math.random() < 0.3) { // 30% of users check status
        try {
          const subData = JSON.parse(submissionResponse.body);
          if (subData.data && subData.data.submission_id) {
            sleep(Math.random() * 3 + 1); // Wait 1-4 seconds
            
            const statusResponse = http.get(
              `${baseUrl}/quizzes/submission_status/${subData.data.submission_id}`,
              { headers: submissionHeaders }
            );
            
            check(statusResponse, {
              'status check successful': (r) => r.status === 200,
            });
          }
        } catch (e) {
          // Ignore status check errors
        }
      }
    } else {
      console.log(`Submission failed for user ${user.name} (${user.email}): ${submissionResponse.status} - ${submissionResponse.body}`);
    }

  } catch (error) {
    console.log(`Error for user ${user.name} (${user.email}): ${error.message}`);
  }

  // Random sleep to simulate realistic user behavior
  sleep(Math.random() * 1 + 0.5); // 0.5-1.5 seconds
}
