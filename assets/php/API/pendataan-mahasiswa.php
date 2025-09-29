<?php
// pendataan_mahasiswa.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
/*
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('#^https://([a-z0-9-]+\.)*ppimalaysia\.id$#i', $origin)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
} else {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>['message'=>'Origin not allowed']]);
    exit;
}
*/
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once '../conf.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ---- Secrets ----
$SECRET = getenv('TOKEN_SECRET') ?: null;
if (!$SECRET) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>['message'=>'Server misconfigured']]);
    exit;
}

// ---- Helpers ----
function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) throw new Exception('Invalid JSON body');
    return $data;
}
function ok(array $arr): void {
    echo json_encode(['success'=>true] + $arr, JSON_UNESCAPED_UNICODE);
    exit;
}
function fail(string $msg, int $code=400, ?string $extra=null): void {
    http_response_code($code);
    $out = ['success'=>false,'error'=>['message'=>$msg]];
    if ($extra) { error_log("DETAIL: ".$extra); }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}
function b64url(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function b64urldec(string $s): string|false { return base64_decode(strtr($s, '-_', '+/')); }

function make_token(int $student_id, int $ttl_seconds, string $secret, array $ctx): string {
    $iat = time();
    $exp = $iat + $ttl_seconds;
    $c = array_filter([
        'w'   => isset($ctx['w'])   ? (int)$ctx['w'] : null,
        'ua'  => isset($ctx['ua'])  ? (string)$ctx['ua'] : null,
        'ugt' => isset($ctx['ugt']) ? (string)$ctx['ugt'] : null,
    ], static fn($v) => $v !== null);

    $payload = json_encode(['sid'=>$student_id,'iat'=>$iat,'exp'=>$exp,'ctx'=>$c], JSON_UNESCAPED_SLASHES);
    $sig = hash_hmac('sha256', $payload, $secret, true);
    return b64url($payload).'.'.b64url($sig);
}
function verify_token(string $token, string $secret, array $expectCtx): ?int {
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) return null;
    [$p, $s] = $parts;
    $payload = b64urldec($p);
    $sig = b64urldec($s);
    if ($payload === false || $sig === false) return null;
    if (!hash_equals(hash_hmac('sha256', $payload, $secret, true), $sig)) return null;

    $data = json_decode($payload, true);
    if (!is_array($data) || !isset($data['sid'])) return null;
    if (isset($data['exp']) && time() > (int)$data['exp']) return null;

    $ctx = $data['ctx'] ?? [];
    if (is_array($ctx) && $ctx) {
        $expected = [
            'w'   => isset($expectCtx['w'])   ? (int)$expectCtx['w'] : null,
            'ua'  => isset($expectCtx['ua'])  ? (string)$expectCtx['ua'] : null,
            'ugt' => isset($expectCtx['ugt']) ? (string)$expectCtx['ugt'] : null,
        ];
        foreach ($ctx as $k => $v) {
            if (!array_key_exists($k, $expected) || $expected[$k] !== $v) return null;
        }
    }
    return (int)$data['sid'];
}
function bearer_token_or_body(array $body): string {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
    if (stripos($auth, 'Bearer ') === 0) return trim(substr($auth, 7));
    return (string)($body['token'] ?? '');
}
function read_token_context(array $body): array {
    $width = isset($body['deviceScreenWidth']) ? (int)$body['deviceScreenWidth'] : null;
    $uaHdr = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ua = isset($body['useragent']) && trim((string)$body['useragent']) !== '' ? (string)$body['useragent'] : $uaHdr;
    $ugt = isset($body['user_generated_token']) ? (string)$body['user_generated_token'] : null;
    return ['w'=>$width, 'ua'=>$ua ?: null, 'ugt'=>$ugt];
}

