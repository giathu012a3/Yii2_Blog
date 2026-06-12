# Yii2 Blog REST API

REST API Blog được phát triển bằng framework Yii2, tích hợp xác thực Bearer Token, phân quyền người dùng RBAC, cùng các tính năng cốt lõi bao gồm Bài viết, Danh mục, Nhãn, Bình luận và Thích bài viết.

---

## 1. Các bước cài đặt

### Yêu cầu hệ thống

* PHP >= 8.2
* MySQL / MariaDB
* Composer

### Cài đặt

1. Tải mã nguồn dự án

```bash
git clone <repository-url> yii2-blog
cd yii2-blog
```

2. Cài đặt các thư viện phụ thuộc

```bash
composer install
```

3. Cấu hình môi trường

Tạo tệp `.env` từ `.env.example`:
- Windows Command Prompt (CMD): `copy .env.example .env`
- Windows PowerShell: `Copy-Item .env.example .env`
- Linux / macOS: `cp .env.example .env`

Mở file `.env` và cập nhật các cấu hình sau:

* **Tạo Database:** Tạo một database trống trong MySQL/MariaDB (ví dụ đặt tên là `yii2-blog`).
* **Cấu hình kết nối Database:**
  ```env
  DB_HOST=localhost
  DB_NAME=yii2-blog
  DB_USERNAME=root
  DB_PASSWORD=
  ```
* **Khởi tạo Cookie Validation Key:** Điền một chuỗi ngẫu nhiên bí mật vào trường `COOKIE_VALIDATION_KEY`. Đây là cấu hình bắt buộc để ứng dụng Yii2 khởi chạy ổn định.

4. Chạy migration và Seed dữ liệu

Hãy chạy các câu lệnh sau để khởi tạo cấu trúc bảng cơ sở dữ liệu. Dữ liệu mẫu (Role, Permission và 4 tài khoản thử nghiệm mặc định) đã được tích hợp sẵn dưới dạng seeder nằm trong các tệp tin migrations:

```bash
php yii migrate --migrationPath=@yii/rbac/migrations --interactive=0
php yii migrate --interactive=0
```

> **Lưu ý (Nếu muốn Reset Database):** Nếu bạn muốn xóa sạch toàn bộ các bảng cũ để làm mới CSDL từ đầu, hãy chạy lần lượt:
> ```bash
> php yii migrate/fresh --migrationPath=@yii/rbac/migrations --interactive=0
> php yii migrate --interactive=0
> ```

5. Khởi chạy ứng dụng

```bash
php yii serve
```

Địa chỉ truy cập mặc định:

```
http://localhost:8080
```

---

## 2. Ma trận Phân quyền (RBAC)

| Vai trò (Role) | Xem bài viết | CRUD Danh mục | CRUD Nhãn (Tag) | Tạo bài viết | Quản lý bài viết của mình | Quản lý mọi bài viết | Thích bài viết | Bình luận | Quản lý bình luận |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| Guest | Chỉ bài đã xuất bản | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Reader | Chỉ bài đã xuất bản | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ | Chỉ bình luận của mình |
| Author | Bài đã xuất bản + bài nháp của mình | ❌ | ❌ | ✅ | ✅ | ❌ | ✅ | ✅ | Bình luận của mình + bình luận trên bài mình |
| Admin | Tất cả bài viết | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | Toàn quyền quản trị |

---

## 3. Danh sách API Endpoints

### Xác thực (Authentication)

#### Đăng ký tài khoản
```
POST /api/auth/register
```
```bash
curl -X POST http://localhost:8080/api/auth/register \
-H "Content-Type: application/json" \
-d '{
  "username":"newuser",
  "email":"newuser@example.com",
  "password":"User@123",
  "password_confirmation":"User@123"
}'
```

#### Đăng nhập
```
POST /api/auth/login
```
```bash
curl -X POST http://localhost:8080/api/auth/login \
-H "Content-Type: application/json" \
-d '{
  "username":"admin",
  "password":"Admin@123"
}'
```

#### Đăng xuất
```
POST /api/auth/logout
```
```bash
curl -X POST http://localhost:8080/api/auth/logout \
-H "Authorization: Bearer <token>"
```

