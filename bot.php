<?php
/*
 ╔══════════════════════════════════════════════════════════╗
 ║  برادر گرامی ناموصا با منبع پخش کن                      ║
 ║  دانلود نسخه های جدید در @net_visit                     ║
 ║  نویسنده @mester_code                                   ║
 ║  ویرایش توسط: Navidi                                    ║
 ║  هر کی اینو دست کاری کنه حقش حرامه :)))                 ║
 ╚══════════════════════════════════════════════════════════╝
*/

// خب اول یه سری متغیر اساسی
@ini_set('display_errors', 0); // تا خطا رو نشون نده
date_default_timezone_set('Asia/Tehran');

// تنظیمات ربات - توکن خودتو بزن
$BOT_TOKEN  = "توکن"; // از بات فادر بگیر
$ADMIN_ID   = ادمین; // ایدی عددی خودت

// دیتابیس - هاست ایران باشه بهتره
$db_host = 'localhost';
$db_name = 'name';
$db_user = 'user';
$db_pass = 'pass'; // 

// =====================================================
// ارایه اکانت های هاست وی دی اس
// چون هر اکانت فقط 10 تا سرور میشه ساخت باید چنتا بذاری
// من اینجا 5 تا گذاشتم ولی می تونی بیشتر کنی
// =====================================================
$navidi_accounts = array(
    array(
        'name'       => 'ci#1',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "",
        'password'   => "",
        'project_id' => "",
        'max_servers'=> 10,
        'status'     => 1,
    ),
    array(
        'name'       => 'HostVDS Account #2',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "",
        'password' => "",
        'project_id' => "",
        'max_servers'=> 10,
        'status' => 0,
    ),
    array(
        'name'       => 'HostVDS Account #3',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "", 'password' => "", 'project_id' => "",
        'max_servers'=> 10, 'status' => 0
    ),
    array(
        'name'       => 'HostVDS Account #4',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "", 'password' => "", 'project_id' => "",
        'max_servers'=> 10, 'status' => 0
    ),
    array(
        'name'       => 'HostVDS Account #5',
        'auth_url'   => "https://os-api.hostvds.com/identity/v3/auth/tokens",
        'user_id'    => "",
        'password'   => "",
        'project_id' => "",
        'max_servers'=> 10,
        'status'     => 0
    ),
);

// یه تابع برای تولید رمز قوی 
function generateStrongPassword($length = 14) {
    $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lower = 'abcdefghjkmnpqrstuvwxyz';
    $numbers = '23456789';
    $special = '!@#$%&*?';
    
    $password = '';
    $password .= $upper[random_int(0, strlen($upper) - 1)];
    $password .= $lower[random_int(0, strlen($lower) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $special[random_int(0, strlen($special) - 1)];
    
    $all = $upper . $lower . $numbers . $special;
    for ($i = strlen($password); $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    
    return str_shuffle($password);
}

// وصل شدن به دیتابیس - اگه ارور داد یعنی تنظیماتت اشتباهه
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ساخت جدول تراکنش اگه نیست
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (id INT AUTO_INCREMENT PRIMARY KEY, chat_id BIGINT, amount INT, description VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    
    // جدول سوالات متداول
    $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (id INT AUTO_INCREMENT PRIMARY KEY, question TEXT, answer TEXT)");
    
    // اضافه کردن فیلدها - اگه هست ارور میده ولی کاری نداریم
    try { $pdo->exec("ALTER TABLE servers ADD COLUMN ipv6_address VARCHAR(100) NULL AFTER ip_address"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE servers ADD COLUMN auto_renew TINYINT(1) DEFAULT 1"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE servers ADD COLUMN hostvds_account_index INT DEFAULT 0 AFTER location_api"); } catch(Exception $e) {}
    
} catch (PDOException $e) { 
    // اگه وصل نشد میره پی کارش
    exit("DB Error - check your config bro"); 
}

// گرفتن آپدیت از تلگرام
$content = file_get_contents("php://input");
$update = json_decode($content, true);
if (!$update) exit;

// متغیرهای اساسی
$chat_id = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null;
$message_id = $update['message']['message_id'] ?? $update['callback_query']['message']['message_id'] ?? null;
$text = trim($update['message']['text'] ?? '');
$callback_data = $update['callback_query']['data'] ?? null;

if (!$chat_id) exit;

// چک کردن وضعیت ربات
$bot_status = $pdo->query("SELECT value FROM settings WHERE `key` = 'bot_status'")->fetchColumn() ?: 'on';
if ($bot_status == 'off' && $chat_id != $ADMIN_ID) {
    $post = ['chat_id' => $chat_id, 'text' => "🛠 <b>ربات در حال حاضر جهت ارتقای دوره‌ای در دست تعمیر است.</b>\n⏱ لطفاً ساعاتی دیگر مجدداً اقدام فرمایید.", 'parse_mode' => 'HTML'];
    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false]);
    curl_exec($ch); curl_close($ch); exit;
}

// اگه کاربر جدید بود ثبتش کن
$stmt = $pdo->prepare("INSERT IGNORE INTO users (chat_id, balance, state) VALUES (?, 0, 'none')");
$stmt->execute([$chat_id]);
$user = $pdo->query("SELECT * FROM users WHERE chat_id = $chat_id")->fetch(PDO::FETCH_ASSOC);

// ==========================================
// توابع هسته سیستم - اینا رو دست نزن
// ==========================================
function sendMessage($text, $keyboard = null) {
    global $BOT_TOKEN, $chat_id;
    $post = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if ($keyboard) $post['reply_markup'] = json_encode($keyboard);
    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false]);
    $res = curl_exec($ch); curl_close($ch); return json_decode($res, true);
}

function sendDirectMessage($to_id, $text, $keyboard = null) {
    global $BOT_TOKEN;
    $post = ['chat_id' => $to_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if ($keyboard) $post['reply_markup'] = json_encode($keyboard);
    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false]);
    $res = curl_exec($ch); curl_close($ch);
}

function sendPhoto($to_id, $file_id, $caption, $keyboard = null) {
    global $BOT_TOKEN;
    $post = ['chat_id' => $to_id, 'photo' => $file_id, 'caption' => $caption, 'parse_mode' => 'HTML'];
    if ($keyboard) $post['reply_markup'] = json_encode($keyboard);
    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/sendPhoto");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false]);
    $res = curl_exec($ch); curl_close($ch); return json_decode($res, true);
}

function editMessageText($message_id, $text, $keyboard = null) {
    global $BOT_TOKEN, $chat_id;
    $post = ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if ($keyboard) $post['reply_markup'] = json_encode($keyboard);
    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/editMessageText");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false]);
    $res = curl_exec($ch); curl_close($ch); return json_decode($res, true);
}

function editMessageCaption($message_id, $text, $keyboard = null) {
    global $BOT_TOKEN, $chat_id;
    $post = ['chat_id' => $chat_id, 'message_id' => $message_id, 'caption' => $text, 'parse_mode' => 'HTML'];
    if ($keyboard) $post['reply_markup'] = json_encode($keyboard);
    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/editMessageCaption");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false]);
    $res = curl_exec($ch); curl_close($ch); return json_decode($res, true);
}

function updateState($state, $temp_data = []) {
    global $pdo, $chat_id, $user;
    $new_data = array_merge(json_decode($user['temp_data'] ?? '{}', true) ?: [], $temp_data);
    $stmt = $pdo->prepare("UPDATE users SET state = ?, temp_data = ? WHERE chat_id = ?");
    $stmt->execute([$state, json_encode($new_data), $chat_id]);
}

function logTransaction($u_id, $amount, $desc) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO transactions (chat_id, amount, description) VALUES (?, ?, ?)");
    $stmt->execute([$u_id, $amount, $desc]);
}

// تابع درخواست به اوپن استک - این خیلی مهمه
function os_request($url, $method = 'GET', $token = null, $post_data = null) {
    $ch = curl_init($url);
    $headers = ["Accept: application/json", "Content-Type: application/json"];
    if ($token) $headers[] = "X-Auth-Token: $token";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($post_data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    } elseif ($method == 'DELETE') { curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); }
    curl_setopt($ch, CURLOPT_HEADER, true);
    $res = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['body' => json_decode(substr($res, $header_size), true), 'headers' => substr($res, 0, $header_size)];
}

