# تست‌های پکیج teksite/icon-laravel (استاندارد PHPUnit + Orchestra Testbench)

این تست‌ها به روش رسمی و متداول برای تست پکیج‌های لاراول نوشته شده‌اند — همان
روشی که پکیج‌های شناخته‌شده (مثل پکیج‌های Spatie) استفاده می‌کنند: **PHPUnit +
Orchestra Testbench**. هیچ استاب یا mock دستی از هسته‌ی لاراول استفاده نشده؛
همه‌چیز از facade های واقعی لاراول (`Cache`, `Config`, `File`, `Log`, `Blade`)
و رندر واقعی Blade استفاده می‌کند.

## نصب و اجرا

```bash
composer install
composer test              # همه تست‌ها
composer test-unit         # فقط Unit
composer test-feature      # فقط Feature
composer test-coverage     # با گزارش پوشش (نیاز به xdebug/pcov)
```

یا مستقیم:

```bash
vendor/bin/phpunit
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
```

## نکته مهم درباره‌ی اجرا در این محیط ساخت

این تست‌ها **در این سندباکس اجرا نشده‌اند**، چون `packagist.org` و
`repo.packagist.org` در این محیط مسدود هستند و نصب `orchestra/testbench` و
`laravel/framework` از طریق Composer ممکن نبود (حتی composer.phar را مستقیم
از GitHub Releases دانلود کردم، اما resolve وابستگی‌ها نیاز به دسترسی به
`repo.packagist.org` دارد که در این سندباکس بسته است).

برای جبران این محدودیت:

- تمام فایل‌های PHP با `php -l` از نظر سینتکس بررسی شدند (بدون خطا).
- هر API لاراولی که استفاده شده (`ServiceProvider::pathsToPublish()`,
  `Blade::render()`, `Log::spy()`, `Cache` با درایور `array`,
  `$this->artisan('vendor:publish')`, `$app['view']->addLocation()`) یک متد
  مستند و پایدار در چارچوب لاراول است و دقیقاً طبق مستندات رسمی و الگوی
  متداول تست پکیج‌ها به‌کار رفته.
- منطق هر تست به‌صورت دستی، خط‌به‌خط، در برابر کد واقعی `src/` بررسی و تطبیق
  داده شده (چه چیزی resolve می‌شود، چه attributeای merge می‌شود، چه زمانی
  log ثبت می‌شود و غیره).

روی سیستم خودتان با یک `composer install` معمولی، این تست‌ها باید بدون نیاز
به هیچ تغییری اجرا شوند.

## ساختار

```
tests/
├── TestCase.php                       # کلاس پایه؛ اپ لاراول واقعی را با Testbench بوت می‌کند
├── Unit/
│   ├── CacheManagerTest.php           # CacheManager به‌تنهایی (facade واقعی Cache/Config)
│   ├── IconManagerLoadingTest.php     # بارگذاری JSON، اولویت مسیرها، فیلترینگ ورودی نامعتبر
│   ├── IconManagerRenderingTest.php   # getIcon/getAll/hasIcon، escape کردن، حالت not-found
│   └── IconManagerCachingTest.php     # رفتار واقعی کش (بدون mock کردن File)
└── Feature/
    ├── IconBladeComponentTest.php     # رندر واقعی <x-icon> با Blade::render()
    ├── TekIconBladeComponentTest.php  # رندر واقعی <x-tkicon>
    ├── ServiceProviderTest.php        # register/boot/publish + اجرای واقعی vendor:publish
    └── PackageResourcesTest.php       # سلامت outline.json/solid.json واقعی + رندر end-to-end
```

## چرا `Unit/` هم به یک اپ لاراول واقعی نیاز دارد؟

کلاس‌های `IconManager` و `CacheManager` مستقیماً از هلپرهای `config()` و از
facade های `Cache`/`File`/`Log` استفاده می‌کنند که فقط داخل یک اپلیکیشن
بوت‌شده کار می‌کنند. به همین دلیل حتی تست‌های "Unit" از `Orchestra\Testbench`
ارث می‌برند — این دقیقاً همان الگویی است که مستندات رسمی توسعه‌ی پکیج لاراول
توصیه می‌کند؛ تفاوت Unit با Feature در این پروژه در **دامنه‌ی تست** است نه در
اینکه اپ بوت می‌شود یا نه:

- **Unit** → فقط یک کلاس را مستقیم می‌سازد و رفتارش را بررسی می‌کند
  (`new IconManager()`، `CacheManager::isCacheEnabled()`), بدون کامپوننت
  Blade یا ServiceProvider.
- **Feature** → مسیر کامل و end-to-end: تگ‌های Blade واقعی
  (`<x-icon>`, `<x-tkicon>`)، سیم‌کشی container، و دستور آرتیزان
  `vendor:publish`.

## نکته‌ی مستندشده (نه شکست تست)

`src/resources/solid.json` در حال حاضر خالی است (`{}`). تست مربوطه
(`PackageResourcesTest::test_the_bundled_solid_json_is_valid_though_it_currently_ships_no_icons`)
به‌جای شکست خوردن، با `markTestIncomplete()` این موضوع را به‌صراحت گزارش
می‌کند — یعنی `type="solid"` فعلاً هیچ آیکونی برای رندر ندارد.