// ---- Redaction / Lookup / Queries ----
function redact_student(array $r): array {
    $passport = $r['passport'];
    if ($passport) $passport = preg_replace('/^(.{4}).+$/', '$1****', $passport);
    $phone = $r['phone_number'];
    if ($phone) $phone = preg_replace('/^.*?(\d{4})$/', '****$1', $phone);
    $addr = $r['address'];
    if ($addr) $addr = mb_strlen($addr) > 12 ? (mb_substr($addr,0,12).'…') : $addr;

    return [
        'student_id' => (int)$r['student_id'],
        'fullname'   => $r['fullname'],
        'dob'        => $r['dob'],
        'email'      => $r['email'],
        'passport'   => $passport,
        'phone'      => $phone,
        'university_id' => $r['university_id'] !== null ? (int)$r['university_id'] : null,
        'level_of_qualification_id' => $r['level_of_qualification_id'] !== null ? (int)$r['level_of_qualification_id'] : null,
        'expected_graduate' => $r['expected_graduate'],
        'address'    => $r['address'] ?? null,
        'postcode_id'=> $r['postcode_id'] ?? null,
        'status_id'  => $r['status_id'] !== null ? (int)$r['status_id'] : null,
        'is_active'  => (bool)$r['is_active'],
        'created_at' => $r['created_at'],
        'updated_at' => $r['updated_at']
    ];
}
function lookup_university_id(mysqli $conn, ?string $university_id, ?string $university_name): ?int {
    if ($university_id !== null && $university_id !== '') return (int)$university_id;
    if (!$university_name) return null;
    $stmt = $conn->prepare('SELECT university_id FROM university WHERE university_name = ? LIMIT 1');
    $stmt->bind_param('s', $university_name);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ? (int)$res['university_id'] : null;
}
function get_student_row(mysqli $conn, int $sid): ?array {
    $stmt = $conn->prepare('SELECT * FROM student WHERE student_id = ? LIMIT 1');
    $stmt->bind_param('i', $sid);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

// ---- Matching logic ----
function select_match(mysqli $conn, array $in): ?array {
    $uid = lookup_university_id($conn, $in['university_id'] ?? null, $in['university'] ?? null);

    // Hard match: dob + passport + phone + university
    if (!empty($in['dob']) && !empty($in['passport']) && !empty($in['phone_number']) && $uid) {
        $stmt = $conn->prepare('SELECT * FROM student WHERE dob = ? AND passport = ? AND phone_number = ? AND university_id = ? LIMIT 1');
        $stmt->bind_param('sssi', $in['dob'], $in['passport'], $in['phone_number'], $uid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) return $row;
    }

    // Soft match: LOWER(fullname) + dob + at least 2 of {passport, phone, university}
    if (empty($in['fullname']) || empty($in['dob'])) return null;

    $stmt = $conn->prepare('SELECT * FROM student WHERE LOWER(fullname) = ? AND dob = ?');
    $stmt->bind_param('ss', $in['fullname'], $in['dob']); // $in['fullname'] already lowercased
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $score = 0;
        if (!empty($in['passport']) && $row['passport'] === $in['passport']) $score++;
        if (!empty($in['phone_number']) && $row['phone_number'] === $in['phone_number']) $score++;
        if ($uid && (int)$row['university_id'] === $uid) $score++;
        if ($score >= 2) return $row;
    }
    return null;
}

function allowed_student_fields(): array {
    return [
        'fullname','university_id','dob','email','passport','phone_number',
        'postcode_id','address','expected_graduate','degree','level_of_qualification_id'
    ];
}

// ---- Validation / Normalization ----
function norm_fullname(?string $s): string {
    $s = trim((string)$s);
    return mb_strtolower($s, 'UTF-8');
}
function norm_dob(?string $s): string {
    $s = trim((string)$s);
    if ($s !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) fail('dob must be YYYY-MM-DD', 422);
    return $s;
}
function norm_passport(?string $s): string {
    $s = strtoupper(trim((string)$s));
    if ($s !== '' && !preg_match('/^[A-Z0-9]{3,30}$/', $s)) fail('passport format invalid', 422);
    return $s;
}
function norm_phone(?string $s): string {
    $s = preg_replace('/[^\d\+]/', '', (string)$s);
    if ($s !== '' && !preg_match('/^\+?\d{6,20}$/', $s)) fail('phone_number format invalid', 422);
    return $s;
}
function norm_email(?string $s): ?string {
    $s = trim((string)$s);
    if ($s === '') return null;
    if (!filter_var($s, FILTER_VALIDATE_EMAIL)) fail('email invalid', 422);
    return $s;
}