// گرفتن توکن احراز هویت
function get_auth($account_index = 0) {
    global $navidi_accounts; // اینجا از navidi استفاده میکنیم
    if (!isset($navidi_accounts[$account_index])) $account_index = 0;
    $account = $navidi_accounts[$account_index];
    if (empty($account['status']) || empty($account['user_id'])) {
        if ($account_index != 0) return get_auth(0);
        return ['token' => null, 'catalog' => []];
    }
    $data = [
        "auth" => [
            "identity" => ["methods" => ["password"], "password" => ["user" => ["id" => $account['user_id'], "password" => $account['password']]]],
            "scope" => ["project" => ["id" => $account['project_id']]]
        ]
    ];
    $res = os_request($account['auth_url'], 'POST', null, $data);
    preg_match('/X-Subject-Token:\s*([^\s]+)/i', $res['headers'], $matches);
    return ['token' => $matches[1] ?? null, 'catalog' => $res['body']['token']['catalog'] ?? []];
}

// پیدا کردن بهترین اکانت برای ساخت سرور
function get_best_account($region = null) {
    global $navidi_accounts, $pdo;
    $best_index = -1; 
    $min_servers = PHP_INT_MAX;
    foreach ($navidi_accounts as $index => $account) {
        if (empty($account['status']) || empty($account['user_id']) || empty($account['password'])) continue;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM servers WHERE hostvds_account_index = ? AND status = 'active'");
        $stmt->execute([$index]);
        $active_count = (int)$stmt->fetchColumn();
        $max = $account['max_servers'] ?? 10;
        if ($active_count >= $max) continue;
        if ($active_count < $min_servers) { $min_servers = $active_count; $best_index = $index; }
    }
    return $best_index;
}

function count_servers_for_account($account_index) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM servers WHERE hostvds_account_index = ? AND status = 'active'");
    $stmt->execute([$account_index]);
    return (int)$stmt->fetchColumn();
}

function get_endpoint($catalog, $type, $region) {
    foreach ($catalog as $service) {
        if ($service['type'] == $type) {
            foreach ($service['endpoints'] as $ep) {
                if ($ep['interface'] == 'public' && (($ep['region'] ?? '') == $region || ($ep['region_id'] ?? '') == $region)) return $ep['url'];
            }
        }
    }
    return null;
}

// تغییر رمز سرور با API 
function changePasswordViaAPI($server_os_id, $account_index, $region, $new_password) {
    $auth = get_auth($account_index);
    $token = $auth['token'];
    if (!$token) return ['success' => false, 'error' => 'Authentication failed'];
    
    $nova_url = get_endpoint($auth['catalog'], 'compute', $region);
    if (!$nova_url) return ['success' => false, 'error' => 'Compute endpoint not found'];
    
    $post_data = ["changePassword" => ["adminPass" => $new_password]];
    $result = os_request($nova_url . "/servers/" . $server_os_id . "/action", 'POST', $token, $post_data);
    
    if (isset($result['body']['error'])) {
        return ['success' => false, 'error' => $result['body']['error']['message'] ?? 'Unknown error'];
    }
    
    return ['success' => true];
}

// کیبوردهای اصلی ربات
$user_kb = ['keyboard' => [
    [['text' => '🛒 ساخت سرور جدید']],
    [['text' => '🖥 سرویس‌های من'], ['text' => '💰 کیف پول']],
    [['text' => '❓ سوالات متداول']]
], 'resize_keyboard' => true];

$admin_kb = ['keyboard' => [
    [['text' => '🛒 ساخت سرور جدید']],
    [['text' => '🖥 سرویس‌های من'], ['text' => '💰 کیف پول']],
    [['text' => '❓ سوالات متداول'], ['text' => '⚙️ پنل مدیریت (مخفی)']]
], 'resize_keyboard' => true];

$kb = ($chat_id == $ADMIN_ID) ? $admin_kb : $user_kb;

// کامند شروع
if (strpos($text, '/start') === 0) {
    updateState('none', []);
    sendMessage("به پلتفرم مدیریت فضای ابری هوشمند خوش آمدید. گزینه مورد نظر را انتخاب کنید:", $kb); exit;
}

// دکمه لغو
if ($text == '❌ لغو') {
    updateState('none', []);
    sendMessage("❌ عملیات جاری لغو شد. به منوی اصلی بازگشتید:", $kb); exit;
}

// منوی کیف پول
if ($text == '💰 کیف پول') {
    $txs = $pdo->query("SELECT * FROM transactions WHERE chat_id = $chat_id ORDER BY id DESC LIMIT 10")->fetchAll();
    $tx_text = "📜 <b>۱۰ تراکنش اخیر شما:</b>\n\n";
    if (empty($txs)) {
        $tx_text .= "تراکنشی یافت نشد.\n";
    } else {
        foreach($txs as $tx) {
            $icon = $tx['amount'] > 0 ? '🟢' : '🔴';
            $sign = $tx['amount'] > 0 ? '+' : '';
            $date = date('Y/m/d H:i', strtotime($tx['created_at']));
            $tx_text .= "$icon <b>{$sign}" . number_format($tx['amount']) . " ت</b> | {$tx['description']}\n▫️ <i>$date</i>\n";
        }
    }

    $ikb = ['inline_keyboard' => [
        [['text' => '💳 افزایش موجودی (کارت به کارت)', 'callback_data' => 'user_deposit_card']],
        [['text' => '💸 انتقال موجودی به کاربر دیگر', 'callback_data' => 'user_transfer_funds']]
    ]];
    
    sendMessage("💳 <b>وضعیت کیف پول شما:</b>\n\n💰 اعتبار فعلی حساب: <code>" . number_format($user['balance']) . "</code> تومان\n\n$tx_text", $ikb); exit;
}

// سوالات متداول
if ($text == '❓ سوالات متداول') {
    $faqs = $pdo->query("SELECT * FROM faqs ORDER BY id DESC")->fetchAll();
    if(empty($faqs)) { sendMessage("❓ در حال حاضر سوالی ثبت نشده است."); exit; }
    $msg = "❓ <b>سوالات متداول (FAQ):</b>\n\n";
    foreach($faqs as $i => $f) {
        $msg .= "<b>" . ($i+1) . ". {$f['question']}</b>\n💬 پاسخ: {$f['answer']}\n〰️〰️〰️〰️〰️〰️\n";
    }
    sendMessage($msg); exit;
}

// پنل مدیریت - فقط ادمین ببینه
if ($text == '⚙️ پنل مدیریت (مخفی)' && $chat_id == $ADMIN_ID) {
    $status_text = $bot_status == 'on' ? "🟢 ربات روشن است" : "🔴 ربات خاموش است";
    $accounts_status = "\n\n📊 <b>وضعیت استخر اکانت‌های HostVDS:</b>\n";
    foreach ($navidi_accounts as $index => $acc) { // navidi_accounts
        if (empty($acc['user_id'])) continue;
        $count = count_servers_for_account($index);
        $max = $acc['max_servers'] ?? 10;
        $st = empty($acc['status']) ? "🔴 غیرفعال" : "🟢 فعال";
        $accounts_status .= "▫️ <b>{$acc['name']}</b>: $count/$max سرور - $st\n";
    }
    $ikb = ['inline_keyboard' => [
        [['text' => $status_text, 'callback_data' => 'admin_toggle_bot']],
        [['text' => '📊 آمار سیستم', 'callback_data' => 'admin_stats'], ['text' => '📢 پیام همگانی', 'callback_data' => 'admin_broadcast']],
        [['text' => '❓ مدیریت FAQ', 'callback_data' => 'admin_manage_faq']],
        [['text' => '🌐 مدیریت لوکیشن‌ها', 'callback_data' => 'admin_manage_locs']],
        [['text' => '⚙️ مدیریت پلن‌ها', 'callback_data' => 'admin_manage_plans']],
        [['text' => '💳 مدیریت مالی کاربران', 'callback_data' => 'admin_wallet_manage']],
        [['text' => '💳 تنظیم شماره کارت بانکی', 'callback_data' => 'admin_set_card']],
        [['text' => '🔐 وضعیت اکانت‌های HostVDS', 'callback_data' => 'admin_accounts_status']]
    ]];
    sendMessage("👨‍💻 <b>پنل ارشد مدیریت سراسری سیستم:</b>$accounts_status", $ikb); exit;
}

// ==========================================
// هندل استیت های متنی - اینجاهاش سخته
// ==========================================

