<?php
/*
function select_full_match(mysqli $conn, array $in): ?array {
    //not finished yet
    $uid = lookup_university_id($conn, $in['university_id'] ?? null, $in['university'] ?? null);
    $dob = $in['dob'] ?? '';
    $pass = $in['passport'] ?? '';
    $phone = $in['phone_number'] ?? '';
    $name = $in['fullname'] ?? '';

    // 1) dob + passport + phone + university
    if ($dob !== '' && $pass !== '' && $phone !== '' && $uid) {
        $stmt = $conn->prepare('SELECT * FROM student WHERE dob=? AND passport=? AND phone_number=? AND university_id=? LIMIT 1');
        $stmt->bind_param('sssi', $dob, $pass, $phone, $uid);
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

    /// 3) Soft match: fullname + dob + at least 2 of {passport, phone, university}
    if ($name === '' || $dob === '') return null;

    $stmt = $conn->prepare('SELECT * FROM student WHERE LOWER(fullname)=? AND dob=?');
    $stmt->bind_param('ss', $name, $dob);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $score = 0;
        if ($pass  !== '' && $row['passport'] === $pass) $score++;
        if ($phone !== '' && $row['phone_number'] === $phone) $score++;
        if ($uid && (int)$row['university_id'] === $uid) $score++;
        if ($score >= 2) return $row;
    }
    return null;
}








elseif ($action === 'create'){ //still implementing!
        // normalize
        $in = [
            'fullname'      => norm_fullname($body['fullname'] ?? ''),
            'university_id' => isset($body['university_id']) ? (string)$body['university_id'] : null,
            'university'    => trim((string)($body['university'] ?? '')),
            'dob'           => norm_dob($body['dob'] ?? ''),        // YYYY-MM-DD
            'email'         => norm_email($body['email'] ?? ''),
            'passport'      => norm_passport($body['passport'] ?? ''),
            'phone_number'  => norm_phone($body['phone_number'] ?? ''),
            'postcode_id'   => trim((string)($body['postcode_id'] ?? '')),
            'address'       => trim((string)($body['address'] ?? '')),
            'expected_graduate' => norm_dob($body['expected_graduate'] ?? ''),
            'degree'            => trim((string)$body['degree'] ?? ''),
            'level_of_qualification_id' => (int)$body['level_of_qualification_id'] ?? '',
            'status_id'     => 1, //1(active)(default) 2(graduated) 3(suspended/dropout) 
            'is_active'.    => 1, //if is_active = 0 means flagged/scam

        ];
        //if one of above empty -> fail
        if ($in['fullname']==='' || $in['university_id']==='') fail('data incomplete', 422);
        $ctx = read_token_context($body);

        $conn->begin_transaction();
        try {
            //to prevent student spam the "create" API, the API will skip creation if exist(fullname, dob) and exist three of(university_id or phone_number or passport or email)
            //throw only fullname, dob, university_id, phone_number, passport, email
            $row = select_full_match($conn, $in);
            if ($row) {
                $token = make_token((int)$row['student_id'], 3600, $SECRET, $ctx);
                $conn->commit();
                ok(['mode'=>'existing','token'=>$token,'student'=>redact_student($conn, $row)]);
            }

            // Save full record
            //fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree, level_of_qualification_id, status_id, is_active
            $uid = lookup_university_id($conn, $in['university_id'], $in['university']);
            if ($uid === null) {
                fail('university invalid', 422);
            } else {
                $stmt = $conn->prepare('INSERT INTO student (fullname, university_id, dob, email, passport, phone_number, postcode_id, address, expected_graduate, degree, level_of_qualification_id, status_id, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->bind_param('sissssssssiii', $in['fullname'], $in['dob'], $in['passport'], $in['phone_number'], $uid);
            }
            $stmt->execute();
            $sid = (int)$conn->insert_id;

            $row = get_student_row($conn, $sid);
            if (!$row) { throw new Exception('Post-insert fetch failed'); }

            $token = make_token($sid, 3600, $SECRET, $ctx);
            $conn->commit();
            ok(['mode'=>'created','token'=>$token,'student'=>redact_student($conn, $row)]);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('check action error: '.$e->getMessage());
            fail('Database error', 409);
        }
    }
*/
?>