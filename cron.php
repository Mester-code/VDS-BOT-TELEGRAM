<?php
/*
 ╔══════════════════════════════════════════════════════════╗
 ║  موتور پردازش کرون جاب (Cron Job)                       ║
 ║  هر ۲ دقیقه اجرا میشه                                   ║
 ║  اگه سرور کمتر از ۴ دقیقه وقت داشت چکش میکنه            ║
 ║  اگه پول داشت تمدید - اگه نداشت پاک                     ║                                    ║
 ║  اینم مثل بقیه دست کاری نکن :))                         ║
 ╚══════════════════════════════════════════════════════════╝
*/

// اول یه سری تنظیمات - خطا رو نشون نده
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Tehran');

// لاگ فایل برای دیباگ - هر بار که اجرا شد مینویسه
$log_file = __DIR__ . '/cron_log.txt';
file_put_contents($log_file, "\n[" . date('Y-m-d H:i:s') . "] Cron started", FILE_APPEND);

// تنظیمات ربات
$BOT_TOKEN  = "token";
$ADMIN_ID   = 000000; // ایدی عددی ادمین

// دیتابیس
$db_host = 'localhost';
$db_name = '';
$db_user = '';
$db_pass = ''; 

// =====================================================
// ارایه اکانت های هاست وی دی اس
// چون هر سرور تو یه اکانت خاص ساخته شده باید چک کنیم
// =====================================================
$navidi_accounts = array(
    array(
        'name'       => 'ci#1',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "",
        'password'   => "",
        'project_id' => "",
        'status'     => 1,
    ),
    array(
        'name'       => 'HostVDS Account #2',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "", 'password' => "", 'project_id' => "",
        'status' => 0,
    ),
    array(
        'name'       => 'HostVDS Account #3',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "", 'password' => "", 'project_id' => "",
        'status' => 0,
    ),
    array(
        'name'       => 'HostVDS Account #4',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "", 'password' => "", 'project_id' => "",
        'status' => 0,
    ),
    array(
        'name'       => 'HostVDS Account #5',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "", 'password' => "", 'project_id' => "",
        'status' => 0,
    ),
);

// وصل شدن به دیتابیس - اگه ارور داد میریم بیرون
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    file_put_contents($log_file, "\n[ERROR] DB connection failed", FILE_APPEND);
    exit("DB Error"); 
}

// ==========================================
// توابع کمکی - دست نزن :))
// ==========================================

// ارسال پیام تلگرام
function sendTelegramNotice($chat_id, $text) {
    global $BOT_TOKEN, $log_file;
    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POST => true, 
        CURLOPT_POSTFIELDS => ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'], 
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    $res = curl_exec($ch); 
    curl_close($ch);
    file_put_contents($log_file, "\n[MSG] Sent to $chat_id", FILE_APPEND);
    return $res;
}

// درخواست به اوپن استک
function os_request($url, $method = 'GET', $token = null) {
    $ch = curl_init($url);
    $headers = ["Accept: application/json", "Content-Type: application/json"];
    if ($token) $headers[] = "X-Auth-Token: $token";
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => $headers, 
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_HEADER => true, 
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20
    ]);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method == 'DELETE') { 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); 
    }
    $res = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return [
        'body' => json_decode(substr($res, $header_size), true),
        'headers' => substr($res, 0, $header_size)
    ];
}