// تغییر رمز سرور
if ($user['state'] == 'wait_new_password') {
    $temp = json_decode($user['temp_data'], true);
    $srv_id = $temp['change_pw_srv_id'] ?? null;
    
    if (!$srv_id) { sendMessage("❌ خطا: سرور یافت نشد."); updateState('none', []); exit; }
    
    $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id AND status = 'active'")->fetch();
    if (!$srv) { sendMessage("❌ سرور یافت نشد."); updateState('none', []); exit; }
    
    $new_password = trim($text);
    
    if (strlen($new_password) < 8) {
        sendMessage("❌ رمز باید حداقل ۸ کاراکتر باشد.\n\nلطفاً یک رمز قوی‌تر وارد کنید یا <b>❌ لغو</b> را بزنید.");
        exit;
    }
    
    if (strlen($new_password) > 32) {
        sendMessage("❌ رمز نباید بیشتر از ۳۲ کاراکتر باشد.");
        exit;
    }
    
    sendMessage("⏳ در حال تغییر رمز عبور...");
    
    $account_index = (int)($srv['hostvds_account_index'] ?? 0);
    $result = changePasswordViaAPI($srv['os_server_id'], $account_index, $srv['location_api'], $new_password);
    
    if ($result['success']) {
        $pdo->prepare("UPDATE servers SET password = ? WHERE id = ?")->execute([$new_password, $srv_id]);
        
        $ip = $srv['ip_address'] ?: 'در حال دریافت';
        $msg = "✅ <b>رمز سرور با موفقیت تغییر یافت!</b>\n\n";
        $msg .= "🖥 سرور: <code>{$srv['server_name']}</code>\n";
        $msg .= "🌐 آی‌پی: <code>$ip</code>\n";
        $msg .= "🔑 رمز جدید: <code>" . htmlspecialchars($new_password) . "</code>\n\n";
        $msg .= "⚠️ <b>این رمز را در جای امنی ذخیره کنید.</b>\n";
        $msg .= "💡 <b>توجه:</b> تغییر رمز ممکن است چند ثانیه طول بکشد تا اعمال شود.";
        
        $ikb = ['inline_keyboard' => [
            [['text' => '🔙 بازگشت به سرور', 'callback_data' => "srv_detail_" . $srv_id]]
        ]];
        sendMessage($msg, $ikb);
    } else {
        $msg = "❌ <b>خطا در تغییر رمز:</b>\n\n⚠️ " . $result['error'] . "\n\nلطفاً بررسی کنید:\n▫️ سرور روشن باشد\n▫️ دسترسی API فعال باشد\n▫️ سرور در وضعیت ACTIVE باشد";
        $ikb = ['inline_keyboard' => [
            [['text' => '🔄 تلاش مجدد', 'callback_data' => "change_password_" . $srv_id]],
            [['text' => '🔙 بازگشت به سرور', 'callback_data' => "srv_detail_" . $srv_id]]
        ]];
        sendMessage($msg, $ikb);
    }
    
    updateState('none', []);
    exit;
}

// دریافت رسید کارت به کارت
if ($user['state'] == 'user_wait_deposit_receipt') {
    if (isset($update['message']['photo'])) {
        $photo = $update['message']['photo'];
        $file_id = $photo[count($photo) - 1]['file_id'];
        $temp = json_decode($user['temp_data'], true);
        $amount = $temp['deposit_amount'] ?? 0;
        updateState('none', []);
        $admin_msg = "💳 <b>درخواست افزایش موجودی جدید (کارت به کارت)</b>\n\n👤 شناسه کاربر: <code>$chat_id</code>\n💵 مبلغ واریزی: <b>" . number_format($amount) . " تومان</b>\n\nلطفاً رسید فوق را با حساب خود تطبیق داده و تایید یا رد کنید:";
        $admin_ikb = ['inline_keyboard' => [
            [['text' => '✅ تایید و افزایش موجودی', 'callback_data' => "adm_app_dep_{$chat_id}_{$amount}"]],
            [['text' => '❌ رد رسید و عدم تایید', 'callback_data' => "adm_rej_dep_{$chat_id}"]],
        ]];
        sendPhoto($ADMIN_ID, $file_id, $admin_msg, $admin_ikb);
        sendMessage("✅ رسید شما با موفقیت دریافت شد و برای مدیریت ارسال گردید. پس از تایید نهایی، کیف پول شما شارژ خواهد شد.", $kb);
        exit;
    } else {
        sendMessage("❌ خطا: لطفاً فقط تصویر یا عکسِ رسید واریزی خود را ارسال کنید.");
        exit;
    }
}

// گرفتن مبلغ از کاربر
if ($user['state'] == 'user_wait_deposit_amount') {
    if (!is_numeric($text) || (int)$text <= 0) {
        sendMessage("❌ لطفاً یک مبلغ معتبر به صورت عددی و به تومان وارد کنید.");
        exit;
    }
    $card_num = $pdo->query("SELECT value FROM settings WHERE `key` = 'card_number'")->fetchColumn() ?: 'وارد نشده';
    updateState('user_wait_deposit_receipt', ['deposit_amount' => (int)$text]);
    $msg_card = "💳 <b>مرحله کارت به کارت:</b>\n\n💰 مبلغ درخواستی: <b>" . number_format($text) . " تومان</b>\n\nلطفاً این مبلغ را به شماره کارت زیر واریز کنید:\n\n💳 شماره کارت:\n<code>$card_num</code>\n\n📸 پس از واریز، <u>تصویر واضح (عکس) رسید</u> خود را در پاسخ به همین پیام ارسال کنید:";
    sendMessage($msg_card, ['keyboard' => [[['text' => '❌ لغو']]], 'resize_keyboard' => true]);
    exit;
}

// انتقال وجه - گرفتن ایدی مقصد
if ($user['state'] == 'user_wait_transfer_id') {
    if (!is_numeric($text)) { sendMessage("❌ شناسه کاربری نامعتبر است."); exit; }
    $check = $pdo->prepare("SELECT chat_id FROM users WHERE chat_id = ?"); $check->execute([$text]);
    if (!$check->fetch() || $text == $chat_id) { sendMessage("❌ کاربر یافت نشد یا شناسه خودتان است."); exit; }
    updateState('user_wait_transfer_amount', ['target_id' => $text]);
    sendMessage("💵 مبلغ مورد نظر برای انتقال را به <b>تومان</b> وارد کنید:\n(توجه: ۱۰٪ کارمزد سیستمی کسر خواهد شد)", ['keyboard' => [[['text' => '❌ لغو']]], 'resize_keyboard' => true]); exit;
}

// انتقال وجه - گرفتن مبلغ
if ($user['state'] == 'user_wait_transfer_amount') {
    if (!is_numeric($text) || (int)$text <= 0) { sendMessage("❌ مبلغ نامعتبر است."); exit; }
    $amount = (int)$text;
    if ($user['balance'] < $amount) { sendMessage("❌ موجودی کیف پول شما برای این انتقال کافی نیست."); exit; }
    $temp = json_decode($user['temp_data'], true); $target = $temp['target_id'];
    $fee = $amount * 0.10; $net = $amount - $fee;
    $pdo->prepare("UPDATE users SET balance = balance - ? WHERE chat_id = ?")->execute([$amount, $chat_id]);
    $pdo->prepare("UPDATE users SET balance = balance + ? WHERE chat_id = ?")->execute([$net, $target]);
    logTransaction($chat_id, -$amount, "انتقال وجه به کاربر $target (کسر کارمزد)");
    logTransaction($target, $net, "دریافت وجه از کاربر $chat_id");
    updateState('none', []);
    sendMessage("✅ مبلغ " . number_format($amount) . " تومان با کسر کارمزد با موفقیت به کاربر مقصد منتقل شد.", $kb);
    sendDirectMessage($target, "💳 <b>موجودی دریافت شد!</b>\nمبلغ " . number_format($net) . " تومان از طرف کاربر <code>$chat_id</code> به کیف پول شما واریز گردید. (پس از کسر کارمزد)"); exit;
}

// تغییر نام سرور
if ($user['state'] == 'user_wait_server_rename') {
    $temp = json_decode($user['temp_data'], true);
    $pdo->prepare("UPDATE servers SET server_name = ? WHERE id = ? AND chat_id = ?")->execute([$text, $temp['rename_id'], $chat_id]);
    updateState('none', []);
    sendMessage("✅ نام سرور با موفقیت به <code>$text</code> تغییر یافت.", $kb); exit;
}

// حالت های ادمین برای FAQ
if ($user['state'] == 'admin_wait_faq_q') { updateState('admin_wait_faq_a', ['faq_q' => $text]); sendMessage("✏️ حالا پاسخ را بنویسید:"); exit; }
if ($user['state'] == 'admin_wait_faq_a') { $temp = json_decode($user['temp_data'], true); $pdo->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)")->execute([$temp['faq_q'], $text]); updateState('none', []); sendMessage("✅ سوال اضافه شد.", $kb); exit; }
if ($user['state'] == 'admin_wait_faq_edit_a') { $temp = json_decode($user['temp_data'], true); $pdo->prepare("UPDATE faqs SET answer = ? WHERE id = ?")->execute([$text, $temp['faq_id']]); updateState('none', []); sendMessage("✅ پاسخ ویرایش شد.", $kb); exit; }