// ---------------- Main ----------------
try {
    if (!isset($conn) || $conn->connect_error) throw new Exception('Database connection failed');

    $body = json_input();
    $action = strtolower(trim((string)($body['action'] ?? '')));

    if ($action === 'check') {
        // normalize
        $in = [
            'fullname'      => norm_fullname($body['fullname'] ?? ''),
            'dob'           => norm_dob($body['dob'] ?? ''),        // YYYY-MM-DD
            'passport'      => norm_passport($body['passport'] ?? ''),
            'phone_number'  => norm_phone($body['phone_number'] ?? ''),
            'university_id' => isset($body['university_id']) ? (string)$body['university_id'] : null,
            'university'    => trim((string)($body['university'] ?? '')),
        ];
        if ($in['fullname']==='' || $in['dob']==='') fail('fullname and dob are required', 422);

        // token context (optional here, included if present)
        $ctx = read_token_context($body);

        $conn->begin_transaction();
        try {
            $row = select_match($conn, $in);
            if ($row) {
                $token = make_token((int)$row['student_id'], 86400, $SECRET, $ctx); // 24h
                $conn->commit();
                ok(['mode'=>'existing','token'=>$token,'student'=>redact_student($row)]);
            }

            // Insert minimal record
            $uid = lookup_university_id($conn, $in['university_id'], $in['university']);
            if ($uid === null) {
                $stmt = $conn->prepare('INSERT INTO student (fullname,dob,passport,phone_number) VALUES (?,?,?,?)');
                $stmt->bind_param('ssss', $in['fullname'], $in['dob'], $in['passport'], $in['phone_number']);
            } else {
                $stmt = $conn->prepare('INSERT INTO student (fullname,dob,passport,phone_number,university_id) VALUES (?,?,?,?,?)');
                $stmt->bind_param('ssssi', $in['fullname'], $in['dob'], $in['passport'], $in['phone_number'], $uid);
            }
            $stmt->execute();
            $sid = (int)$conn->insert_id;

            $row = get_student_row($conn, $sid);
            if (!$row) { throw new Exception('Post-insert fetch failed'); }

            $token = make_token($sid, 86400, $SECRET, $ctx);
            $conn->commit();
            ok(['mode'=>'created','token'=>$token,'student'=>redact_student($row)]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('check action error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }
    elseif ($action === 'edit') {
        $ctx = read_token_context($body);
        if ($ctx['w'] === null || $ctx['ua'] === null || $ctx['ugt'] === null) fail('Missing token context', 401);

        $token = bearer_token_or_body($body);
        $sid = $token ? verify_token($token, $SECRET, $ctx) : null;
        if (!$sid) fail('Invalid token', 401);

        // university by name
        if (array_key_exists('university', $body)) {
            $uid = lookup_university_id($conn, $body['university_id'] ?? null, $body['university'] ?? null);
            $body['university_id'] = $uid;
        }

        // normalize if present
        if (array_key_exists('fullname', $body))      $body['fullname'] = norm_fullname($body['fullname']);
        if (array_key_exists('dob', $body))           $body['dob'] = norm_dob($body['dob']);
        if (array_key_exists('passport', $body))      $body['passport'] = norm_passport($body['passport']);
        if (array_key_exists('phone_number', $body))  $body['phone_number'] = norm_phone($body['phone_number']);
        if (array_key_exists('email', $body))         $body['email'] = norm_email($body['email']);

        $fields = allowed_student_fields();
        $set = [];
        $vals = [];
        $types = '';

        foreach ($fields as $f) {
            if (array_key_exists($f, $body)) {
                $set[] = "$f = ?";
                $val = $body[$f];
                if ($val === '') $val = null;
                $vals[] = $val;
                $types .= in_array($f, ['university_id','level_of_qualification_id','postcode_id','status_id'], true) ? 'i' : 's';
            }
        }
        if (!$set) fail('No updatable fields provided', 422);

        $sql = 'UPDATE student SET '.implode(',', $set).' WHERE student_id = ?';
        $types .= 'i';
        $vals[] = $sid;

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$vals);
            $stmt->execute();

            $row = get_student_row($conn, $sid);
            $conn->commit();
            ok(['updated'=>true, 'student'=>redact_student($row)]);
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            error_log('edit action DB error: '.$e->Message());
            fail('Database error', 409);
        }
    }
    elseif ($action === 'add') {
        $ctx = read_token_context($body);
        if ($ctx['w'] === null || $ctx['ua'] === null || $ctx['ugt'] === null) fail('Missing token context', 401);

        $token = bearer_token_or_body($body);
        $sid = $token ? verify_token($token, $SECRET, $ctx) : null;
        if (!$sid) fail('Invalid token', 401);

        $resource = strtolower(trim((string)($body['resource'] ?? '')));
        if (!in_array($resource, ['ppim','ppi_campus'], true)) fail('Unsupported resource', 422);

        $conn->begin_transaction();
        try {
            if ($resource === 'ppim') {
                $start_year = (int)($body['start_year'] ?? 0);
                $end_year   = isset($body['end_year']) ? (int)$body['end_year'] : null;
                $department = trim((string)($body['department'] ?? ''));
                $position   = trim((string)($body['position'] ?? ''));
                $desc       = trim((string)($body['description'] ?? ''));

                $stmt = $conn->prepare('INSERT INTO ppim (student_id,start_year,end_year,department,position,description) VALUES (?,?,?,?,?,?)');
                $stmt->bind_param('iiisss', $sid, $start_year, $end_year, $department, $position, $desc);
                $stmt->execute();
            } else {
                $uid = lookup_university_id($conn, $body['university_id'] ?? null, $body['university'] ?? null);
                if (!$uid) { throw new Exception('university_id or valid university name required'); }

                $start_year = (int)($body['start_year'] ?? 0);
                $end_year   = isset($body['end_year']) ? (int)$body['end_year'] : null;
                $department = trim((string)($body['department'] ?? ''));
                $position   = trim((string)($body['position'] ?? ''));
                $desc       = trim((string)($body['description'] ?? ''));

                $stmt = $conn->prepare('INSERT INTO ppi_campus (student_id,start_year,end_year,university_id,department,position,description) VALUES (?,?,?,?,?,?,?)');
                $stmt->bind_param('iiiisss', $sid, $start_year, $end_year, $uid, $department, $position, $desc);
                $stmt->execute();
            }

            $conn->commit();
            ok(['inserted'=>true, 'resource'=>$resource]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('add action error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }
    else {
        fail('Unknown action. Use one of: check, edit, add', 400);
    }
}
catch (Throwable $e) {
    error_log('Fatal: '.$e->getMessage());
    fail('Internal server error', 500);
}
finally {
    if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
}