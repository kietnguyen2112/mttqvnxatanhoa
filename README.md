# MTTQ Tan Hoa MVC PHP

Du an trang thong tin dien tu MTTQ xa Tan Hoa, to chuc theo mo hinh MVC bang PHP va MySQL.

## Cau hinh database

1. Mo phpMyAdmin.
2. Tao hoac chon database can dung, sau do import file `database/mttq_tanhoa.sql`.
3. Neu tai khoan MySQL khac mac dinh, sua `config/database.php` hoac dat bien moi truong:
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`

Neu da import database truoc do, co the import lai `database/mttq_tanhoa.sql` de lam moi database theo ban chuc nang rut gon hien tai.

## Chay local

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

Sau do mo:

- Trang chu: `http://127.0.0.1:8000`
- To chuc thanh vien: `http://127.0.0.1:8000/organizations`
- To vay von: `http://127.0.0.1:8000/loan-groups`
- Van ban: `http://127.0.0.1:8000/documents`
- Quan tri: `http://127.0.0.1:8000/admin`

Tai khoan quan tri duoc luu trong bang `admin_users` va mat khau duoc hash bang `password_hash`.
Sau khi import database, dang nhap `/admin` va doi mat khau ngay tai `http://127.0.0.1:8000/admin/password`.
Neu chua co bang `admin_users`, co the dat bien moi truong `ADMIN_PASSWORD` tam thoi, sau do tao/import tai khoan admin va xoa bien nay tren hosting.

## Deploy InfinityFree

1. Trong control panel InfinityFree, tao MySQL database va ghi lai `MySQL Hostname`, ten database, username va password.
2. Vao phpMyAdmin cua dung database vua tao, chon database do roi import `database/mttq_tanhoa.sql`. File SQL khong tu tao database, phu hop voi quyen tren shared hosting.
3. Sua `config/database.php` truoc khi upload:
   - `host`: dung MySQL Hostname trong control panel, thuong co dang `sqlXXX.infinityfree.com`; khong dung `127.0.0.1` hoac `localhost`.
   - `database`: dung day du ten database co tien to tai khoan.
   - `username` va `password`: dung thong tin database InfinityFree.
4. Neu muon an/hien chuc nang bai dang/tin tuc, sua truc tiep file `config/app.php`:
   - An module: `'post_module_enabled' => false`
   - Hien module: `'post_module_enabled' => true`
5. Upload `app`, `config`, `public`, `resources`, `routes`, `storage`, `index.php` va `.htaccess` vao thu muc `htdocs`. Thu muc `database` chi dung de import va khong can upload len web.
6. Truy cap domain chinh. File `index.php` o goc va `.htaccess` chuyen route ve `public/index.php`, nen khong truy cap `/public`.
7. Dang nhap `/admin`, sau do doi mat khau ngay tai `/admin/password`. Khong giu mat khau tam thoi trong bien `ADMIN_PASSWORD` sau khi da co tai khoan admin trong database.

Toi uu va bao ve da bat san cho hosting:

- CSS, JavaScript va anh tinh duoc cache; HTML/CSS/JavaScript duoc nen neu may chu bat module Apache tuong ung.
- URL CSS va JavaScript co phien ban tu dong theo file, tranh giao dien cu bi giu lai sau khi upload ban moi.
- Anh tinh dung ten file ASCII chu thuong (`logo-mttq.png`, `hoi-phu-nu.png`, ...), tranh loi phan biet hoa/thuong va chuan hoa Unicode tren may chu Linux.
- URL `/uploads/...` duoc dua dung ve `public/uploads/...`; tep script trong thu muc upload bi chan.
- Thu muc ma nguon, cau hinh va storage bi chan truy cap truc tiep boi `.htaccess`.
- Form quan tri co ma CSRF va cookie phien co `HttpOnly`/`SameSite`.

### An/hien module bai dang tren host

Module bai dang/tin tuc duoc giu nguyen code va database, nhung co the an bang cau hinh PHP trong `config/app.php`:

```php
'post_module_enabled' => false,
```

Khi bien nay la `false`:

- Menu `Bai dang` o frontend va admin bi an.
- Block bai dang o trang chu bi an.
- Ket qua tim kiem nhom bai dang bi an.
- Cac URL `/posts`, `/posts/show`, `/tin-tuc/{slug}` va `/admin/posts` tra ve `404`.
- Du lieu trong bang `posts`, anh trong `public/uploads/posts` va code module khong bi xoa.

