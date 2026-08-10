<div align="center">

<!-- هدر گرافیکی (میتوانید لینک عکس اختصاصی خود را جایگزین کنید) -->
<img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&height=250&section=header&text=HostVDS%20Reseller%20Bot&fontSize=50&fontAlignY=38&desc=Advanced%20Telegram%20Bot%20For%20OpenStack%20VPS&descAlignY=55&descAlign=60" width="100%" />

<br>

<!-- نشان‌های تکنولوژی -->
<a href="https://php.net"><img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white"></a>
<a href="https://mysql.com/"><img src="https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white"></a>
<a href="https://www.openstack.org/"><img src="https://img.shields.io/badge/API-OpenStack-ED1944?style=for-the-badge&logo=openstack&logoColor=white"></a>
<a href="https://core.telegram.org/bots"><img src="https://img.shields.io/badge/Platform-Telegram-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white"></a>

<br><br>

**یک ربات تلگرامی فوق‌پیشرفته برای مدیریت، فروش و اتوماسیون سرورهای ابری بر بستر OpenStack**

**اکنون فقط دیتاسنتر cgi اضافه شده در آینده نیز بیشتر اراعه دهنده ها اضافه خواهند شد**

</div>

---

## ⚡️ چرا این ربات؟
این پروژه یک اسکریپت ساده نیست؛ بلکه یک **سیستم مدیریت مشتریان (CRM)** و **اتوماسیون فروش** کامل در بستر تلگرام است که به صورت مستقیم با API دیتاسنتر (OpenStack) ارتباط برقرار می‌کند. کاربر می‌تواند بدون نیاز به خروج از تلگرام، سرور بخرد، سیستم‌عامل نصب کند، آی‌پی بگیرد و سرور خود را ریبوت کند.

> **نکته:** معماری این سورس بر پایه یک فایل اصلی منسجم و یک فایل کرون‌جاب طراحی شده تا استقرار (Deployment) آن روی هر هاستی در سریع‌ترین زمان ممکن انجام شود.

---

## 💎 امکانات و قابلیت‌ها

<div align="center">
  
| 👨‍💻 پنل مدیریت (Admin) | 🛒 پنل کاربری (Client) |
| :--- | :--- |
| 🛡 **استخر اکانت‌ها:** دور زدن محدودیت 10 سرور با اتصال چند اکانت HostVDS | 🚀 **خرید آنی:** انتخاب لوکیشن، پلن و ساخت خودکار سرور در چند ثانیه |
| 💰 **مدیریت مالی:** تایید/رد فیش‌های واریزی با یک کلیک | 🔄 **مدیریت سرور:** ریبوت، روشن، خاموش و دریافت IP |
| 📊 **آمار زنده:** مشاهده وضعیت اکانت‌ها و سرورهای فعال | 🔑 **تغییر رمز عبور:** تغییر روت پسورد مستقیماً از طریق API |
| ⚙️ **داینامیک:** افزودن لوکیشن و قیمت‌گذاری از داخل ربات | 💳 **کیف پول:** شارژ حساب، انتقال موجودی و مشاهده تراکنش‌ها |
| 📢 **ارسال پیام:** سیستم اطلاع‌رسانی همگانی به کاربران | ⏱ **تمدید خودکار:** سیستم Auto-Renew برای جلوگیری از قطعی |

</div>

---

## 📸 تصاویری از محیط ربات


## 🚀 راهنمای نصب و راه‌اندازی سریع

برای شلوغ نشدن صفحه، مراحل نصب را در لیست کشویی زیر قرار داده‌ایم. روی آن کلیک کنید:

<details>
<summary><b>🛠 نمایش مراحل نصب قدم‌به‌قدم</b></summary>

<br>

**قدم اول: آماده‌سازی دیتابیس**
یک دیتابیس MySQL بسازید. نیازی به ایمپورت جداول به صورت دستی نیست، جداول کلیدی در صورت عدم وجود به صورت خودکار پیکربندی می‌شوند.

**قدم دوم: ویرایش کانفیگ‌ها**
فایل `bot.php` را باز کرده و اطلاعات زیر را جایگذاری کنید:
```php
$BOT_TOKEN  = "YOUR_TELEGRAM_BOT_TOKEN"; 
$ADMIN_ID   = 123456789; // Telegram User ID

قدم سوم: اتصال اکانت HostVDS
در متغیر $navidi_accounts اطلاعات لاگین دیتاسنتر خود را وارد کنید:
array(
    'name'       => 'Account 1',
    'auth_url'   => "[https://os-api.hostvds.com/identity/v3/auth/tokens](https://os-api.hostvds.com/identity/v3/auth/tokens)",
    'user_id'    => "...",
    'password'   => "...",
    'project_id' => "...",
    'max_servers'=> 10,
    'status'     => 1,
)

قدم چهارم: تنظیم Webhook و Cronjob
آدرس ربات خود را به تلگرام متصل کنید و دستور زیر را برای اجرای هر ۲ دقیقه در سرور لینوکسی خود قرار دهید:
*/2 * * * * /usr/local/bin/php -q /path/to/cron.php >/dev/null 2>&1

</details>
🧩 معماری فنی (Technical Stack)
 * State Machine: مدیریت مراحل گفتگو با استفاده از فیلدهای JSON در دیتابیس بدون درگیری با Session.
 * Cloud-Init: استفاده از cloud-config برای تزریق کانفیگ و پسورد به سرورهای اوبونتو هنگام Build.
 * API Wrapper: توابع اختصاصی cURL برای هندل کردن توکن‌های OpenStack و جلوگیری از Expire شدن آن‌ها.
🛡 امنیت و هشدارها
> [!WARNING]
> اطلاعات حساس شما (پسورد دیتاسنتر و دیتابیس) درون فایل‌ها قرار دارد. حتماً مطمئن شوید که قابلیت Directory Listing روی هاست شما غیرفعال است تا کسی نتواند فایل‌های شما را ببیند.
> 
<div align="center">


<sub>توسعه و بهینه‌سازی توسط تیم <b>Net Visit</b></sub>



</div>

