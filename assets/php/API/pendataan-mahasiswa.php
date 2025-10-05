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
function make_token(int $sid, int $ttl, string $secret, array $ctx): string {
    if (!isset($ctx['w'], $ctx['ua'], $ctx['ugt'])) throw new InvalidArgumentException('w, ua, ugt required');
    $iat = time(); 
    $exp = $iat + $ttl;
    $payload = json_encode(['sid'=>$sid,'iat'=>$iat,'exp'=>$exp,'ctx'=>['w'=>(int)$ctx['w'],'ua'=>(string)$ctx['ua'],'ugt'=>(string)$ctx['ugt']]], JSON_UNESCAPED_SLASHES);
    $sig = hash_hmac('sha256', $payload, $secret, true);
    return b64url($payload).'.'.b64url($sig);
}
function verify_token(string $token, string $secret, array $expectCtx): ?int {
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) return null;
    [$p,$s] = $parts; $payload = b64urldec($p);
    $sig = b64urldec($s);
    if ($payload === false || $sig === false) return null;
    if (!hash_equals(hash_hmac('sha256', $payload, $secret, true), $sig)) return null;
    $data = json_decode($payload, true);
    if (!is_array($data) || !isset($data['sid'])) return null;
    if (isset($data['exp']) && time() > (int)$data['exp']) return null;
    $ctx = $data['ctx'] ?? null;
    if (!is_array($ctx) || !isset($ctx['w'], $ctx['ua'], $ctx['ugt'])) return null;
    $exp = ['w'=>(int)($expectCtx['w'] ?? -1), 'ua'=>(string)($expectCtx['ua'] ?? ''), 'ugt'=>(string)($expectCtx['ugt'] ?? '')];
    foreach (['w','ua','ugt'] as $k) if ($ctx[$k] !== $exp[$k]) return null;
    return (int)$data['sid'];
}
function bearer_token_or_body(array $body): string {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
    if (stripos($auth, 'Bearer ') === 0) return trim(substr($auth, 7));
    return (string)($body['token'] ?? '');
}
function read_token_context(array $body): array {
    if (!isset($body['w'], $body['ua'], $body['ugt'])) fail('Missing context: w, ua, ugt required', 422);
    $w = (int)$body['w']; $ua = (string)$body['ua']; $ugt = (string)$body['ugt'];
    if ($w <= 0 || $ua === '' || $ugt === '') fail('invalid w, ua, or ugt', 422);
    return ['w'=>$w,'ua'=>$ua,'ugt'=>$ugt];
}
// ---- Redaction / Lookup / Queries ----
function redact_student(mysqli $conn, array $r): array {
    $passport = $r['passport'];
    if ($passport) $passport = preg_replace('/^(.{4}).+$/', '$1****', $passport);
    $email = $r['email'];
    $email = ($email && filter_var($email,FILTER_VALIDATE_EMAIL) && strlen(($p=explode('@',$email,2))[0])>4) ? substr($p[0],0,2).str_repeat('*',strlen($p[0])-4).substr($p[0],-2).'@'.$p[1] : $email;
    $phone = $r['phone_number'];
    $phone = $phone ? preg_replace_callback('/^(\+?\d{3})(\d+)(\d{3})$/', fn($m) => $m[1] . str_repeat('*', strlen($m[2])) . $m[3], $phone) : null;
    $addr = $r['address'];
    if ($addr) $addr = mb_substr($addr, 0, 12) . str_repeat('*', max(0, mb_strlen($addr) - 12));
    $uid = isset($r['university_id']) ? (int)$r['university_id'] : null;
    $uname = $uid ? lookup_university($conn, $uid) : null;
    $sid = isset($r['student_id']) ? (int)$r['student_id'] : null;
    $ppi = $sid ? lookup_ppi_record($conn, $sid, $uid) : [],
    $ppim = $sid ? lookup_ppim_record($conn, $sid) : [],
    return [
        'fullname'   => $r['fullname'],
        'dob'        => $r['dob'],
        'email'      => $email,
        'passport'   => $passport,
        'phone'      => $phone,
        'university_id' => $uid,
        'university' => $uname,
        'degree' => $r['degree'] ?? null,
        'level_of_qualification_id' => isset($r['level_of_qualification_id']) ? (int)$r['level_of_qualification_id'] : null,
        'expected_graduate' => $r['expected_graduate'] ?? null,
        'address'    => $addr,
        'postcode_id'=> $r['postcode_id'] ?? null,
        'status_id'  => isset($r['status_id']) ? (int)$r['status_id'] : null,
        'ppi' => $ppi,
        'ppim' => $ppim
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
function lookup_university(mysqli $conn, ?int $university_id): ?string {
    if (!$university_id) return null;
    $stmt = $conn->prepare('SELECT university_name FROM university WHERE university_id = ? LIMIT 1');
    $stmt->bind_param('i', $university_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ? (string)$res['university_name'] : null;
}
function lookup_ppim_record(mysqli $conn, ?int $student_id) : array {
    if (!$student_id) return [];
    $stmt = $conn->prepare(
        'SELECT ppim_id, start_year, end_year, department, position, description, is_active
         FROM ppim
         WHERE student_id = ?
         ORDER BY COALESCE(end_year,9999) DESC, start_year DESC, ppim_id DESC'
    );
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = [
            'ppim_id'      => (int)$r['ppim_id'],
            'start_year'   => (int)$r['start_year'],
            'end_year'     => isset($r['end_year']) ? (int)$r['end_year'] : null,
            'department'   => (string)$r['department'],
            'position'     => (string)$r['position'],
            'description'  => (string)$r['description'],
            'is_active'    => (int)$r['is_active'],
        ];
    }
    $stmt->close();
    return $out;
}
function lookup_ppi_record(mysqli $conn, ?int $student_id, ?int $university_id) : array {
    if (!$student_id) return [];
    // When $university_id is null, show all campus entries for this student.
    if ($university_id) {
        $stmt = $conn->prepare(
            'SELECT ppi_campus_id, university_id, start_year, end_year, department, position, description, is_active
             FROM ppi_campus
             WHERE student_id = ? AND university_id = ?
             ORDER BY COALESCE(end_year,9999) DESC, start_year DESC, ppi_campus_id DESC'
        );
        $stmt->bind_param('ii', $student_id, $university_id);
    } else {
        $stmt = $conn->prepare(
            'SELECT ppi_campus_id, university_id, start_year, end_year, department, position, description, is_active
             FROM ppi_campus
             WHERE student_id = ?
             ORDER BY COALESCE(end_year,9999) DESC, start_year DESC, ppi_campus_id DESC'
        );
        $stmt->bind_param('i', $student_id);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = [
            'ppi_campus_id' => (int)$r['ppi_campus_id'],
            'university_id' => isset($r['university_id']) ? (int)$r['university_id'] : null,
            'start_year'    => (int)$r['start_year'],
            'end_year'      => isset($r['end_year']) ? (int)$r['end_year'] : null,
            'department'    => (string)$r['department'],
            'position'      => (string)$r['position'],
            'description'   => (string)$r['description'],
            'is_active'     => (int)$r['is_active'],
        ];
    }
    $stmt->close();
    return $out;
}
function get_student_row(mysqli $conn, int $sid): ?array {
    $stmt = $conn->prepare('SELECT * FROM student WHERE student_id = ? LIMIT 1');
    $stmt->bind_param('i', $sid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function get_student_uni(mysqli $conn, int $sid): ?array {
    $stmt = $conn->prepare('SELECT university_id FROM student WHERE student_id = ? LIMIT 1');
    $stmt->bind_param('i', $sid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

// ---- Matching logic ----
function select_match(mysqli $conn, array $in): ?array {
    $uid = lookup_university_id($conn, $in['university_id'] ?? null, $in['university'] ?? null);
    $dob = $in['dob'] ?? '';
    $pass = $in['passport'] ?? '';
    $phone = $in['phone_number'] ?? '';
    $name = $in['fullname'] ?? '';
    $email = $in['email'] ?? '';

    // 1) dob + passport + phone + university + email
    if ($dob !== '' && $pass !== '' && $phone !== '' && $uid) {
        $stmt = $conn->prepare('SELECT * FROM student WHERE dob=? AND passport=? AND phone_number=? AND university_id=? AND email=? LIMIT 1');
        $stmt->bind_param('sssis', $dob, $pass, $phone, $uid, $email);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) return $row;
    }

    // 2) Fallback ONLY when DB has empty DOB: fullname + university AND (dob IS NULL OR dob='')
    if ($name !== '' && $uid) {
        $stmt = $conn->prepare('SELECT * FROM student WHERE LOWER(fullname)=? AND university_id=? AND (dob IS NULL OR dob="") LIMIT 1');
        $stmt->bind_param('si', $name, $uid);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) return $row;
    }

    /// 3) Soft match: fullname + dob + at least 2 of {passport, phone, university, email} is correct
    if ($name === '' || $dob === '') return null;

    $stmt = $conn->prepare('SELECT * FROM student WHERE LOWER(fullname)=? AND dob=?');
    $stmt->bind_param('ss', $name, $dob);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $score = 0;
        if ($pass  !== '' && $row['passport'] === $pass) $score++;
        if ($email !== '' && $row['email'] === $email) $score++;
        if ($phone !== '' && $row['phone_number'] === $phone) $score++;
        if ($uid && (int)$row['university_id'] === $uid) $score++;
        if ($score >= 2) return $row;
    }
    return null;
}
function allowed_student_fields(): array {
    return [
        'fullname','university_id','dob','email','passport','phone_number','status_id',
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

    //get student data by student information
    if ($action === 'check') {
        // normalize
        $in = [
            'fullname'      => norm_fullname($body['fullname'] ?? ''),
            'dob'           => norm_dob($body['dob'] ?? ''),        // YYYY-MM-DD
            'passport'      => norm_passport($body['passport'] ?? ''),
            'phone_number'  => norm_phone($body['phone_number'] ?? ''),
            'university_id' => isset($body['university_id']) ? (string)$body['university_id'] : null,
            'university'    => trim((string)($body['university'] ?? '')),
            'email'         => norm_email($body['email'] ?? ''),
        ];
        if ($in['fullname']==='' || $in['university_id']==='') fail('fullname and university_id are required', 422);
        
        $ctx = read_token_context($body);

        $conn->begin_transaction();
        try {
            $row = select_match($conn, $in);
            if ($row) {
                $token = make_token((int)$row['student_id'], 3600, $SECRET, $ctx);
                $conn->commit();
                ok(['mode'=>'existing','token'=>$token,'student'=>redact_student($conn, $row)]);
            }
            fail('student not found', 404);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('check action error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }
    //get student data by token
    elseif ($action === 'get') {
        $ctx = read_token_context($body);
        $sid = verify_token(bearer_token_or_body($body), $SECRET, $ctx);
        if (!$sid) fail('Invalid token', 401);

        $conn->begin_transaction();
        try {
            $row = get_student_row($conn, $sid);
            if (!$row) fail('student not found', 404);

            $student = redact_student($conn, $row);

            $conn->commit();
            ok(['student' => $student]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('get action error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }

    //edit student data by token
    elseif ($action === 'edit') {
        // verify who is editing
        $ctx = read_token_context($body);
        $sid = verify_token(bearer_token_or_body($body), $SECRET, $ctx);
        if (!$sid) fail('Invalid token', 401);

        // allow resolving university by name -> id
        if (array_key_exists('university', $body)) {
            $uid = lookup_university_id($conn, $body['university_id'] ?? null, $body['university'] ?? null);
            $body['university_id'] = $uid;
        }

        // whitelist of updatable columns in student table
        $allowed = [
            'fullname', 'university_id', 'dob', 'email', 'passport', 'phone_number',
            'status_id', 'postcode_id', 'address', 'expected_graduate', 'degree',
            'level_of_qualification_id'
        ];

        // per-field normalizers
        $norm = [
            'fullname'                   => fn($v) => norm_fullname($v),
            'university_id'             => fn($v) => ($v === '' || $v === null) ? null : (int)$v,
            'dob'                        => fn($v) => norm_dob($v),
            'email'                      => fn($v) => array_key_exists('email', $body) ? norm_email($v) : null,
            'passport'                   => fn($v) => norm_passport($v),
            'phone_number'               => fn($v) => norm_phone($v),
            'status_id'                  => fn($v) => ($v === '' || $v === null) ? null : (int)$v,
            'postcode_id'                => fn($v) => ($v === '' || $v === null) ? null : (int)$v,
            'address'                    => fn($v) => trim((string)$v),
            'expected_graduate'          => fn($v) => norm_dob($v),
            'degree'                     => fn($v) => ($v === '' ? null : trim((string)$v)),
            'level_of_qualification_id'  => fn($v) => ($v === '' || $v === null) ? null : (int)$v,
        ];

        // MySQLi bind types per field
        $typesMap = [
            'fullname'                   => 's',
            'university_id'             => 'i',
            'dob'                        => 's',
            'email'                      => 's',
            'passport'                   => 's',
            'phone_number'               => 's',
            'status_id'                  => 'i',
            'postcode_id'                => 'i',
            'address'                    => 's',
                'expected_graduate'          => 's',
                'degree'                     => 's',
                'level_of_qualification_id'  => 'i',
        ];

        // collect updates
        $set = [];
        $vals = [];
        $types = '';

        foreach ($allowed as $f) {
            if (array_key_exists($f, $body)) {
                $val = $body[$f];
                $val = $norm[$f]($val);
                if ($val === '') $val = null;
                $set[]  = "$f = ?";
                $vals[] = $val;
                $types .= $typesMap[$f];
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

            ok(['updated' => true, 'student' => redact_student($conn, $row)]);
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            error_log('edit action DB error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }
    //add completed student data
    elseif ($action === 'add') {
        // no verify; only context required to mint token
        $ctx = read_token_context($body);

        // resolve university id from id or name
        $uid = lookup_university_id($conn, (string)$body['university_id'] ?? null, $body['university'] ?? null);
        if (!$uid) fail('university_id or valid university name required', 422);

        // normalize
        $fullname    = norm_fullname($body['fullname'] ?? '');
        $dob         = norm_dob($body['dob'] ?? '');
        $email       = array_key_exists('email', $body) ? norm_email($body['email']) : null;
        $passport    = norm_passport($body['passport'] ?? '');
        $phone       = norm_phone($body['phone_number'] ?? '');
        $address     = trim((string)($body['address'] ?? ''));
        $expected    = norm_dob($body['education_graduation'] ?? '');
        $degree      = isset($body['education_programme']) ? trim((string)$body['education_programme']) : null;
        $level_id    = isset($body['education_level']) ? (int)$body['education_level'] : null;
        $postcode_id = isset($body['postcode_id']) ? (string)$body['postcode_id'] : null;

        if ($fullname === '' || $dob === '' || $passport === '' || $phone === '')
            fail('fullname, dob, passport, phone_number are required', 422);

        // status: 1=graduated, 0=ongoing (adjust to your enum)
        $status_id = isset($body['status_id'])
            ? (int)$body['status_id']
            : (($expected !== '' && $expected < date('Y-m-d')) ? 1 : 0);

        $is_active = 1;

        $conn->begin_transaction();
        try {
            // insert student
            $sql = 'INSERT INTO student
                (fullname, university_id, dob, email, passport, phone_number, postcode_id, address,
                expected_graduate, degree, level_of_qualification_id, status_id, is_active)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'sissssssssiii',
                $fullname, $uid, $dob, $email, $passport, $phone, $postcode_id, $address,
                $expected, $degree, $level_id, $status_id, $is_active
            );
            $stmt->execute();
            $new_sid = (int)$conn->insert_id;

            // optional PPIM
            if (!empty($body['ppim']) && is_array($body['ppim'])) {
                $p = $body['ppim'];
                $stmt = $conn->prepare('INSERT INTO ppim
                    (student_id,start_year,end_year,department,position,description)
                    VALUES (?,?,?,?,?,?)');
                $ppim_start = (int)($p['startYear'] ?? 0);
                $ppim_end   = isset($p['endYear']) ? (int)$p['endYear'] : null;
                $ppim_dept  = trim((string)($p['department'] ?? ''));
                $ppim_pos   = trim((string)($p['position'] ?? ''));
                $ppim_desc  = trim((string)($p['additionalInfo'] ?? ''));
                $stmt->bind_param('iiisss', $new_sid, $ppim_start, $ppim_end, $ppim_dept, $ppim_pos, $ppim_desc);
                $stmt->execute();
            }

            // optional PPI campus
            if (!empty($body['ppi_campus']) && is_array($body['ppi_campus'])) {
                $p = $body['ppi_campus'];
                $stmt = $conn->prepare('INSERT INTO ppi_campus
                    (student_id,start_year,end_year,university_id,department,position,description)
                    VALUES (?,?,?,?,?,?,?)');
                $pc_start = (int)($p['startYear'] ?? 0);
                $pc_end   = isset($p['endYear']) ? (int)$p['endYear'] : null;
                $pc_dept  = trim((string)($p['department'] ?? ''));
                $pc_pos   = trim((string)($p['position'] ?? ''));
                $pc_desc  = trim((string)($p['additionalInfo'] ?? ''));
                $stmt->bind_param('iiiisss', $new_sid, $pc_start, $pc_end, $uid, $pc_dept, $pc_pos, $pc_desc);
                $stmt->execute();
            }

            // load row and mint token for the new student
            $row   = get_student_row($conn, $new_sid);
            $token = make_token($new_sid, 3600, $SECRET, $ctx);

            $conn->commit();
            ok([
                'inserted' => true,
                'token'    => $token,
                'student'  => redact_student($conn, $row)
            ]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('add action error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }
    elseif ($action === 'addPPI') {
        // mandatory
        $ctx = read_token_context($body);
        $sid = verify_token(bearer_token_or_body($body), $SECRET, $ctx);
        if (!$sid) fail('Invalid token', 401);

        // get type: ppim | ppi_campus
        $type = strtolower(trim((string)($body['type'] ?? '')));
        if ($type !== 'ppim' && $type !== 'ppi_campus') fail('type must be ppim or ppi_campus', 422);

        // normalize inputs
        $startYear = (int)($body['start_year'] ?? 0);
        $endYear   = isset($body['end_year']) && $body['end_year'] !== '' ? (int)$body['end_year'] : null;
        $dept      = trim((string)($body['department'] ?? ''));
        $pos       = trim((string)($body['position'] ?? ''));
        $desc      = trim((string)($body['additionalInfo'] ?? ''));

        // check validation
        if ($startYear < 1900 || $startYear > 2100) fail('start_year invalid', 422);
        if ($endYear !== null && ($endYear < 1900 || $endYear > 2100)) fail('end_year invalid', 422);
        if ($endYear !== null && $endYear < $startYear) fail('end_year < start_year', 422);

        // if campus entry, resolve university_id
        $ppiUniId = null;
        if ($type === 'ppi_campus') {
            $ppiUniId = lookup_university_id($conn, $body['university_id'] ?? null, $body['university'] ?? null);
            if (!$ppiUniId) {
                // fallback to student's own university_id
                $sr = get_student_uni($conn, $sid);
                if (!$sr) fail('student not found', 404);
                $ppiUniId = (int)($sr['university_id'] ?? 0);
            }
            if (!$ppiUniId) fail('university_id required for ppi_campus', 422);
        }
        $conn->begin_transaction();
        try {
            if ($type === 'ppim') {
                // ppim(student_id,start_year,end_year,department,position,description,is_active)
                $stmt = $conn->prepare(
                    'INSERT INTO ppim (student_id,start_year,end_year,department,position,description)
                    VALUES (?,?,?,?,?,?)'
                );
                $stmt->bind_param('iiisss', $sid, $startYear, $endYear, $dept, $pos, $desc);
                $stmt->execute();
            } else {
                // ppi_campus(student_id,university_id,start_year,end_year,department,position,description,is_active)
                $stmt = $conn->prepare(
                    'INSERT INTO ppi_campus (student_id,university_id,start_year,end_year,department,position,description)
                    VALUES (?,?,?,?,?,?,?)'
                );
                $stmt->bind_param('iiiisss', $sid, $ppiUniId, $startYear, $endYear, $dept, $pos, $desc);
                $stmt->execute();
            }

            $row = get_student_row($conn, $sid);
            $conn->commit();

            ok([
                'added'   => true,
                'type'    => $type,
                'student' => redact_student($conn, $row),
            ]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('addPPI error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }
    elseif ($action === 'delPPI') {
        // auth
        $ctx = read_token_context($body);
        $sid = verify_token(bearer_token_or_body($body), $SECRET, $ctx);
        if (!$sid) fail('Invalid token', 401);

        // type
        $type = strtolower(trim((string)($body['type'] ?? '')));
        if ($type !== 'ppim' && $type !== 'ppi_campus') fail('type must be ppim or ppi_campus', 422);

        // id per type
        if ($type === 'ppim') {
            $id = (int)($body['ppim_id'] ?? 0);
            if ($id <= 0) fail('ppi_id required', 422);
            $sql  = 'DELETE FROM ppim WHERE ppim_id = ? AND student_id = ? LIMIT 1';
            $bind = fn($stmt) => $stmt->bind_param('ii', $id, $sid);
        } else {
            $id = (int)($body['ppi_campus_id'] ?? 0);
            if ($id <= 0) fail('pp_campus_id required', 422);
            $sql  = 'DELETE FROM ppi_campus WHERE ppi_campus_id = ? AND student_id = ? LIMIT 1';
            $bind = fn($stmt) => $stmt->bind_param('ii', $id, $sid);
        }

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare($sql);
            $bind($stmt);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $conn->rollback();
                fail('ppi record not found', 404);
            }

            $row = get_student_row($conn, $sid);
            $conn->commit();

            ok([
                'deleted' => true,
                'type'    => $type,
                'id'      => $id,
                'student' => redact_student($conn, $row),
            ]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('delPPI error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }

    elseif ($action === 'editPPI') {
        // auth
        $ctx = read_token_context($body);
        $sid = verify_token(bearer_token_or_body($body), $SECRET, $ctx);
        if (!$sid) fail('Invalid token', 401);

        // type
        $type = strtolower(trim((string)($body['type'] ?? '')));
        if ($type !== 'ppim' && $type !== 'ppi_campus') fail('type must be ppim or ppi_campus', 422);

        // pk
        if ($type === 'ppim') {
            $pk = (int)($body['ppi_id'] ?? $body['ppim_id'] ?? $body['id'] ?? 0);
            if ($pk <= 0) fail('ppi_id required', 422);
            $table = 'ppim';
            $pkcol = 'ppim_id';
        } else {
            $pk = (int)($body['pp_campus_id'] ?? $body['ppi_campus_id'] ?? $body['id'] ?? 0);
            if ($pk <= 0) fail('pp_campus_id required', 422);
            $table = 'ppi_campus';
            $pkcol = 'ppi_campus_id';
        }

        // require all fields for full update
        $startYear = (int)($body['start_year'] ?? 0);
        $endYear   = array_key_exists('end_year', $body) && $body['end_year'] !== '' ? (int)$body['end_year'] : null;
        $dept      = trim((string)($body['department'] ?? ''));
        $pos       = trim((string)($body['position'] ?? ''));
        $desc      = trim((string)($body['description'] ?? $body['additionalInfo'] ?? ''));
        $isActive  = isset($body['is_active']) ? (int)((bool)$body['is_active']) : 1;

        if ($startYear < 1900 || $startYear > 2100) fail('start_year invalid', 422);
        if ($endYear !== null && ($endYear < 1900 || $endYear > 2100)) fail('end_year invalid', 422);
        if ($endYear !== null && $endYear < $startYear) fail('end_year < start_year', 422);
        if ($dept === '' || $pos === '') fail('department and position are required', 422);

        // campus must provide university
        $ppiUniId = null;
        if ($type === 'ppi_campus') {
            $ppiUniId = lookup_university_id($conn, $body['university_id'] ?? null, $body['university'] ?? null);
            if (!$ppiUniId) fail('university_id or valid university required', 422);
        }

        $conn->begin_transaction();
        try {
            // ensure ownership
            $chk = $conn->prepare("SELECT 1 FROM {$table} WHERE {$pkcol} = ? AND student_id = ? LIMIT 1");
            $chk->bind_param('ii', $pk, $sid);
            $chk->execute();
            if (!$chk->get_result()->fetch_row()) {
                $conn->rollback();
                fail('ppi record not found', 404);
            }

            if ($type === 'ppim') {
                // update all columns for ppim
                $sql = 'UPDATE ppim
                        SET start_year = ?, end_year = ?, department = ?, position = ?, description = ?, is_active = ?
                        WHERE ppim_id = ? AND student_id = ? LIMIT 1';
                // types: i i s s s i i i  (NULL ok for end_year with 'i')
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('iisssiii', $startYear, $endYear, $dept, $pos, $desc, $isActive, $pk, $sid);
                $stmt->execute();
            } else {
                // update all columns for ppi_campus
                $sql = 'UPDATE ppi_campus
                        SET university_id = ?, start_year = ?, end_year = ?, department = ?, position = ?, description = ?, is_active = ?
                        WHERE ppi_campus_id = ? AND student_id = ? LIMIT 1';
                // types: i i i s s s i i i
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('iiisssiii', $ppiUniId, $startYear, $endYear, $dept, $pos, $desc, $isActive, $pk, $sid);
                $stmt->execute();
            }

            if ($stmt->affected_rows < 0) {
                $conn->rollback();
                fail('Database error', 409);
            }

            $row = get_student_row($conn, $sid);
            $conn->commit();

            ok([
                'updated' => true,
                'type'    => $type,
                'id'      => $pk,
                'student' => redact_student($conn, $row),
            ]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('editPPI error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }

    else {
        fail('Unknown action. Use one of: check, get, edit, add, addPPI, delPPI, editPPI', 400);
    }
}
catch (Throwable $e) {
    error_log('Fatal: '.$e->getMessage());
    fail('Internal server error', 500);
}
finally {
    if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
}