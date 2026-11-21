
<?php
/*
 * UniVibe single PHP backend.
 * Supports local XAMPP and Railway MySQL.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
| Railway environment variables with XAMPP fallback.
*/

$DB_HOST = getenv('MYSQLHOST') ?: 'localhost';
$DB_PORT = getenv('MYSQLPORT') ?: '3306';
$DB_NAME = getenv('MYSQLDATABASE') ?: 'univibe';
$DB_USER = getenv('MYSQLUSER') ?: 'root';
$DB_PASS = getenv('MYSQLPASSWORD') ?: '';

/*
|--------------------------------------------------------------------------
| RESPONSE FUNCTION
|--------------------------------------------------------------------------
*/

function respond(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/*
|--------------------------------------------------------------------------
| INPUT DATA
|--------------------------------------------------------------------------
*/

function input_data(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

function require_login(): array {
    if (empty($_SESSION['user'])) {
        respond(['success'=>false,'message'=>'Please log in to continue.'], 401);
    }
    return $_SESSION['user'];
}

/*
|--------------------------------------------------------------------------
| ADMIN CHECK
|--------------------------------------------------------------------------
*/

function require_admin(): array {
    $u = require_login();

    if ($u['role'] !== 'admin') {
        respond(['success'=>false,'message'=>'Administrator access required.'], 403);
    }

    return $u;
}

/*
|--------------------------------------------------------------------------
| SEATS LEFT
|--------------------------------------------------------------------------
*/

function seats_left(PDO $pdo, int $eventId, int $capacity): int {
    $q = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE event_id=?');
    $q->execute([$eventId]);
    return max(0, $capacity-(int)$q->fetchColumn());
}

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

try {

    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Create default administrator once.
    // Change this password after first login.

    $adminCount = (int)$pdo->query(
        "SELECT COUNT(*) FROM users WHERE role='admin'"
    )->fetchColumn();

    if ($adminCount === 0) {
        $hash = password_hash('Admin@12345', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "INSERT INTO users(full_name,email,password_hash,role)
             VALUES(?,?,?,'admin')"
        );

        $stmt->execute([
            'UniVibe Administrator',
            'admin@univibe.edu',
            $hash
        ]);
    }

} catch (Throwable $e) {

    // Write the actual database error to Railway deployment logs.
    error_log("UNIVIBE DATABASE ERROR: " . $e->getMessage());

    respond([
        'success'=>false,
        'message'=>'Database connection failed. Check Railway logs.'
    ], 500);
}

/*
|--------------------------------------------------------------------------
| API ACTION
|--------------------------------------------------------------------------
*/

$action = $_GET['action'] ?? '';
$data = input_data();

try {

    switch ($action) {

        case 'events':

            $rows = $pdo->query(
                "SELECT e.*,
                (e.capacity-(SELECT COUNT(*) FROM registrations r
                WHERE r.event_id=e.id)) AS seats_left
                FROM events e
                WHERE e.event_date >= CURDATE()
                ORDER BY e.event_date,e.start_time"
            )->fetchAll();

            respond(['success'=>true,'events'=>$rows]);


        case 'event':

            $id = (int)($data['id'] ?? 0);

            $q = $pdo->prepare(
                "SELECT e.*,
                (e.capacity-(SELECT COUNT(*) FROM registrations r
                WHERE r.event_id=e.id)) AS seats_left
                FROM events e WHERE e.id=?"
            );

            $q->execute([$id]);
            $event = $q->fetch();

            if (!$event) {
                respond(['success'=>false,'message'=>'Event not found.'],404);
            }

            respond(['success'=>true,'event'=>$event]);


        case 'register':

            $name = trim($data['full_name'] ?? '');
            $email = strtolower(trim($data['email'] ?? ''));
            $password = $data['password'] ?? '';
            $uni = trim($data['university_id'] ?? '');

            if (
                $name === '' ||
                !filter_var($email,FILTER_VALIDATE_EMAIL) ||
                strlen($password)<8
            ) {
                respond([
                    'success'=>false,
                    'message'=>'Enter a name, valid email and password with at least 8 characters.'
                ],422);
            }

            $q = $pdo->prepare(
                "INSERT INTO users(full_name,university_id,email,password_hash,role)
                 VALUES(?,?,?,?, 'student')"
            );

            try {
                $q->execute([
                    $name,
                    $uni,
                    $email,
                    password_hash($password,PASSWORD_DEFAULT)
                ]);
            } catch (PDOException $e) {
                respond(['success'=>false,'message'=>'This email is already registered.'],409);
            }

            respond(['success'=>true]);


        case 'login':

            $email = strtolower(trim($data['email'] ?? ''));
            $password = $data['password'] ?? '';

            $q = $pdo->prepare(
                'SELECT id,full_name,email,password_hash,role
                 FROM users WHERE email=?'
            );

            $q->execute([$email]);
            $u = $q->fetch();

            if (!$u || !password_verify($password,$u['password_hash'])) {
                respond(['success'=>false,'message'=>'Incorrect email or password.'],401);
            }

            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id'=>(int)$u['id'],
                'full_name'=>$u['full_name'],
                'email'=>$u['email'],
                'role'=>$u['role']
            ];

            respond(['success'=>true,'user'=>$_SESSION['user']]);


        case 'profile':

            $u = require_login();

            $q = $pdo->prepare(
                'SELECT id,full_name,university_id,email,role,created_at
                 FROM users WHERE id=?'
            );

            $q->execute([$u['id']]);

            respond(['success'=>true,'user'=>$q->fetch()]);


        case 'update_profile':

            $u = require_login();

            $name = trim($data['full_name'] ?? '');
            $uni = trim($data['university_id'] ?? '');

            if ($name === '') {
                respond(['success'=>false,'message'=>'Please enter your full name.'],422);
            }

            $q = $pdo->prepare(
                'UPDATE users SET full_name=?, university_id=? WHERE id=?'
            );

            $q->execute([$name,$uni,$u['id']]);

            $_SESSION['user']['full_name'] = $name;

            respond(['success'=>true]);


        case 'logout':

            $_SESSION = [];
            session_destroy();

            respond(['success'=>true]);


        case 'register_event':

            $u = require_login();
            $eventId = (int)($data['event_id'] ?? 0);

            $pdo->beginTransaction();

            $q = $pdo->prepare(
                'SELECT id,capacity,registration_deadline,event_date
                 FROM events WHERE id=? FOR UPDATE'
            );

            $q->execute([$eventId]);
            $e = $q->fetch();

            if (!$e) {
                $pdo->rollBack();
                respond(['success'=>false,'message'=>'Event not found.'],404);
            }

            if (
                $e['registration_deadline'] < date('Y-m-d') ||
                $e['event_date'] < date('Y-m-d')
            ) {
                $pdo->rollBack();
                respond(['success'=>false,'message'=>'Registration for this event has closed.'],422);
            }

            $countQ = $pdo->prepare(
                'SELECT COUNT(*) FROM registrations WHERE event_id=?'
            );

            $countQ->execute([$eventId]);

            if ((int)$countQ->fetchColumn() >= (int)$e['capacity']) {
                $pdo->rollBack();
                respond(['success'=>false,'message'=>'Sorry, this event is full.'],422);
            }

            try {
                $q = $pdo->prepare(
                    'INSERT INTO registrations(user_id,event_id) VALUES(?,?)'
                );

                $q->execute([$u['id'],$eventId]);

            } catch (PDOException $ex) {
                $pdo->rollBack();
                respond(['success'=>false,'message'=>'You are already registered for this event.'],409);
            }

            $pdo->commit();

            respond(['success'=>true]);


        case 'student_dashboard':

            $u = require_login();

            if ($u['role'] !== 'student') {
                respond(['success'=>false,'message'=>'Student account required.'],403);
            }

            $q = $pdo->prepare(
                "SELECT e.id,e.title,e.event_date,e.venue,r.attendance
                 FROM registrations r
                 JOIN events e ON e.id=r.event_id
                 WHERE r.user_id=?
                 ORDER BY e.event_date"
            );

            $q->execute([$u['id']]);
            $regs = $q->fetchAll();

            $ann = $pdo->query(
                'SELECT title,message,created_at
                 FROM announcements ORDER BY created_at DESC LIMIT 8'
            )->fetchAll();

            $att = count(array_filter(
                $regs,
                fn($x)=>(int)$x['attendance']===1
            ));

            $up = count(array_filter(
                $regs,
                fn($x)=>$x['event_date']>=date('Y-m-d')
            ));

            respond([
                'success'=>true,
                'user'=>$u,
                'registrations'=>$regs,
                'announcements'=>$ann,
                'stats'=>[
                    'registered'=>count($regs),
                    'attended'=>$att,
                    'upcoming'=>$up,
                    'announcements'=>count($ann)
                ]
            ]);


        case 'suggest_event':

            $u = require_login();

            $required = ['title','category','event_date','venue','description'];

            foreach ($required as $f) {
                if (trim((string)($data[$f] ?? ''))==='') {
                    respond([
                        'success'=>false,
                        'message'=>"Please complete the $f field."
                    ],422);
                }
            }

            $q = $pdo->prepare(
                'INSERT INTO event_suggestions
                (user_id,title,category,event_date,venue,description)
                VALUES(?,?,?,?,?,?)'
            );

            $q->execute([
                $u['id'],
                trim($data['title']),
                trim($data['category']),
                $data['event_date'],
                trim($data['venue']),
                trim($data['description'])
            ]);

            respond([
                'success'=>true,
                'message'=>'Your event suggestion was sent to the UniVibe team for review.'
            ]);


        case 'admin_dashboard':

            require_admin();

            $events = $pdo->query(
                "SELECT e.*,
                (SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) registrations,
                (e.capacity-(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id)) seats_left
                FROM events e ORDER BY e.event_date DESC"
            )->fetchAll();

            $list = $pdo->query(
                "SELECT r.id,r.attendance,r.registered_at,u.full_name,u.email,e.title
                 FROM registrations r
                 JOIN users u ON u.id=r.user_id
                 JOIN events e ON e.id=r.event_id
                 ORDER BY r.registered_at DESC"
            )->fetchAll();

            $stats = $pdo->query(
                "SELECT
                (SELECT COUNT(*) FROM events) events,
                (SELECT COUNT(*) FROM registrations) registrations,
                (SELECT COUNT(*) FROM registrations WHERE attendance=1) attendance,
                (SELECT COALESCE(SUM(capacity),0) FROM events) -
                (SELECT COUNT(*) FROM registrations) seats_left"
            )->fetch();

            $suggestions = $pdo->query(
                "SELECT s.*,u.full_name,u.email
                 FROM event_suggestions s
                 JOIN users u ON u.id=s.user_id
                 WHERE s.status='pending'
                 ORDER BY s.created_at DESC"
            )->fetchAll();

            respond([
                'success'=>true,
                'events'=>$events,
                'registrations_list'=>$list,
                'stats'=>$stats,
                'suggestions'=>$suggestions
            ]);


        case 'save_event':

            require_admin();

            $id = (int)($data['id'] ?? 0);

            $fields = [
                'title','category','event_date','start_time','end_time',
                'venue','registration_deadline','capacity','description','image_url'
            ];

            foreach ([
                'title','category','event_date','start_time','end_time',
                'venue','registration_deadline','description'
            ] as $f) {
                if (trim((string)($data[$f] ?? ''))==='') {
                    respond([
                        'success'=>false,
                        'message'=>"Please complete the $f field."
                    ],422);
                }
            }

            $vals = [];

            foreach ($fields as $f) {
                $vals[] = $data[$f] ?? '';
            }

            if ((int)$data['capacity']<1) {
                respond(['success'=>false,'message'=>'Capacity must be at least 1.'],422);
            }

            if ($id>0) {
                $vals[] = $id;

                $q = $pdo->prepare(
                    'UPDATE events SET title=?,category=?,event_date=?,
                    start_time=?,end_time=?,venue=?,registration_deadline=?,
                    capacity=?,description=?,image_url=? WHERE id=?'
                );

                $q->execute($vals);

            } else {
                $q = $pdo->prepare(
                    'INSERT INTO events(title,category,event_date,start_time,
                    end_time,venue,registration_deadline,capacity,description,image_url)
                    VALUES(?,?,?,?,?,?,?,?,?,?)'
                );

                $q->execute($vals);
            }

            respond(['success'=>true]);


        case 'review_suggestion':

            require_admin();

            $id = (int)($data['id'] ?? 0);
            $decision = $data['decision'] ?? '';

            $q = $pdo->prepare(
                "SELECT * FROM event_suggestions
                 WHERE id=? AND status='pending'"
            );

            $q->execute([$id]);
            $suggestion = $q->fetch();

            if (!$suggestion) {
                respond([
                    'success'=>false,
                    'message'=>'Suggestion not found or already reviewed.'
                ],404);
            }

            if ($decision==='approve') {

                $date = $suggestion['event_date'];
                $deadline = $date;

                $q = $pdo->prepare(
                    'INSERT INTO events(title,category,event_date,start_time,
                    end_time,venue,registration_deadline,capacity,description,image_url)
                    VALUES(?,?,?,?,?,?,?,?,?,?)'
                );

                $q->execute([
                    $suggestion['title'],
                    $suggestion['category'],
                    $date,
                    '10:00:00',
                    '12:00:00',
                    $suggestion['venue'],
                    $deadline,
                    100,
                    $suggestion['description'],
                    'assets/images/event-12.svg'
                ]);

                $status = 'approved';

            } else {
                $status = 'rejected';
            }

            $q = $pdo->prepare(
                'UPDATE event_suggestions SET status=? WHERE id=?'
            );

            $q->execute([$status,$id]);

            respond(['success'=>true]);


        case 'delete_event':

            require_admin();

            $q = $pdo->prepare('DELETE FROM events WHERE id=?');
            $q->execute([(int)($data['id'] ?? 0)]);

            respond(['success'=>true]);


        case 'attendance':

            require_admin();

            $q = $pdo->prepare(
                'UPDATE registrations SET attendance=? WHERE id=?'
            );

            $q->execute([
                (int)!!($data['attendance'] ?? 0),
                (int)($data['registration_id'] ?? 0)
            ]);

            respond(['success'=>true]);


        case 'announcement':

            require_admin();

            $title = trim($data['title'] ?? '');
            $message = trim($data['message'] ?? '');

            if ($title==='' || $message==='') {
                respond([
                    'success'=>false,
                    'message'=>'Enter an announcement title and message.'
                ],422);
            }

            $q = $pdo->prepare(
                'INSERT INTO announcements(title,message) VALUES(?,?)'
            );

            $q->execute([$title,$message]);

            respond(['success'=>true]);


        case 'export_csv':

            require_admin();

            header_remove('Content-Type');

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="univibe-participants.csv"');

            $out = fopen('php://output','w');

            fputcsv($out,[
                'Student Name',
                'Email',
                'Event',
                'Registered At',
                'Attendance'
            ]);

            $rows = $pdo->query(
                "SELECT u.full_name,u.email,e.title,r.registered_at,r.attendance
                 FROM registrations r
                 JOIN users u ON u.id=r.user_id
                 JOIN events e ON e.id=r.event_id
                 ORDER BY e.event_date"
            )->fetchAll();

            foreach ($rows as $r) {
                fputcsv($out,[
                    $r['full_name'],
                    $r['email'],
                    $r['title'],
                    $r['registered_at'],
                    $r['attendance'] ? 'Present' : 'Not marked'
                ]);
            }

            fclose($out);
            exit;


        default:

            respond([
                'success'=>false,
                'message'=>'Unknown API action.'
            ],404);
    }

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    respond([
        'success'=>false,
        'message'=>'Server error. Please check your input and database setup.'
    ],500);
}

?>