// گرفتن توکن احراز هویت - بر اساس ایندکس اکانت
function get_auth($account_index = 0) {
    global $navidi_accounts;
    if (!isset($navidi_accounts[$account_index])) $account_index = 0;
    $account = $navidi_accounts[$account_index];
    
    if (empty($account['status']) || empty($account['user_id'])) {
        if ($account_index != 0) return get_auth(0);
        return ['token' => null, 'catalog' => []];
    }
    
    $data = [
        "auth" => [
            "identity" => [
                "methods" => ["password"], 
                "password" => [
                    "user" => [
                        "id" => $account['user_id'], 
                        "password" => $account['password']
                    ]
                ]
            ], 
            "scope" => [
                "project" => ["id" => $account['project_id']]
            ]
        ]
    ];
    
    $ch = curl_init($account['auth_url']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, 
        CURLOPT_POSTFIELDS => json_encode($data), 
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"], 
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_HEADER => true, 
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15
    ]);
    $res = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    preg_match('/X-Subject-Token:\s*([^\s]+)/i', substr($res, 0, $header_size), $matches);
    $body = json_decode(substr($res, $header_size), true);
    return [
        'token' => $matches[1] ?? null, 
        'catalog' => $body['token']['catalog'] ?? []
    ];
}

// پیدا کردن endpoint
function get_endpoint($catalog, $type, $region) {
    foreach ($catalog as $service) {
        if ($service['type'] == $type) {
            foreach ($service['endpoints'] as $ep) { 
                if ($ep['interface'] == 'public' && 
                    (($ep['region'] ?? '') == $region || ($ep['region_id'] ?? '') == $region)) {
                    return $ep['url']; 
                }
            }
        }
    } 
    return null;
}

// ==========================================
// اجرای عملیات اصلی
// کرون جاب ۲ دقیقه یکبار اجرا میشه
// سرورهایی که کمتر از ۴ دقیقه وقت دارن چک میشن
// ==========================================

// گرفتن سرورهایی که تا ۴ دقیقه دیگه منقضی میشن (یا منقضی شدن)
$stmt = $pdo->query("SELECT * FROM servers WHERE status = 'active' AND paid_until <= DATE_ADD(NOW(), INTERVAL 4 MINUTE)");
$servers_to_process = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents($log_file, "\n[INFO] Found " . count($servers_to_process) . " servers to check", FILE_APPEND);

$renewed_count = 0;
$deleted_count = 0;

