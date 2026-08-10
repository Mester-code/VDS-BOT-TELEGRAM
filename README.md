برای اینکه صفحه گیت‌هاب پروژه‌تان ظاهری کاملاً حرفه‌ای، چشم‌نواز و به قول معروف «ناب» داشته باشد، باید از ترکیب Markdown و تگ‌های مجاز HTML (مثل <div align="center">، <details> و نشان‌ها یا Badges) استفاده کنیم.
در این نسخه، از بنرهای گرافیکی داینامیک، نشان‌های تکنولوژی (Shields)، لیست‌های کشویی (برای شلوغ نشدن صفحه) و جداول زیبا استفاده شده است تا در رابط کاربری وبِ گیت‌هاب به بهترین شکل رندر شود.
کد زیر را کپی کرده و دقیقاً داخل فایل README.md خود پیست کنید:
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

[گزارش باگ](https://github.com/YOUR_USERNAME/YOUR_REPO/issues) · [درخواست قابلیت جدید](https://github.com/YOUR_USERNAME/YOUR_REPO/issues)

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

<div align="center">
  <img src="https://via.placeholder.com/250x450.png?text=User+Panel+Screenshot" width="250" alt="منوی کاربری">
  &nbsp;&nbsp;&nbsp;&nbsp;
  <img src="https://via.placeholder.com/250x450.png?text=Server+Management" width="250" alt="مدیریت سرور">
  <br>
  <i>(اسکرین‌شات‌های ربات خود را جایگزین لینک‌های بالا کنید)</i>
</div>

---

## 🚀 راهنمای نصب و راه‌اندازی سریع

برای شلوغ نشدن صفحه، مراحل نصب را در لیست کشویی زیر قرار داده‌ایم. روی آن کلیک کنید:

<details>
<summary><b>🛠 نمایش مراحل نصب قدم‌به‌قدم</b></summary>

<br>

**قدم اول: آماده‌سازی دیتابیس**
یک دیتابیس MySQL بسازید. نیازی به ایمپورت جداول به صورت دستی نیست، جداول کلیدی در صورت عدم وجود به صورت خودکار پیکربندی می‌شوند (یا فایل `.sql` همراه سورس را ایمپورت کنید).

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
 * State Machine: مدیریت مراحل گفتگو (ارسال فیش، انتخاب پلن) با استفاده از فیلدهای JSON در دیتابیس بدون درگیری با Session.
 * Cloud-Init: استفاده از cloud-config برای تزریق کانفیگ و پسورد به سرورهای اوبونتو هنگام Build.
 * API Wrapper: توابع اختصاصی cURL برای هندل کردن توکن‌های OpenStack و جلوگیری از Expire شدن آن‌ها.
🛡 امنیت و هشدارها
> [!WARNING]
> اطلاعات حساس شما (پسورد دیتاسنتر و دیتابیس) درون فایل‌ها قرار دارد. حتماً مطمئن شوید که قابلیت Directory Listing روی هاست شما غیرفعال است تا کسی نتواند فایل‌های شما را ببیند.
> 
<div align="center">


<sub>ساخته شده با ❤️ توسط تیم <b>Net Visit</b></sub>


<a href="https://net-visit.ir">
<img src="https://img.shields.io/badge/Website-Net_Visit-000000?style=flat-square&logo=google-chrome&logoColor=white" />
</a>
</div>

### چند نکته برای شخصی‌سازی نهایی در گیت‌هاب:
1. **لینک‌های اسکرین‌شات:** در بخش `📸 تصاویری از محیط ربات` دو تا لینک عکس آزمایشی گذاشتم (`via.placeholder.com`). کافیست از محیط ربات خود در گوشی دو تا اسکرین‌شات بگیرید، در یک Issue گیت‌هاب آپلود کنید (تا لینک مستقیم بگیرید) و جایگزین آن‌ها کنید.
2. **لینک ریپازیتوری:** در بخش بالا که نوشته `YOUR_USERNAME/YOUR_REPO` حتماً یوزرنیم و اسم مخزن گیت‌هاب خودتان را جایگزین کنید تا لینک‌های گزارش باگ کار کنند.
3. **هدر سایت:** بنر بالای صفحه به صورت کاملاً اتوماتیک با کیفیت بالا و افکت موج رندر می‌شود و جذابیت بصری مخزن شما را به شدت بالا می‌برد.