// شماره کارت
if ($user['state'] == 'admin_wait_card_num') { $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES ('card_number', ?) ON DUPLICATE KEY UPDATE `value` = ?")->execute([$text, $text]); updateState('none', []); sendMessage("✅ شماره کارت آپدیت شد:\n<code>$text</code>", $kb); exit; }

// لوکیشن
if ($user['state'] == 'admin_wait_loc_name') { $temp = json_decode($user['temp_data'], true); $pdo->prepare("INSERT INTO locations (name, api_id, status) VALUES (?, ?, 1)")->execute([$text, $temp['loc_api']]); updateState('none', []); sendMessage("✅ لوکیشن افزوده شد.", $kb); exit; }

// پلن
if ($user['state'] == 'admin_wait_plan_name') { $temp = json_decode($user['temp_data'], true); updateState('admin_wait_plan_price', array_merge($temp, ['plan_name' => $text])); sendMessage("💰 هزینه ساعتی (تومان):"); exit; }
if ($user['state'] == 'admin_wait_plan_price' && is_numeric($text)) { $temp = json_decode($user['temp_data'], true); $pdo->prepare("INSERT INTO plans (name, api_id, hourly_price) VALUES (?, ?, ?)")->execute([$temp['plan_name'], $temp['plan_api'], (int)$text]); updateState('none', []); sendMessage("✅ پلن ثبت شد.", $kb); exit; }

// پیام همگانی
if ($user['state'] == 'admin_wait_broadcast') { updateState('none', []); sendMessage("⏳ در حال ارسال..."); $users = $pdo->query("SELECT chat_id FROM users")->fetchAll(PDO::FETCH_COLUMN); foreach ($users as $u_id) { sendDirectMessage($u_id, "📢 <b>پیام همگانی:</b>\n\n" . $text); } sendMessage("✅ ارسال شد.", $kb); exit; }

// مدیریت کیف پول توسط ادمین
if ($user['state'] == 'admin_wait_wallet_id' && is_numeric($text)) { updateState('admin_wait_wallet_amount', ['target_user' => $text]); sendMessage("💵 مبلغ (مثبت/منفی):"); exit; }
if ($user['state'] == 'admin_wait_wallet_amount' && is_numeric($text)) { $temp = json_decode($user['temp_data'], true); $amount = (int)$text; $target = $temp['target_user']; $pdo->prepare("UPDATE users SET balance = balance + ? WHERE chat_id = ?")->execute([$amount, $target]); logTransaction($target, $amount, "ویرایش توسط مدیریت"); updateState('none', []); sendMessage("✅ موجودی ویرایش شد.", $kb); sendDirectMessage($target, "💳 حساب شما " . ($amount > 0 ? "شارژ" : "کاهش") . " شد: " . number_format(abs($amount)) . " تومان"); exit; }

// گرفتن ساعت برای خرید سرور
if ($user['state'] == 'wait_hours' && is_numeric($text)) {
    $hours = (int) $text;
    if ($hours < 1 || $hours > 720) { sendMessage("❌ زمان نامعتبر."); exit; }
    $temp = json_decode($user['temp_data'], true);
    $loc = $pdo->query("SELECT * FROM locations WHERE id = {$temp['buy_loc']}")->fetch();
    $plan = $pdo->query("SELECT * FROM plans WHERE id = {$temp['buy_plan']}")->fetch();
    $total = $plan['hourly_price'] * $hours;
    updateState('none', array_merge($temp, ['buy_hours' => $hours]));
    $msg = "📋 <b>فاکتور خرید سرور:</b>\n\n📍 لوکیشن: <b>{$loc['name']}</b>\n⚙️ پلن: <b>{$plan['name']}</b>\n⏱ مدت: <b>$hours ساعت</b>\n💵 هزینه: <b>" . number_format($total) . " تومان</b>";
    $msg .= "\n📦 نوع: <b>Ubuntu خالص</b>";
    $ikb = ['inline_keyboard' => [[['text' => '✅ پرداخت', 'callback_data' => 'confirm_buy']], [['text' => '❌ انصراف', 'callback_data' => 'cancel']]]];
    sendMessage($msg, $ikb); exit;
}

// ==========================================
// پردازش کالبک‌ها - این بخش خیلی طولانیه
// ==========================================
if ($callback_data) {
    if ($callback_data == 'cancel') { updateState('none', []); editMessageText($message_id, "❌ لغو شد."); exit; }
    
    if ($callback_data == 'user_deposit_card') { updateState('user_wait_deposit_amount'); editMessageText($message_id, "💵 مبلغ (تومان):"); exit; }
    
    // بازگشت به کیف پول
    if ($callback_data == 'back_to_wallet') {
        $txs = $pdo->query("SELECT * FROM transactions WHERE chat_id = $chat_id ORDER BY id DESC LIMIT 10")->fetchAll();
        $tx_text = "📜 <b>۱۰ تراکنش اخیر:</b>\n\n";
        if (empty($txs)) $tx_text .= "تراکنشی یافت نشد.\n";
        else {
            foreach($txs as $tx) {
                $icon = $tx['amount'] > 0 ? '🟢' : '🔴';
                $sign = $tx['amount'] > 0 ? '+' : '';
                $date = date('Y/m/d H:i', strtotime($tx['created_at']));
                $tx_text .= "$icon <b>{$sign}" . number_format($tx['amount']) . " ت</b> | {$tx['description']}\n▫️ <i>$date</i>\n";
            }
        }
        $ikb = ['inline_keyboard' => [
            [['text' => '💳 کارت به کارت', 'callback_data' => 'user_deposit_card']],
            [['text' => '💸 انتقال به کاربر', 'callback_data' => 'user_transfer_funds']]
        ]];
        editMessageText($message_id, "💳 <b>کیف پول:</b>\n\n💰 موجودی: <code>" . number_format($user['balance']) . "</code> تومان\n\n$tx_text", $ikb);
        exit;
    }
    
    if ($callback_data == 'user_transfer_funds') { updateState('user_wait_transfer_id'); editMessageText($message_id, "👤 Chat ID مقصد:"); exit; }
    
    // تایید رسید توسط ادمین
    if (strpos($callback_data, 'adm_app_dep_') === 0) {
        if ($chat_id != $ADMIN_ID) exit;
        $parts = explode('_', str_replace('adm_app_dep_', '', $callback_data));
        $u_id = $parts[0]; $amount = (int)$parts[1];
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE chat_id = ?")->execute([$amount, $u_id]);
        logTransaction($u_id, $amount, "شارژ (کارت به کارت)");
        editMessageCaption($message_id, "✅ تایید شد. کاربر <code>$u_id</code> به مبلغ " . number_format($amount) . " تومان شارژ شد.");
        sendDirectMessage($u_id, "💳 <b>رسید تایید شد!</b>\n" . number_format($amount) . " تومان به کیف پول شما اضافه شد. 🟢");
        exit;
    }
    
    // رد رسید
    if (strpos($callback_data, 'adm_rej_dep_') === 0) {
        if ($chat_id != $ADMIN_ID) exit;
        $u_id = str_replace('adm_rej_dep_', '', $callback_data);
        editMessageCaption($message_id, "❌ رسید رد شد.");
        sendDirectMessage($u_id, "⚠️ <b>درخواست شما تایید نشد!</b> 🔴");
        exit;
    }
    
    // مدیریت FAQ
    if ($callback_data == 'admin_manage_faq' && $chat_id == $ADMIN_ID) {
        $faqs = $pdo->query("SELECT * FROM faqs ORDER BY id DESC")->fetchAll();
        $btns = [];
        foreach ($faqs as $f) { $btns[] = [['text' => "📝 " . mb_substr($f['question'], 0, 30), 'callback_data' => "adm_faq_opts_" . $f['id']]]; }
        $btns[] = [['text' => '➕ سوال جدید', 'callback_data' => 'adm_faq_add']];
        $btns[] = [['text' => '🔙', 'callback_data' => 'cancel']];
        editMessageText($message_id, "❓ <b>مدیریت FAQ:</b>", ['inline_keyboard' => $btns]); exit;
    }
    if ($callback_data == 'adm_faq_add' && $chat_id == $ADMIN_ID) { updateState('admin_wait_faq_q'); editMessageText($message_id, "✏️ سوال:"); exit; }
    if (strpos($callback_data, 'adm_faq_opts_') === 0 && $chat_id == $ADMIN_ID) {
        $id = str_replace('adm_faq_opts_', '', $callback_data);
        $f = $pdo->query("SELECT * FROM faqs WHERE id = $id")->fetch();
        $ikb = ['inline_keyboard' => [
            [['text' => '✏️ ویرایش پاسخ', 'callback_data' => "adm_faq_edit_" . $id]],
            [['text' => '🗑️ حذف', 'callback_data' => "adm_faq_del_" . $id]],
            [['text' => '🔙', 'callback_data' => 'admin_manage_faq']]
        ]];
        editMessageText($message_id, "❓ <b>{$f['question']}</b>\n\n💬 {$f['answer']}", $ikb); exit;
    }
    if (strpos($callback_data, 'adm_faq_del_') === 0 && $chat_id == $ADMIN_ID) { $id = str_replace('adm_faq_del_', '', $callback_data); $pdo->query("DELETE FROM faqs WHERE id = $id"); sendMessage("✅ حذف شد."); exit; }
    if (strpos($callback_data, 'adm_faq_edit_') === 0 && $chat_id == $ADMIN_ID) { $id = str_replace('adm_faq_edit_', '', $callback_data); updateState('admin_wait_faq_edit_a', ['faq_id' => $id]); sendMessage("✏️ پاسخ جدید:"); exit; }
    
    // شماره کارت
    if ($callback_data == 'admin_set_card' && $chat_id == $ADMIN_ID) { updateState('admin_wait_card_num'); editMessageText($message_id, "💳 شماره کارت ۱۶ رقمی:"); exit; }
    
    // روشن خاموش کردن ربات
    if ($callback_data == 'admin_toggle_bot' && $chat_id == $ADMIN_ID) { $next = $bot_status == 'on' ? 'off' : 'on'; $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'bot_status'")->execute([$next]); sendMessage("✅ وضعیت: " . ($next == 'on' ? "روشن" : "خاموش")); exit; }
    
    // وضعیت اکانت ها
    if ($callback_data == 'admin_accounts_status' && $chat_id == $ADMIN_ID) {
        $msg = "🔐 <b>وضعیت اکانت‌های HostVDS:</b>\n\n";
        foreach ($navidi_accounts as $index => $acc) {
            if (empty($acc['user_id'])) continue;
            $count = count_servers_for_account($index);
            $max = $acc['max_servers'] ?? 10;
            $st = empty($acc['status']) ? "🔴" : "🟢";
            $msg .= "$st <b>{$acc['name']}</b>: $count/$max\n";
        }
        $total_used = $pdo->query("SELECT COUNT(*) FROM servers WHERE status = 'active'")->fetchColumn();
        $msg .= "\n📊 کل سرورهای فعال: <code>$total_used</code>";
        editMessageText($message_id, $msg, ['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'cancel']]]]);
        exit;
    }
    
    // آمار
    if ($callback_data == 'admin_stats' && $chat_id == $ADMIN_ID) {
        $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $total_balance = $pdo->query("SELECT SUM(balance) FROM users")->fetchColumn() ?: 0;
        $active_vps = $pdo->query("SELECT COUNT(*) FROM servers WHERE status='active'")->fetchColumn();
        $msg = "📊 <b>آمار:</b>\n\nکاربران: <code>$total_users</code>\nدارایی‌ها: <code>" . number_format($total_balance) . "</code> ت\nسرورهای فعال: <code>$active_vps</code>";
        editMessageText($message_id, $msg, ['inline_keyboard' => [[['text' => '🔙', 'callback_data' => 'cancel']]]]); exit;
    }
    
    // پیام همگانی
    if ($callback_data == 'admin_broadcast' && $chat_id == $ADMIN_ID) { updateState('admin_wait_broadcast'); editMessageText($message_id, "📢 متن پیام:"); exit; }
    
    // مدیریت مالی
    if ($callback_data == 'admin_wallet_manage' && $chat_id == $ADMIN_ID) { updateState('admin_wait_wallet_id'); editMessageText($message_id, "👤 Chat ID:"); exit; }
    
    // مدیریت لوکیشن
    if ($callback_data == 'admin_manage_locs' && $chat_id == $ADMIN_ID) {
        $locs = $pdo->query("SELECT * FROM locations")->fetchAll(); $btns = [];
        foreach($locs as $l) { $btns[] = [['text' => "{$l['name']} (" . ($l['status'] == 1 ? "🟢" : "🔴") . ")", 'callback_data' => "view_loc_" . $l['id']]]; }
        $btns[] = [['text' => '➕ لوکیشن جدید', 'callback_data' => 'admin_add_loc']];
        $btns[] = [['text' => '🔙', 'callback_data' => 'cancel']];
        editMessageText($message_id, "🌐 <b>لوکیشن‌ها:</b>", ['inline_keyboard' => $btns]); exit;
    }
    if (strpos($callback_data, 'view_loc_') === 0 && $chat_id == $ADMIN_ID) {
        $id = str_replace('view_loc_', '', $callback_data); $l = $pdo->query("SELECT * FROM locations WHERE id = $id")->fetch();
        $ikb = ['inline_keyboard' => [[['text' => $l['status'] == 1 ? "🔴 ناموجود" : "🟢 موجود", 'callback_data' => "toggle_loc_" . $id]], [['text' => '🗑️ حذف', 'callback_data' => "del_loc_" . $id]], [['text' => '🔙', 'callback_data' => 'admin_manage_locs']]]];
        editMessageText($message_id, "📍 <b>{$l['name']}</b>", $ikb); exit;
    }
    if (strpos($callback_data, 'toggle_loc_') === 0 && $chat_id == $ADMIN_ID) { $id = str_replace('toggle_loc_', '', $callback_data); $pdo->query("UPDATE locations SET status = 1 - status WHERE id = $id"); sendMessage("✅ تغییر کرد."); exit; }
    if (strpos($callback_data, 'del_loc_') === 0 && $chat_id == $ADMIN_ID) { $id = str_replace('del_loc_', '', $callback_data); $pdo->query("DELETE FROM locations WHERE id = $id"); sendMessage("✅ حذف شد."); exit; }
    if ($callback_data == 'admin_add_loc' && $chat_id == $ADMIN_ID) {
        $auth = get_auth(0); $regions = [];
        foreach ($auth['catalog'] as $srv) { if ($srv['type'] == 'compute') { foreach ($srv['endpoints'] as $ep) { if ($ep['interface'] == 'public') { $reg = $ep['region'] ?? $ep['region_id']; if ($reg && !in_array($reg, $regions)) $regions[] = $reg; } } } }
        $btns = []; foreach($regions as $reg) { $btns[] = [['text' => "📍 " . strtoupper($reg), 'callback_data' => "set_loc_api_" . $reg]]; }
        editMessageText($message_id, "🌐 ریجن:", ['inline_keyboard' => $btns]); exit;
    }
    if (strpos($callback_data, 'set_loc_api_') === 0 && $chat_id == $ADMIN_ID) { $api_id = str_replace('set_loc_api_', '', $callback_data); updateState('admin_wait_loc_name', ['loc_api' => $api_id]); sendMessage("✏️ نام نمایشی:"); exit; }
    
    // مدیریت پلن
    if ($callback_data == 'admin_manage_plans' && $chat_id == $ADMIN_ID) {
        $plans = $pdo->query("SELECT * FROM plans")->fetchAll(); $btns = [];
        foreach($plans as $p) { $btns[] = [['text' => "⚙️ {$p['name']} - " . number_format($p['hourly_price']) . " ت", 'callback_data' => "del_plan_info_" . $p['id']]]; }
        $btns[] = [['text' => '➕ پلن جدید', 'callback_data' => 'admin_add_plan']]; $btns[] = [['text' => '🔙', 'callback_data' => 'cancel']];
        editMessageText($message_id, "⚙️ <b>پلن‌ها:</b>", ['inline_keyboard' => $btns]); exit;
    }
    if (strpos($callback_data, 'del_plan_info_') === 0 && $chat_id == $ADMIN_ID) { $id = str_replace('del_plan_info_', '', $callback_data); $pdo->query("DELETE FROM plans WHERE id = $id"); sendMessage("✅ حذف شد."); exit; }
    if ($callback_data == 'admin_add_plan' && $chat_id == $ADMIN_ID) {
        $locs = $pdo->query("SELECT * FROM locations")->fetchAll(); if(empty($locs)) { sendMessage("❌ ابتدا لوکیشن اضافه کنید."); exit; }
        $auth = get_auth(0); $token = $auth['token']; $nova_url = get_endpoint($auth['catalog'], 'compute', $locs[0]['api_id']);
        $flav_res = os_request($nova_url . "/flavors/detail", 'GET', $token); $btns = [];
        foreach($flav_res['body']['flavors'] ?? [] as $flv) { $btns[] = [['text' => "⚙️ {$flv['name']} [RAM: {$flv['ram']}MB]", 'callback_data' => "set_flv_api_" . $flv['id']]]; }
        editMessageText($message_id, "⚙️ سخت‌افزار:", ['inline_keyboard' => $btns]); exit;
    }
    if (strpos($callback_data, 'set_flv_api_') === 0 && $chat_id == $ADMIN_ID) { $api_id = str_replace('set_flv_api_', '', $callback_data); updateState('admin_wait_plan_name', ['plan_api' => $api_id]); sendMessage("✏️ نام نمایشی:"); exit; }
    
    // انتخاب لوکیشن
    if (strpos($callback_data, 'buy_loc_') === 0) {
        $loc_id = str_replace('buy_loc_', '', $callback_data); 
        $plans = $pdo->query("SELECT * FROM plans")->fetchAll(); 
        $btns = []; 
        foreach($plans as $plan) { 
            $btns[] = [['text' => "⚙️ {$plan['name']} - ساعتی {$plan['hourly_price']} ت", 'callback_data' => "buy_plan_{$loc_id}_{$plan['id']}"]]; 
        }
        updateState('none', ['buy_loc' => $loc_id]); 
        sendMessage("⚙️ <b>پلن سخت‌افزاری مورد نظر خود را انتخاب کنید:</b>", ['inline_keyboard' => $btns]); 
        exit;
    }
    
    // انتخاب پلن
    if (strpos($callback_data, 'buy_plan_') === 0) {
        $parts = explode('_', str_replace('buy_plan_', '', $callback_data));
        $loc_id = $parts[0];
        $plan_id = $parts[1];
        
        updateState('wait_hours', ['buy_loc' => $loc_id, 'buy_plan' => $plan_id]);
        sendMessage("⏳ سرور را برای چند ساعت پیش‌خرید می‌کنید؟ (۱ تا ۷۲۰ ساعت):", ['keyboard' => [[['text' => '❌ لغو']]], 'resize_keyboard' => true]);
        exit;
    }
    
    // تایید خرید - اینجا سرور ساخته میشه
    if ($callback_data == 'confirm_buy') {
        $temp = json_decode($user['temp_data'], true);
        $loc = $pdo->query("SELECT * FROM locations WHERE id = {$temp['buy_loc']}")->fetch();
        $plan = $pdo->query("SELECT * FROM plans WHERE id = {$temp['buy_plan']}")->fetch();
        $hours = $temp['buy_hours']; $total_cost = $plan['hourly_price'] * $hours;
        if ($user['balance'] < $total_cost) { sendMessage("❌ موجودی کافی نیست."); exit; }
        $best_account_index = get_best_account($loc['api_id']);
        if ($best_account_index === -1) { sendMessage("❌ ظرفیت تکمیل است!"); exit; }
        $account_name = $navidi_accounts[$best_account_index]['name'] ?? "Account #$best_account_index";
        sendMessage("⏳ در حال ساخت...\n🔐 اکانت: <b>$account_name</b>\n📦 نصب: <b>Ubuntu خالص</b>");
        $auth = get_auth($best_account_index); $token = $auth['token'];
        if (!$token) { sendMessage("❌ خطا در اتصال."); exit; }
        $region = $loc['api_id'];
        $nova_url = get_endpoint($auth['catalog'], 'compute', $region);
        $net_url = get_endpoint($auth['catalog'], 'network', $region);
        $glance_url = get_endpoint($auth['catalog'], 'image', $region);
        $sec_group_name = "default";
        $sec_res = os_request($net_url . "/v2.0/security-groups", 'GET', $token);
        if (!empty($sec_res['body']['security_groups'])) {
            foreach ($sec_res['body']['security_groups'] as $sg) {
                if (strtolower($sg['name']) == 'allow_all') { $sec_group_name = "allow_all"; break; }
            }
        }
        $net_res = os_request($net_url . "/v2.0/networks", 'GET', $token); $network_id = null;
        if (!empty($net_res['body']['networks'])) {
            foreach ($net_res['body']['networks'] as $net) {
                if (strpos(strtolower($net['name']), 'ipv4') !== false) { $network_id = $net['id']; break; }
            }
            if (!$network_id) {
                $subnet_res = os_request($net_url . "/v2.0/subnets", 'GET', $token);
                if (!empty($subnet_res['body']['subnets'])) {
                    foreach ($subnet_res['body']['subnets'] as $sub) {
                        if ($sub['ip_version'] == 4) { $network_id = $sub['network_id']; break; }
                    }
                }
            }
            if (!$network_id) {
                foreach ($net_res['body']['networks'] as $net) {
                    if (strpos(strtolower($net['name']), 'public') !== false || strpos(strtolower($net['name']), 'ext') !== false) { $network_id = $net['id']; break; }
                }
            }
            if (!$network_id && isset($net_res['body']['networks'][0])) $network_id = $net_res['body']['networks'][0]['id'];
        }
        $img_res = os_request($glance_url . "/v2/images?limit=100", 'GET', $token); $ubuntu_id = null;
        foreach ($img_res['body']['images'] ?? [] as $img) { $n = strtolower(trim($img['name'])); if (strpos($n, 'ubuntu-22.04') !== false && strpos($n, 'prebuilt') === false) { $ubuntu_id = $img['id']; break; } }
        if (!$ubuntu_id || !$network_id) { sendMessage("❌ خطا در تامین منابع."); exit; }
        $pdo->query("UPDATE users SET balance = balance - $total_cost WHERE chat_id = $chat_id");
        logTransaction($chat_id, -$total_cost, "خرید سرور در {$loc['name']}");
        $server_name = "VDS-" . substr(uniqid(), -4);
        
        $custom_password = generateStrongPassword(14);
        
        $cloud_config = "#cloud-config\nchpasswd:\n  list: |\n    root:{$custom_password}\n  expire: False\nssh_pwauth: True\n";
        
        $server_data = ["server" => ["name" => $server_name, "imageRef" => $ubuntu_id, "flavorRef" => $plan['api_id'], "networks" => [["uuid" => $network_id]], "user_data" => base64_encode($cloud_config), "security_groups" => [["name" => $sec_group_name]]]];
        $create_res = os_request($nova_url . "/servers", 'POST', $token, $server_data);
        if (isset($create_res['body']['server']['id'])) {
            $os_id = $create_res['body']['server']['id']; $paid_until = date('Y-m-d H:i:s', strtotime("+$hours hours"));
            $stmt = $pdo->prepare("INSERT INTO servers (chat_id, os_server_id, server_name, password, hourly_price, plan_id, paid_until, location_api, hostvds_account_index, status, auto_renew) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1)");
            $stmt->execute([$chat_id, $os_id, $server_name, $custom_password, $plan['hourly_price'], $plan['id'], $paid_until, $region, $best_account_index]);
            $msg = "🎉 <b>سرور ساخته شد!</b>\n\n🖥 نام: <code>$server_name</code>\n🔑 رمز SSH: <code>$custom_password</code>\n🔐 اکانت: <b>$account_name</b>\n📦 نصب: <b>Ubuntu خالص</b>";
            sendMessage($msg, $kb);
        } else {
            $pdo->query("UPDATE users SET balance = balance + $total_cost WHERE chat_id = $chat_id");
            sendMessage("❌ اختلال در دیتاسنتر؛ مبلغ برگشت داده شد.");
        }
        updateState('none', []); exit;
    }
    
    // جزئیات سرور
    if (strpos($callback_data, 'srv_detail_') === 0) {
        $srv_id = str_replace('srv_detail_', '', $callback_data);
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id AND status = 'active'")->fetch();
        if (!$srv) { editMessageText($message_id, "❌ سرویس یافت نشد."); exit; }
        $ip4 = $srv['ip_address']; $ip6 = $srv['ipv6_address'];
        $loc_name = "وارد نشده";
        if (!empty($srv['location_api'])) {
            $l_db = $pdo->prepare("SELECT name FROM locations WHERE api_id = ?"); $l_db->execute([$srv['location_api']]); $l_res = $l_db->fetch();
            if ($l_res && !empty($l_res['name'])) $loc_name = $l_res['name'];
        }
        $plan_name = "وارد نشده"; $plan_price = 0;
        if (!empty($srv['plan_id'])) {
            $p_db = $pdo->prepare("SELECT name, hourly_price FROM plans WHERE id = ?"); $p_db->execute([$srv['plan_id']]); $p_res = $p_db->fetch();
            if ($p_res && !empty($p_res['name'])) { $plan_name = $p_res['name']; $plan_price = $p_res['hourly_price']; }
        }
        $account_index = (int)($srv['hostvds_account_index'] ?? 0);
        $account_name = $navidi_accounts[$account_index]['name'] ?? "Account #$account_index";
        $auth = get_auth($account_index); $token = $auth['token'];
        $nova_url = get_endpoint($auth['catalog'], 'compute', $srv['location_api']);
        $info = os_request($nova_url . "/servers/" . $srv['os_server_id'], 'GET', $token);
        $vm_state = $info['body']['server']['status'] ?? 'UNKNOWN';
        $status_emoji = "⚪️"; $status_text = "نامشخص";
        if ($vm_state == 'ACTIVE') { $status_emoji = "🟢"; $status_text = "روشن"; }
        elseif ($vm_state == 'SHUTOFF') { $status_emoji = "🔴"; $status_text = "خاموش"; }
        elseif (in_array($vm_state, ['BUILD', 'REBUILD', 'RESIZE'])) { $status_emoji = "⏳"; $status_text = "در حال پردازش"; }
        if (isset($info['body']['server']['addresses'])) {
            foreach ($info['body']['server']['addresses'] as $net_lbl => $ips) {
                foreach ($ips as $i) { if ($i['version'] == 4) $ip4 = $i['addr']; if ($i['version'] == 6) $ip6 = $i['addr']; }
            }
            $pdo->query("UPDATE servers SET ip_address = '$ip4', ipv6_address = '$ip6' WHERE id = {$srv['id']}");
        }
        $ip4_display = ($ip4 == 'Wait...' || $ip4 == '') ? "⏳ در حال دریافت..." : "<code>$ip4</code>";
        $ip6_display = ($ip6 == '' || $ip6 == null) ? "⏳/❌ ناموجود" : "<code>$ip6</code>";
        $renew_status = ($srv['auto_renew'] == 1) ? "🟢 روشن" : "🔴 خاموش";
        $msg = "🖥 <b>مشخصات سرور:</b>\n\n";
        $msg .= "🏷 نام: <code>{$srv['server_name']}</code>\n";
        $msg .= "📡 وضعیت: $status_emoji <b>$status_text</b>\n";
        $msg .= "🔄 تمدید خودکار: <b>$renew_status</b>\n";
        $msg .= "🌐 IPv4: $ip4_display\n";
        $msg .= "🌐 IPv6: $ip6_display\n";
        $msg .= "⚙️ پلن: <b>$plan_name</b>\n";
        $msg .= "📍 دیتاسنتر: <b>$loc_name</b>\n";
        $msg .= "🔐 اکانت: <b>$account_name</b>\n";
        $msg .= "👤 یوزر: <code>root</code>\n";
        $msg .= "🔑 پسورد: <code>{$srv['password']}</code>\n";
        $msg .= "⏳ پایان: <code>{$srv['paid_until']}</code>\n";
        $msg .= "━━━━━━━━━━━━━";
        $ikb = ['inline_keyboard' => [
            [['text' => '🟢 روشن', 'callback_data' => "pwr_start_" . $srv['id']], ['text' => '🔴 خاموش', 'callback_data' => "pwr_stop_" . $srv['id']], ['text' => '🔄 ریبوت', 'callback_data' => "pwr_reboot_" . $srv['id']]],
            [['text' => '📊 آمار', 'callback_data' => "srv_stats_" . $srv['id']]],
            [['text' => '✏️ تغییر نام', 'callback_data' => "srv_rename_" . $srv['id']]],
            [['text' => '🔐 تغییر رمز', 'callback_data' => "change_password_" . $srv['id']]],
            [['text' => '🔄 بازنصب OS', 'callback_data' => "reinstall_menu_" . $srv['id']]],
            [['text' => 'تغییر تمدید خودکار', 'callback_data' => "toggle_renew_" . $srv['id']]],
            [['text' => '🗑️ حذف سرور', 'callback_data' => "del_srv_req_" . $srv['id']]],
            [['text' => '🔙 لیست', 'callback_data' => 'back_to_vps_list']]
        ]];
        editMessageText($message_id, $msg, $ikb); exit;
    }
    
    // تغییر رمز
    if (strpos($callback_data, 'change_password_') === 0) {
        $srv_id = str_replace('change_password_', '', $callback_data);
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id AND status = 'active'")->fetch();
        if (!$srv) { editMessageText($message_id, "❌ سرور یافت نشد."); exit; }
        
        $ip = $srv['ip_address'];
        if (empty($ip) || $ip == 'Wait...') {
            editMessageText($message_id, "❌ سرور هنوز آی‌پی دریافت نکرده است.");
            exit;
        }
        
        updateState('wait_new_password', ['change_pw_srv_id' => $srv_id]);
        
        $msg = "🔐 <b>تغییر رمز سرور</b>\n\n";
        $msg .= "🖥 سرور: <code>{$srv['server_name']}</code>\n";
        $msg .= "🌐 آی‌پی: <code>$ip</code>\n\n";
        $msg .= "✏️ <b>رمز جدید را وارد کنید:</b>\n\n";
        $msg .= "📋 <b>الزامات رمز:</b>\n";
        $msg .= "▫️ حداقل ۸ کاراکتر\n";
        $msg .= "▫️ حداکثر ۳۲ کاراکتر\n";
        $msg .= "▫️ شامل حروف بزرگ، کوچک و عدد\n\n";
        $msg .= "💡 <b>روش تغییر رمز:</b>\n";
        $msg .= "▫️ از طریق <b>OpenStack API</b> (بدون نیاز به SSH)\n";
        $msg .= "▫️ تغییر رمز چند ثانیه طول می‌کشد\n\n";
        $msg .= "🎲 <b>یا رمز تصادفی می‌خواهید؟</b> روی دکمه زیر کلیک کنید.";
        
        $random_password = generateStrongPassword(14);
        
        editMessageText($message_id, $msg, ['inline_keyboard' => [
            [['text' => '🎲 استفاده از رمز تصادفی', 'callback_data' => "use_random_pw_" . $srv_id]],
            [['text' => '❌ لغو', 'callback_data' => "srv_detail_" . $srv_id]]
        ]]);
        exit;
    }
    
    // رمز تصادفی
    if (strpos($callback_data, 'use_random_pw_') === 0) {
        $srv_id = str_replace('use_random_pw_', '', $callback_data);
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id AND status = 'active'")->fetch();
        if (!$srv) { editMessageText($message_id, "❌ سرور یافت نشد."); exit; }
        
        $random_password = generateStrongPassword(14);
        
        editMessageText($message_id, "⏳ در حال تغییر رمز سرور از طریق API...");
        
        $account_index = (int)($srv['hostvds_account_index'] ?? 0);
        $result = changePasswordViaAPI($srv['os_server_id'], $account_index, $srv['location_api'], $random_password);
        
        if ($result['success']) {
            $pdo->prepare("UPDATE servers SET password = ? WHERE id = ?")->execute([$random_password, $srv_id]);
            
            $ip = $srv['ip_address'] ?: 'در حال دریافت';
            $msg = "✅ <b>رمز سرور با موفقیت تغییر یافت!</b>\n\n";
            $msg .= "🖥 سرور: <code>{$srv['server_name']}</code>\n";
            $msg .= "🌐 آی‌پی: <code>$ip</code>\n";
            $msg .= "🔑 رمز جدید: <code>" . htmlspecialchars($random_password) . "</code>\n\n";
            $msg .= "⚠️ <b>این رمز را در جای امنی ذخیره کنید.</b>\n";
            $msg .= "💡 <b>توجه:</b> تغییر رمز ممکن است چند ثانیه طول بکشد تا اعمال شود.";
            
            $ikb = ['inline_keyboard' => [
                [['text' => '🔙 بازگشت به سرور', 'callback_data' => "srv_detail_" . $srv_id]]
            ]];
            editMessageText($message_id, $msg, $ikb);
        } else {
            $msg = "❌ <b>خطا در تغییر رمز:</b>\n\n⚠️ " . $result['error'] . "\n\nلطفاً بررسی کنید:\n▫️ سرور روشن باشد\n▫️ دسترسی API فعال باشد\n▫️ سرور در وضعیت ACTIVE باشد";
            $ikb = ['inline_keyboard' => [
                [['text' => '🔄 تلاش مجدد', 'callback_data' => "change_password_" . $srv_id]],
                [['text' => '🔙 بازگشت به سرور', 'callback_data' => "srv_detail_" . $srv_id]]
            ]];
            editMessageText($message_id, $msg, $ikb);
        }
        exit;
    }
    
    // دکمه های پاور سرور
    if (strpos($callback_data, 'pwr_') === 0) {
        $parts = explode('_', str_replace('pwr_', '', $callback_data)); $action = $parts[0]; $srv_id = $parts[1];
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id")->fetch();
        if ($srv) {
            $account_index = (int)($srv['hostvds_account_index'] ?? 0);
            $auth = get_auth($account_index); $nova_url = get_endpoint($auth['catalog'], 'compute', $srv['location_api']);
            $post_data = [];
            if ($action == 'start') $post_data = ["os-start" => null];
            elseif ($action == 'stop') $post_data = ["os-stop" => null];
            elseif ($action == 'reboot') $post_data = ["reboot" => ["type" => "HARD"]];
            os_request($nova_url . "/servers/" . $srv['os_server_id'] . "/action", 'POST', $auth['token'], $post_data);
            sendMessage("✅ سیگنال '$action' ارسال شد.");
        } exit;
    }
    
    // تغییر نام
    if (strpos($callback_data, 'srv_rename_') === 0) { $srv_id = str_replace('srv_rename_', '', $callback_data); updateState('user_wait_server_rename', ['rename_id' => $srv_id]); sendMessage("✏️ نام جدید:"); exit; }
    
    // آمار سرور
    if (strpos($callback_data, 'srv_stats_') === 0) {
        $srv_id = str_replace('srv_stats_', '', $callback_data);
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id")->fetch();
        if ($srv) {
            $account_index = (int)($srv['hostvds_account_index'] ?? 0);
            $auth = get_auth($account_index); $nova_url = get_endpoint($auth['catalog'], 'compute', $srv['location_api']);
            $diag_res = os_request($nova_url . "/servers/" . $srv['os_server_id'] . "/diagnostics", 'GET', $auth['token']);
            if (isset($diag_res['body']) && !empty($diag_res['body'])) {
                sendMessage("📊 <b>آمار:</b>\n\nاز دستورات <code>top</code> یا <code>htop</code> داخل سرور استفاده کنید.");
            } else {
                sendMessage("📊 مانیتورینگ در دسترس نیست.");
            }
        } exit;
    }
    
    // لیست سرور
    if ($callback_data == 'back_to_vps_list') {
        $servers = $pdo->query("SELECT * FROM servers WHERE chat_id = $chat_id AND status = 'active'")->fetchAll(); $btns = [];
        foreach ($servers as $s) { $btns[] = [['text' => "🖥 " . $s['server_name'], 'callback_data' => "srv_detail_" . $s['id']]]; }
        editMessageText($message_id, "🖥 <b>سرورهای شما:</b>", ['inline_keyboard' => $btns]); exit;
    }
    
    // منوی بازنصب
    if (strpos($callback_data, 'reinstall_menu_') === 0) {
        $srv_id = (int)str_replace('reinstall_menu_', '', $callback_data);
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id")->fetch();
        if (!$srv) exit;
        $account_index = (int)($srv['hostvds_account_index'] ?? 0);
        $auth = get_auth($account_index); $token = $auth['token'];
        $glance_url = get_endpoint($auth['catalog'], 'image', $srv['location_api']);
        $img_res = os_request($glance_url . "/v2/images?limit=10", 'GET', $token);
        $btns = [];
        foreach ($img_res['body']['images'] ?? [] as $img) {
            if (strpos(strtolower($img['name']), 'prebuilt') === false) {
                $btns[] = [['text' => "💿 " . $img['name'], 'callback_data' => "exec_reinstall_{$srv_id}_" . $img['id']]];
            }
        }
        $btns[] = [['text' => '🔙', 'callback_data' => "srv_detail_" . $srv_id]];
        editMessageText($message_id, "💿 <b>بازنصب OS:</b>\n\n⚠️ همه داده‌ها پاک می‌شود!", ['inline_keyboard' => $btns]); exit;
    }
    
    // انجام بازنصب
    if (strpos($callback_data, 'exec_reinstall_') === 0) {
        $raw = str_replace('exec_reinstall_', '', $callback_data);
        $parts = explode('_', $raw); $srv_id = (int)$parts[0]; $img_id = $parts[1];
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $srv_id AND chat_id = $chat_id AND status = 'active'")->fetch();
        if (!$srv) exit;
        $account_index = (int)($srv['hostvds_account_index'] ?? 0);
        $account_name = $navidi_accounts[$account_index]['name'] ?? "Account #$account_index";
        sendMessage("⏳ در حال بازنصب...\n🔐 $account_name");
        $auth = get_auth($account_index); $token = $auth['token'];
        $nova_url = get_endpoint($auth['catalog'], 'compute', $srv['location_api']);
        
        $new_password = generateStrongPassword(14);
        
        $rebuild_data = ["rebuild" => ["imageRef" => $img_id, "adminPass" => $new_password]];
        os_request($nova_url . "/servers/" . $srv['os_server_id'] . "/action", 'POST', $token, $rebuild_data);
        
        $pdo->prepare("UPDATE servers SET password = ? WHERE id = ?")->execute([$new_password, $srv_id]);
        
        sendMessage("✅ بازنصب آغاز شد!\n🔑 رمز جدید: <code>$new_password</code>\n\n⚠️ <b>این رمز را در جای امنی ذخیره کنید.</b>", $kb); exit;
    }
    
    // تمدید خودکار
    if (strpos($callback_data, 'toggle_renew_') === 0) { 
        $id = str_replace('toggle_renew_', '', $callback_data); 
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $id AND chat_id = $chat_id")->fetch(); 
        if ($srv) { 
            $pdo->query("UPDATE servers SET auto_renew = 1 - auto_renew WHERE id = $id"); 
            $st = ($srv['auto_renew'] == 1) ? "خاموش" : "روشن";
            sendMessage("✅ تمدید خودکار: $st"); 
        } exit; 
    }
    
    // حذف سرور - تایید
    if (strpos($callback_data, 'del_srv_req_') === 0) { 
        $id = str_replace('del_srv_req_', '', $callback_data); 
        $ikb = ['inline_keyboard' => [[['text' => '⚠️ بله، حذف شود', 'callback_data' => "del_srv_conf_" . $id]], [['text' => '🔙 انصراف', 'callback_data' => "srv_detail_" . $id]]]]; 
        sendMessage("⚠️ مطمئن هستید؟", $ikb); exit; 
    }
    
    // حذف سرور - انجام
    if (strpos($callback_data, 'del_srv_conf_') === 0) {
        $id = str_replace('del_srv_conf_', '', $callback_data); 
        $srv = $pdo->query("SELECT * FROM servers WHERE id = $id AND chat_id = $chat_id AND status = 'active'")->fetch();
        if ($srv) { 
            $account_index = (int)($srv['hostvds_account_index'] ?? 0);
            $auth = get_auth($account_index); $token = $auth['token']; 
            $nova_url = get_endpoint($auth['catalog'], 'compute', $srv['location_api']); 
            os_request($nova_url . "/servers/" . $srv['os_server_id'], 'DELETE', $token); 
            $pdo->query("UPDATE servers SET status = 'deleted' WHERE id = $id"); 
            sendMessage("🗑️ سرور حذف شد."); 
        } exit;
    }
}

// منوی ساخت سرور
if ($text == '🛒 ساخت سرور جدید') {
    $locs = $pdo->query("SELECT * FROM locations WHERE status = 1")->fetchAll();
    if(empty($locs)) { sendMessage("❌ لوکیشنی فعال نیست."); exit; }
    $btns = []; foreach($locs as $loc) { $btns[] = [['text' => "📍 " . $loc['name'], 'callback_data' => "buy_loc_" . $loc['id']]]; }
    sendMessage("🌐 <b>مرحله ۱:</b> لوکیشن:", ['inline_keyboard' => $btns]); exit;
}

// لیست سرویس ها
if ($text == '🖥 سرویس‌های من') {
    $servers = $pdo->query("SELECT * FROM servers WHERE chat_id = $chat_id AND status = 'active'")->fetchAll();
    if (empty($servers)) { sendMessage("📭 سروری ندارید."); exit; }
    $btns = [];
    foreach ($servers as $s) { $btns[] = [['text' => "🖥 " . $s['server_name'], 'callback_data' => "srv_detail_" . $s['id']]]; }
    sendMessage("🖥 <b>سرورهای شما:</b>", ['inline_keyboard' => $btns]); exit;
}

// پایان کد - موفق باشی برادر :)))
?>
