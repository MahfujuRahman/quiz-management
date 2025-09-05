import http from "k6/http";
import { sleep, check } from "k6";
import { SharedArray } from "k6/data";
import { exportedUsers } from "./users-data.js";

// Rate-limited test configuration to avoid 429 errors
export let options = {
  vus: 50, // 50 virtual users
  duration: "5m", // Run for 5 minutes
  rps: 55, // Limit to 55 requests per second globally (under 60/minute per IP)
  thresholds: {
    http_req_duration: ["p(95)<3000"], // 95% of requests must complete within 3s
    http_req_failed: ["rate<0.05"], // Error rate must be less than 5%
  },
};

// Much larger IP pool to distribute load and avoid rate limiting
const ipAddresses = [
  // Local network ranges
  "192.168.1.10",
  "192.168.1.11",
  "192.168.1.12",
  "192.168.1.13",
  "192.168.1.14",
  "192.168.1.15",
  "192.168.1.16",
  "192.168.1.17",
  "192.168.1.18",
  "192.168.1.19",
  "192.168.2.10",
  "192.168.2.11",
  "192.168.2.12",
  "192.168.2.13",
  "192.168.2.14",
  "10.0.0.10",
  "10.0.0.11",
  "10.0.0.12",
  "10.0.0.13",
  "10.0.0.14",
  "10.0.0.15",
  "10.0.0.16",
  "10.0.0.17",
  "10.0.0.18",
  "10.0.0.19",
  "10.1.1.10",
  "10.1.1.11",
  "10.1.1.12",
  "10.1.1.13",
  "10.1.1.14",
  "172.16.0.10",
  "172.16.0.11",
  "172.16.0.12",
  "172.16.0.13",
  "172.16.0.14",
  "172.16.1.10",
  "172.16.1.11",
  "172.16.1.12",
  "172.16.1.13",
  "172.16.1.14",
  // Simulated public IPs
  "203.112.218.10",
  "203.112.218.11",
  "203.112.218.12",
  "203.112.218.13",
  "123.456.789.10",
  "123.456.789.11",
  "123.456.789.12",
  "123.456.789.13",
  "45.127.89.10",
  "45.127.89.11",
  "45.127.89.12",
  "45.127.89.13",
  "78.142.34.10",
  "78.142.34.11",
  "78.142.34.12",
  "78.142.34.13",
  "156.213.45.10",
  "156.213.45.11",
  "156.213.45.12",
  "156.213.45.13",
];

// Load real users from database export
const userData = new SharedArray("users", function () {
  console.log(`Loaded ${exportedUsers.length} real users from database`);
  return exportedUsers;
});

export default function () {
  const baseUrl = "http://127.0.0.1:8000/api/v1";
  const userIndex = (__VU - 1) % userData.length;
  const user = userData[userIndex];

  // Better IP distribution to avoid rate limiting
  const ipIndex =
    (__VU * __ITER + Math.floor(Math.random() * 100)) % ipAddresses.length;
  const clientIP = ipAddresses[ipIndex];

  const headers = {
    "Content-Type": "application/json",
    "X-Forwarded-For": clientIP,
    "X-Real-IP": clientIP,
    "X-Client-IP": clientIP,
    "CF-Connecting-IP": clientIP, // Cloudflare header
    "User-Agent": `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 LoadTest-${__VU}`,
    Accept: "application/json, text/plain, */*",
    "Accept-Language": "en-US,en;q=0.9,bn;q=0.8",
    "Cache-Control": "no-cache",
  };

  try {
    // Add random delay to spread out requests and avoid rate limiting
    const initialDelay = Math.random() * 2; // 0-2 seconds
    sleep(initialDelay);

    // Use existing user session token (no registration needed)
    const sessionToken = user.session_token;
    const quizId = user.quiz_id;

    console.log(
      `[${clientIP}] Using user: ${user.name} (${user.email}) with quiz ${quizId}`
    );

    // Submit quiz using existing session
    const answers = {
      15: [57, 58], // Multiple choice question
      16: [61, 62], // Multiple choice question
    };

    const submissionPayload = {
      quiz_id: quizId,
      answers: answers,
      duration: Math.floor(Math.random() * 300) + 60, // 1-5 minutes
      submit_reason: "Load test completion",
      security_data: {
        tabSwitchCount: Math.floor(Math.random() * 3),
        fullscreenExitCount: Math.floor(Math.random() * 2),
        screenshotAttempts: 0,
        keyboardViolations: Math.floor(Math.random() * 5),
        copyPasteAttempts: 0,
        networkRequestCount: Math.floor(Math.random() * 10),
        windowBlurCount: Math.floor(Math.random() * 5),
        violations: [],
      },
    };

    const submissionHeaders = {
      ...headers,
      Authorization: `Bearer ${sessionToken}`,
    };

    const submissionResponse = http.post(
      `${baseUrl}/quizzes/submit_exam`,
      JSON.stringify(submissionPayload),
      { headers: submissionHeaders }
    );

    const submissionSuccess = check(submissionResponse, {
      "submission successful": (r) => r.status === 200 || r.status === 202,
      "submission has response": (r) => r.body && r.body.length > 0,
      "not rate limited": (r) => r.status !== 429,
    });

    if (!submissionSuccess) {
      if (submissionResponse.status === 429) {
        console.log(
          `🚫 Rate limited for ${user.name} from IP ${clientIP} - backing off`
        );
        sleep(Math.random() * 5 + 2); // Back off 2-7 seconds on rate limit
      } else {
        console.log(
          `❌ Submission failed for ${user.name} from IP ${clientIP}: ${
            submissionResponse.status
          } - ${submissionResponse.body.substring(0, 100)}`
        );
      }
    } else {
      console.log(
        `✅ Submission successful for ${user.name} from IP ${clientIP}`
      );

      // Optional: Check submission status for some users (but reduce frequency)
      if (Math.random() < 0.1) {
        // Only 10% check status to reduce load
        try {
          sleep(1 + Math.random() * 2); // Wait 1-3 seconds before status check

          const responseData = JSON.parse(submissionResponse.body);
          if (responseData.data && responseData.data.submission_id) {
            const statusResponse = http.get(
              `${baseUrl}/quizzes/submission_status/${responseData.data.submission_id}`,
              { headers: submissionHeaders }
            );

            check(statusResponse, {
              "status check successful": (r) => r.status === 200,
              "status not rate limited": (r) => r.status !== 429,
            });

            console.log(
              `📊 Status check for ${user.name} from IP ${clientIP}: ${statusResponse.status}`
            );
          }
        } catch (e) {
          console.log(`⚠️ Status check error for ${user.name}: ${e.message}`);
        }
      }
    }
  } catch (error) {
    console.log(
      `💥 Error for user ${user.name} from IP ${clientIP}: ${error.message}`
    );
  }

  // Variable sleep to spread out requests and avoid rate limiting
  const endDelay = Math.random() * 3 + 1; // 1-4 seconds
  sleep(endDelay);
}