#### Thông tin cá nhân
```
GET /api/auth/me
```
```bash
curl -X GET http://localhost:8080/api/auth/me \
-H "Authorization: Bearer <token>"
```

---

### Bài viết (Posts)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| GET | /api/posts | Lấy danh sách bài viết |
| POST | /api/posts | Tạo bài viết mới |
| PUT | /api/posts/{id} | Cập nhật bài viết |
| DELETE | /api/posts/{id} | Xóa bài viết |
| POST | /api/posts/{id}/like | Thích / Bỏ thích bài viết |

Ví dụ tạo bài viết:
```bash
curl -X POST http://localhost:8080/api/posts \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
  "category_id": 1,
  "title": "My Post",
  "description": "Post description",
  "content": "Hello Yii2",
  "status": 1,
  "tag_list": ["yii2", "php"]
}'
```

---

### Danh mục (Categories)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| GET | /api/categories | Lấy danh sách danh mục (Chỉ Admin) |
| POST | /api/categories | Tạo danh mục mới (Chỉ Admin) |
| PUT | /api/categories/{id} | Cập nhật danh mục (Chỉ Admin) |
| DELETE | /api/categories/{id} | Xóa danh mục (Chỉ Admin) |

Ví dụ tạo danh mục:
```bash
curl -X POST http://localhost:8080/api/categories \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
  "name": "Technology"
}'
```

---

### Nhãn (Tags)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| GET | /api/tags | Lấy danh sách nhãn (Chỉ Admin) |
| POST | /api/tags | Tạo nhãn mới (Chỉ Admin) |
| PUT | /api/tags/{id} | Cập nhật nhãn (Chỉ Admin) |
| DELETE | /api/tags/{id} | Xóa nhãn (Chỉ Admin) |

**Lưu ý:**
Author có thể truyền `tag_list` khi tạo hoặc cập nhật bài viết. Nếu một Tag chưa tồn tại, hệ thống sẽ tự động tạo Tag đó và liên kết với bài viết. Đây không được xem là quyền quản lý trực tiếp tài nguyên Tag.

Ví dụ tạo nhãn:
```bash
curl -X POST http://localhost:8080/api/tags \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
  "name": "yii2"
}'
```

---

### Bình luận (Comments)

| Phương thức | Endpoint | Mô tả |
| :--- | :--- | :--- |
| POST | /api/posts/{post_id}/comments | Tạo hoặc phản hồi bình luận (Yêu cầu đăng nhập) |
| PUT | /api/comments/{id} | Cập nhật bình luận (Chủ bình luận hoặc Admin) |
| DELETE | /api/comments/{id} | Xóa bình luận (Chủ bình luận, Tác giả bài viết hoặc Admin) |
| POST | /api/comments/{id}/hide | Ẩn bình luận (Tác giả bài viết hoặc Admin) |

Ví dụ tạo bình luận:
```bash
curl -X POST http://localhost:8080/api/posts/1/comments \
-H "Authorization: Bearer <token>" \
-H "Content-Type: application/json" \
-d '{
  "content": "This is a comment",
  "parent_id": null
}'
```

---

## 4. Sơ đồ ERD

Sơ đồ quan hệ thực thể cơ sở dữ liệu:

https://drive.google.com/file/d/12jby51hJwU11mPKhxoqBWM-sI0slSl2f/view?usp=sharing

---

## 5. Kiểm thử & Tài khoản mẫu

### Tài khoản thử nghiệm mặc định
Các tài khoản sau được tự động nạp sẵn khi chạy lệnh migrations:
* **Admin:** `admin` / `Admin@123`
* **Author 1:** `author1` / `Author@123`
* **Author 2:** `author2` / `Author@123`
* **Reader:** `reader` / `Reader@123`

### Postman Collection
Import tệp tin kiểm thử Postman sau để chạy test:
```
docs/yii2_blog_postman_collection.json
docs/yii2_blog_postman_environment.json
```
Sau khi import xong thì chọn đúng enviroment: Yii2 Blog - Local Development
Sau đó run collection: Yii2 Blog API

---Lưu ý----
Nếu chạy thủ công: Hãy sử dụng chuỗi Bearer Token nhận được từ API Đăng nhập (`POST /api/auth/login`) gắn vào phần cấu hình `Authorization` cho các API yêu cầu xác thực.