Khi can bat lai, sua `config/app.php`:

```php
'post_module_enabled' => true,
```

Sau do upload lai file `config/app.php` va tai lai trang. Du an PHP MVC hien tai khong can chay `php artisan`.

Neu gap `403 Forbidden`, kiem tra:

- `htdocs/index.php` da ton tai.
- `htdocs/.htaccess` da duoc upload dung ten, co dau cham o dau file.
- Khong upload rieng thu muc `public` ma bo mat cac thu muc `app`, `routes`, `resources`, `config`.

Neu gap loi ket noi database, kiem tra lai `host` tu MySQL Databases trong control panel; tren InfinityFree, `localhost` khong phai hostname database cua tai khoan free.

Luu y gioi han tep: InfinityFree cong bo gioi han 1 MB cho tep `.php`/`.html`, 10 kB cho `.htaccess` va 10 MB cho cac tep khac. Cac tep hien co trong du an nam duoi gioi han nay.

Thu muc `database` chi co mot file import chinh la `database/mttq_tanhoa.sql`. File nay da gom schema, du lieu Ban Cong tac Mat tran ap, cac chi hoi Phu nu va duong dan anh tinh dung ten ASCII.

Anh va van ban tai len tu trang quan tri duoc luu tren o dia tai `public/uploads/avatars` va `public/uploads/documents`, khong nam trong file SQL. Khi cap nhat source tren host:

- Khong xoa hoac ghi de cac thu muc tai len neu trong do co tep dang su dung.
- Backup ca database va `public/uploads` truoc khi thay doi lon.
- Khi chuyen host, upload lai thu muc `public/uploads` cung voi import database, neu khong cac tep da tai len se mat du tren bang database van con duong dan.

## Chuc nang da co

- Trang chu hien thi trang gioi thieu MTTQVN xa.
- Trang gioi thieu MTTQVN xa voi Chu tich va 4 Pho Chu tich kiem phu trach cac hoi.
- Trang to chuc thanh vien gom 4 hoi, lanh dao cap xa va thanh vien cac hoi cap ap.
- Trang to vay von gom to truong, hoi quan ly, ap phu trach va thanh vien tung to.
- Trang van ban cho phep tra cuu, xem truoc PDF va tai xuong cac tep dinh kem PDF, DOC, DOCX, XLS, XLSX da cong bo.
- Quan tri them/xoa lanh dao hoi, thanh vien cap ap, to vay von va thanh vien to vay von.
- Trang quan tri co giao dien rieng, chia thanh cac trang con va co chuc nang doi mat khau.
- Trang quan tri co muc tai len nhieu tep dinh kem cho tung van ban, quan ly va xoa van ban; gioi han moi tep tai len la 10 MB.
- Trang quan tri co chuc nang nhap du lieu tu file `.xlsx` tai `http://127.0.0.1:8000/admin/import`.

## Nhap du lieu XLSX

Dang nhap quan tri, vao muc `Nhap Excel`, chon loai du lieu va tai file `.xlsx`.
Dòng dau tien cua sheet phai la tieu de cot. Cac nhom cot ho tro:

- Lanh dao: `organization, full_name, position, phone, email, sort_order`
- Ho so cap ap (gom thanh vien chi doan, chi hoi va Ban Cong tac Mat tran): `organization, hamlet_name, full_name, birth_date, role, phone, note, sort_order`
- To vay von: `organization, hamlet_name, name, leader_name, leader_phone, fund_source, note`
- Thanh vien to vay von: `loan_group, full_name, role, phone, loan_amount, outstanding_amount, overdue_amount, purpose, note, sort_order`

Cot `organization` co the dung ID, ten day du, ten viet tat hoac slug cua hoi. Cot `loan_group` co the dung ID hoac ten to vay von.

Ghi chu: May hien tai chua co `composer` va Laravel CLI, nen du an duoc to chuc theo mo hinh MVC Laravel-style bang PHP thuan de chay ngay voi phpMyAdmin/MySQL. Khi can nang cap thanh Laravel chinh thuc, co the dung database va view/controller hien co de dua vao Laravel project.