if (!empty($servers_to_process)) {
    // کش کردن توکن برای هر اکانت - تا هی نخوایم احراز هویت کنیم
    $auth_cache = [];
    
    foreach ($servers_to_process as $srv) {
        $account_index = (int)($srv['hostvds_account_index'] ?? 0);
        $cost_1hour = (int)$srv['hourly_price']; // هزینه یک ساعت کامل
        
        // گرفتن اطلاعات کاربر
        $user = $pdo->query("SELECT balance FROM users WHERE chat_id = {$srv['chat_id']}")->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            file_put_contents($log_file, "\n[WARN] User {$srv['chat_id']} not found for server {$srv['id']}", FILE_APPEND);
            continue;
        }
        
        $user_balance = (int)($user['balance'] ?? 0);
        
        file_put_contents($log_file, "\n[CHECK] Server {$srv['id']} | User {$srv['chat_id']} | Balance: $user_balance | Cost: $cost_1hour | AutoRenew: {$srv['auto_renew']}", FILE_APPEND);
        
        // شرط تمدید: تمدید خودکار فعال + موجودی کافی برای یک ساعت
        if ($srv['auto_renew'] == 1 && $user_balance >= $cost_1hour) {
            
            // کسر هزینه یک ساعت از موجودی
            $pdo->query("UPDATE users SET balance = balance - $cost_1hour WHERE chat_id = {$srv['chat_id']}");
            
            // تمدید زمان انقضا برای یک ساعت آینده
            $pdo->query("UPDATE servers SET paid_until = DATE_ADD(paid_until, INTERVAL 1 HOUR) WHERE id = {$srv['id']}");
            
            // ارسال پیام تمدید موفق به کاربر
            $msg = "✅ <b>تمدید خودکار سرویس</b>\n\n";
            $msg .= "🖥 سرویس: <b>{$srv['server_name']}</b>\n";
            $msg .= "⏱ مدت تمدید: <b>۱ ساعت</b>\n";
            $msg .= "💵 هزینه کسر شده: <b>" . number_format($cost_1hour) . " تومان</b>\n";
            $msg .= "💰 موجودی باقیمانده: <b>" . number_format($user_balance - $cost_1hour) . " تومان</b>\n";
            $msg .= "⏳ زمان جدید انقضا: <code>" . date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($srv['paid_until']))) . "</code>";
            
            sendTelegramNotice($srv['chat_id'], $msg);
            $renewed_count++;
            file_put_contents($log_file, "\n[RENEWED] Server {$srv['id']} renewed successfully", FILE_APPEND);
            
        } else {
            // گرفتن توکن اگه کش نیست
            if (!isset($auth_cache[$account_index])) {
                $auth_cache[$account_index] = get_auth($account_index);
            }
            $auth = $auth_cache[$account_index];
            $token = $auth['token'];
            
            if (!$token) {
                file_put_contents($log_file, "\n[ERROR] Cannot auth for account $account_index", FILE_APPEND);
                continue;
            }
            
            // پیدا کردن endpoint برای دیتاسنتر
            $nova_url = get_endpoint($auth['catalog'], 'compute', $srv['location_api']);
            
            if ($nova_url) {
                // حذف سرور از دیتاسنتر
                $delete_res = os_request($nova_url . "/servers/" . $srv['os_server_id'], 'DELETE', $token);
                file_put_contents($log_file, "\n[DELETE] Sent delete request for server {$srv['id']}", FILE_APPEND);
            } else {
                file_put_contents($log_file, "\n[ERROR] No compute endpoint for server {$srv['id']}", FILE_APPEND);
            }
            
            // علامت زدن سرور به عنوان حذف شده در دیتابیس
            $pdo->query("UPDATE servers SET status = 'deleted' WHERE id = {$srv['id']}");
            
            // تعیین دلیل حذف
            if ($srv['auto_renew'] == 0) {
                $reason = "قابلیت تمدید خودکار غیرفعال بود";
            } else {
                $reason = "موجودی حساب شما برای تمدید یک‌ساعته کافی نبود";
            }
            
            // ارسال پیام حذف به کاربر
            $msg = "❌ <b>سرویس شما حذف شد!</b>\n\n";
            $msg .= "🖥 سرویس: <b>{$srv['server_name']}</b>\n";
            $msg .= "⚠️ دلیل: $reason\n";
            $msg .= "💰 موجودی فعلی: <b>" . number_format($user_balance) . " تومان</b>\n";
            $msg .= "💡 <b>نکته:</b> برای جلوگیری از حذف سرورها، همیشه موجودی کافی داشته باشید.";
            
            sendTelegramNotice($srv['chat_id'], $msg);
            
            // به ادمین هم خبر بده
            if ($srv['chat_id'] != $ADMIN_ID) {
                $admin_msg = "🗑️ <b>سرور حذف شد</b>\n\n";
                $admin_msg .= "👤 کاربر: <code>{$srv['chat_id']}</code>\n";
                $admin_msg .= "🖥 سرور: <code>{$srv['server_name']}</code>\n";
                $admin_msg .= "⚠️ دلیل: $reason\n";
                $admin_msg .= "💰 موجودی: " . number_format($user_balance) . " ت";
                sendTelegramNotice($ADMIN_ID, $admin_msg);
            }
            
            $deleted_count++;
            file_put_contents($log_file, "\n[DELETED] Server {$srv['id']} deleted - Reason: $reason", FILE_APPEND);
        }
    }
}

// نتیجه نهایی رو تو لاگ بنویس
$summary = "\n[SUMMARY] Processed: " . count($servers_to_process) . " | Renewed: $renewed_count | Deleted: $deleted_count";
file_put_contents($log_file, $summary, FILE_APPEND);

// خروجی برای cron
echo "OK - Processed: " . count($servers_to_process) . " | Renewed: $renewed_count | Deleted: $deleted_count\n";
?>
