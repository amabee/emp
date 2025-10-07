<?php
/**
 * Simple end-to-end test script for leave endpoints.
 * Usage (from web server root): php tests/test_leaves.php
 * This script uses curl to call the AJAX endpoints. It requires the dev server to be running
 * or that the tests are executed in an environment where sessions/auth are not required.
 *
 * The script demonstrates three simple scenarios:
 *  - Fetch leaves as anonymous (should return authentication error)
 *  - Fetch leaves as employee by simulating session via cookie file (developer must login via browser first and save cookies)
 *  - Submit a leave request using a simple POST (note: requires a valid session cookie)
 *
 * This is intentionally minimal and intended as a developer helper. If you want fully automated tests,
 * consider using PHPUnit with a test DB and programmatically creating users and sessions.
 */

$base = 'http://localhost/emp';
$cookieFile = sys_get_temp_dir() . '/emp_test_cookie.txt';

function call($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init();
    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 10,
    ];

    if ($method === 'POST') {
        $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
    }

    if ($cookieFile) {
        $opts[CURLOPT_COOKIEFILE] = $cookieFile;
        $opts[CURLOPT_COOKIEJAR] = $cookieFile;
    }

    curl_setopt_array($ch, $opts);
    $out = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['out' => $out, 'err' => $err, 'info' => $info];
}

// 1) Unauthenticated call to get_leaves.php
echo "[1] Unauthenticated get_leaves.php\n";
$r = call($base . '/ajax/get_leaves.php');
echo "HTTP: " . ($r['info']['http_code'] ?? 'n/a') . "\n";
echo $r['out'] . "\n\n";

// 2) If you have already logged in with a browser, reuse cookies by pointing to cookie file
if (file_exists($cookieFile)) {
    echo "[2] Authenticated get_leaves.php using existing cookie file\n";
    $r2 = call($base . '/ajax/get_leaves.php', 'GET', [], $cookieFile);
    echo "HTTP: " . ($r2['info']['http_code'] ?? 'n/a') . "\n";
    echo $r2['out'] . "\n\n";

    // 3) Submit a simple leave request (adjust dates to future)
    echo "[3] Submit add_leave.php (requires a valid logged-in session)\n";
    $post = [
        'leave_type' => 'vacation',
        'start_date' => date('Y-m-d', strtotime('+7 days')),
        'end_date' => date('Y-m-d', strtotime('+9 days')),
        'reason' => 'Automated test request from test_leaves.php'
    ];
    $r3 = call($base . '/ajax/add_leave.php', 'POST', $post, $cookieFile);
    echo "HTTP: " . ($r3['info']['http_code'] ?? 'n/a') . "\n";
    echo $r3['out'] . "\n\n";
} else {
    echo "[INFO] No cookie file found at $cookieFile. To run authenticated tests, login via browser and save cookies to that path or modify this script to authenticate programmatically.\n";
}

echo "Done.\n";
