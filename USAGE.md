# 🖥️ Hướng dẫn sử dụng - ComputerLab MVC

Phiên bản hướng dẫn này là một bản hướng dẫn tuần tự bắt buộc: khi ai đó tải repository này về, hãy *thực hiện theo các bước theo đúng thứ tự* (bạn có thể **thêm** ghi chú hoặc bước phụ trợ, nhưng **không được bỏ bớt** các bước cốt lõi).

Mục tiêu: giúp người mới dựng và chạy dự án bằng Docker, import dữ liệu mẫu, và biết nơi cấu hình biến môi trường.

## 📋 Yêu cầu trước khi bắt đầu
- Git (để clone repo)
- Docker và Docker Compose (Docker Desktop trên Windows/Mac hoặc Docker Engine + Compose)
- Ít nhất 4GB RAM cho container (tùy quy mô dữ liệu)

> Ghi chú: không cần cài PHP/MySQL/Nginx/Composer trên máy thật nếu dùng Docker theo hướng dẫn.

---

## 🚀 Hướng dẫn cài đặt và chạy (bắt buộc, theo thứ tự)

> Lưu ý: các bước sau đây là bắt buộc và phải chạy theo thứ tự — bạn có thể **thêm** bước phụ trợ nhưng **không được bỏ** bước cốt lõi.

### Bước 1 — Lấy mã nguồn
Mở Terminal (PowerShell trên Windows) và chạy:

```bash
git clone https://github.com/lawnguyenit/ComputerLab-MVC.git
cd ComputerLab-MVC
```

### Bước 2 — Tạo file cấu hình môi trường `.env`
Nhân bản file ví dụ `.env.example` thành `.env` (bắt buộc):

PowerShell (Windows):
```powershell
Copy-Item .env.example .env
```

Linux / Git Bash / macOS:
```bash
cp .env.example .env
```

Mở `.env` và xác nhận các biến quan trọng sau đã đúng với cấu hình Docker (tên service, database, user, password):
- `DB_CONNECTION` (mặc định: `mysql`)
- `DB_HOST` (trong Docker Compose thường là `laravel_db`)
- `DB_PORT` (mặc định: `3306`)
- `DB_DATABASE` (mặc định: `laravel_db`)
- `DB_USERNAME` / `DB_PASSWORD`

> Không commit file `.env` chứa thông tin nhạy cảm lên Git.

### Bước 3 — Khởi động Docker stack
Khởi tạo và build các container (bắt buộc):

```bash
docker-compose up -d --build
```

Chờ đến khi container khởi động xong trước khi tiếp tục (dùng `docker ps` để kiểm tra).

### Bước 4 — Cài dependencies và cấu hình application
Chạy các lệnh trong container ứng dụng Laravel (mặc định service name là `laravel_app`):

```bash
docker exec -it laravel_app composer install
docker exec -it laravel_app php artisan key:generate
```

### Bước 5 — Import database mẫu
Import file SQL mẫu (tệp `quanlyphongmaytinh.sql` đã có trong repository) vào container database (mặc định user/password theo `docker-compose`):

```bash
docker exec -i laravel_db mysql -uuser -p123123 laravel_db < quanlyphongmaytinh.sql
```

Thay `user` / `123123` bằng giá trị bạn đã cấu hình trong `.env` / `docker-compose.yaml` nếu bạn đã chỉnh sửa.

### Bước 6 — Migrate & Seed
Chạy migration và seed (bắt buộc):

```bash
docker exec -it laravel_app php artisan migrate --seed
```

### Bước 7 — Phân quyền thư mục storage và cache
Thiết lập quyền để webserver trong container có thể ghi:

```bash
docker exec -it laravel_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
```

### Bước 8 — Kiểm tra ứng dụng
Truy cập ứng dụng trên trình duyệt tại `http://localhost:8080` hoặc cổng mà `docker-compose.yaml` đã expose (kiểm tra file `docker-compose.yaml`).

---

## Phần cấu hình chi tiết và các lưu ý quan trọng
- **Không bỏ qua bất kỳ bước nào ở phần trên** — mọi bước đều bắt buộc để ứng dụng hoạt động đúng.
- Bạn có thể thêm các bước phụ (ví dụ: cài thêm tools, cron jobs), nhưng không được xóa các bước cốt lõi.

### Biến môi trường (.env)
File `.env.example` chứa cấu trúc các biến môi trường. Ví dụ mẫu:

```ini
APP_NAME=ComputerLab
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=laravel_db
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=user
DB_PASSWORD=123123
```

Sau khi chỉnh `.env`, nếu bạn đang caching config trong Laravel, chạy:

```bash
docker exec -it laravel_app php artisan config:clear
docker exec -it laravel_app php artisan cache:clear
```

### Cập nhật dependencies
Nếu cần cập nhật package PHP:

```bash
docker exec -it laravel_app composer update
```

---

## Chạy test & kiểm tra chất lượng (tùy chọn nhưng khuyến nghị)
- Chạy unit/integration tests (nếu có):

```bash
docker exec -it laravel_app ./vendor/bin/phpunit
```

- Chạy static analysis (nếu cấu hình): `phpstan`/`psalm` theo thiết lập của dự án.

---

## Xử lý sự cố phổ biến
- Nếu container `laravel_app` không khởi động: kiểm tra logs
```bash
docker logs laravel_app
```
- Nếu lỗi kết nối DB: kiểm tra `DB_HOST` trong `.env` và trạng thái container DB `docker ps`.
- Nếu migration bị fail: kiểm tra lỗi chi tiết trong logs và rollback migration nếu cần.

## Bảo mật & vận hành
- Tuyệt đối không commit `.env` với mật khẩu thật.
- Backup database thường xuyên trước khi chạy migration lớn.
- Sử dụng staging environment để test release trước production.
