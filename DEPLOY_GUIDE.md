# Deploy Guide - InfinityFree FTP

Huong dan nay dung cho website PHP MVC hien tai trong thu muc nay. Du an nay khong phai Laravel day du vi khong co `artisan` va `composer.json`.

## 1. Cau hinh FTP

Mo file `.env.deploy` va dien mat khau FTP:

```env
FTP_HOST=ftpupload.net
FTP_USER=if0_41021694
FTP_PASS=mat_khau_ftp_cua_ban
FTP_PORT=21
FTP_REMOTE_DIR=/htdocs
DEPLOY_DOMAIN=mttqvnxatanhoa.io.vn
```

Khong commit hoac chia se file `.env.deploy`. File nay da duoc dua vao `.gitignore`.

## 2. Deploy tren macOS/Linux

Script `deploy.sh` dung `lftp`.

 Cai `lftp` neu chua co:

```bash
brew install lftp
```

Cap quyen chay script:

```bash
chmod +x deploy.sh
```

Chay deploy:

```bash
./deploy.sh
```

Script se upload source len `/htdocs` va khong xoa file tren hosting.

## 3. Deploy tren Windows

Script `deploy.ps1` dung WinSCP command line.

1. Cai WinSCP: https://winscp.net/
2. Dam bao `WinSCP.com` nam trong PATH, hoac truyen duong dan day du.
3. Mo PowerShell tai thu muc project.
4. Chay:

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy.ps1
```

Neu WinSCP khong co trong PATH:

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy.ps1 -WinScpPath "C:\Program Files (x86)\WinSCP\WinSCP.com"
```

Script se upload source len `/htdocs` va khong xoa file tren hosting.

## 4. File va thu muc bi bo qua

Ca hai script deu bo qua:

- `.git`
- `node_modules`
- `vendor`
- `.env`
- `.env.deploy`
- `storage/logs`
- `tests`
- `*.log`
- `.DS_Store`

Ly do khong upload `vendor`: tren InfinityFree thuong khong chay Composer truc tiep. Neu sau nay du an la Laravel va can `vendor`, hay chay `composer install --no-dev --optimize-autoloader` tren may local roi quyet dinh co upload `vendor` hay khong tuy goi hosting.

## 5. Luu y cho PHP MVC hien tai

Du an hien tai co file `index.php` o goc va `.htaccess` route ve `public/index.php`, vi vay upload toan bo project vao `/htdocs`.

Can upload:

- `app`
- `config`
- `public`
- `resources`
- `routes`
- `storage`
- `index.php`
- `.htaccess`

Khong can truy cap `/public` tren domain. Truy cap truc tiep:

```text
https://mttqvnxatanhoa.io.vn
```

## 6. Neu la Laravel day du

Neu sau nay project co `artisan` va `composer.json`, can kiem tra:

- Thu muc public cua Laravel nen la document root. Tren shared hosting nhu InfinityFree, thuong phai upload noi dung `public` vao `htdocs` hoac giu toan bo source ngoai public neu hosting cho phep.
- Neu upload toan bo Laravel vao `htdocs`, can sua `public/index.php` de tro dung `../vendor/autoload.php` va `../bootstrap/app.php`, hoac dung file `index.php` o goc de bootstrap vao `public`.
- Can chay local truoc khi upload:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

InfinityFree thuong khong ho tro SSH/Composer, nen nen build local roi moi upload.

## 7. Database MySQL va phpMyAdmin InfinityFree

File SQL chinh:

```text
database/mttq_tanhoa.sql
```

Quy trinh import:

1. Vao InfinityFree Control Panel.
2. Mo MySQL Databases, tao database neu chua co.
3. Ghi lai MySQL Hostname, Database Name, Username, Password.
4. Mo phpMyAdmin cua dung database.
5. Chon database.
6. Vao tab Import.
7. Chon file `database/mttq_tanhoa.sql`.
8. Bam Go/Import.
9. Sua `config/database.php` tren source host cho dung thong tin database InfinityFree.

Tren InfinityFree, MySQL host thuong khong phai `localhost` hay `127.0.0.1`; hay dung hostname trong Control Panel.

## 8. SSL va HTTPS

Domain hien tai:

```text
mttqvnxatanhoa.io.vn
```

Can bat SSL trong InfinityFree Control Panel. Source da co cau hinh ep HTTPS trong `.htaccess` va `public/index.php`.

Neu SSL chua active ma da truy cap HTTPS, trinh duyet co the bao loi bao mat. Cho SSL active roi thu lai.

## 9. Kiem tra sau deploy

Mo cac URL:

```text
https://mttqvnxatanhoa.io.vn
https://mttqvnxatanhoa.io.vn/admin
https://mttqvnxatanhoa.io.vn/organizations
https://mttqvnxatanhoa.io.vn/loan-groups
```

Neu `/admin` khong dang nhap duoc, mo:

```text
https://mttqvnxatanhoa.io.vn/test-session.php
```

F5 vai lan. Neu Session ID doi lien tuc, host/trinh duyet dang khong giu cookie.

## 10. Nguyen tac an toan

Script deploy khong dung tuy chon xoa file tren hosting. Truoc khi import database lai tren phpMyAdmin, nen backup database cu va thu muc `public/uploads`.
