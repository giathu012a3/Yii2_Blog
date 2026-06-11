# Yii2 Blog System with AI Assistant - REST API

Dự án này là hệ thống REST API Blog được xây dựng bằng framework Yii2, tích hợp cơ chế xác thực Bearer Token, phân quyền RBAC toàn diện, các nghiệp vụ Core Blog (Post, Category, Tag, Comment, Like).

---

## 1. Hướng dẫn thiết lập (Setup Steps)

### Yêu cầu hệ thống:
- PHP >= 8.2 (Khuyên dùng PHP 8.2.x của Laragon)
- MySQL / MariaDB
- Composer

### Các bước cài đặt:

1. **Clone dự án & Truy cập thư mục:**
   ```bash
   cd d:/APP/laragon/laragon6/www/yii2-blog
   ```

2. **Cài đặt các gói phụ thuộc (Dependencies):**
   ```bash
   composer install
   ```

3. **Cấu hình môi trường (`.env`):**
   Sao chép file cấu hình mẫu và chỉnh sửa các thông tin kết nối Database của bạn:
   ```bash
   cp .env.example .env
   ```
   *Mở file `.env` ra điền thông tin `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD` cho khớp với môi trường của bạn.*

4. **Tạo Database:**
   Tạo một cơ sở dữ liệu trống trong MySQL (ví dụ: `yii2-blog`).

5. **Chạy Migrations khởi tạo bảng:**
   ```bash
   php yii migrate/fresh --interactive=0
   ```
   *Lưu ý: Nếu sử dụng Laragon trên Windows và `php` chưa được cấu hình môi trường toàn cục, hãy chạy bằng đường dẫn tuyệt đối:*
   ```bash
   D:\APP\laragon\laragon6\bin\php\php-8.2.10-Win32-vs16-x64\php.exe yii migrate/fresh --interactive=0
   ```

6. **Chạy các bảng RBAC gốc:**
   ```bash
   D:\APP\laragon\laragon6\bin\php\php-8.2.10-Win32-vs16-x64\php.exe yii migrate --migrationPath=@yii/rbac/migrations --interactive=0
   ```

7. **Chạy migrations dự án (để seed và gán quyền RBAC):**
   ```bash
   D:\APP\laragon\laragon6\bin\php\php-8.2.10-Win32-vs16-x64\php.exe yii migrate --interactive=0
   ```

8. **Kiểm thử hệ thống:**
   Bạn có thể import file [Postman Collection](file:///d:/APP/laragon/laragon6/www/yii2-blog/docs/yii2_blog_postman_collection.json) vào Postman để test trực tiếp tất cả các API.

---

## 2. Ma trận Phân quyền (Role/Permission Matrix)

| Vai trò (Role) | Thao tác cá nhân (Auth) | Quản lý Category (CRUD) | Tạo bài viết (Post) | Xem bài viết (Draft/Published) | Sửa/Xóa Post của mình | Sửa/Xóa Post người khác | Like bài viết | Viết Bình luận | Sửa/Xóa Bình luận của mình | Ẩn Bình luận bất kỳ | Xóa bình luận bất kỳ |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Guest** | Đăng nhập/Đăng ký | ❌ | ❌ | Chỉ bài viết đã xuất bản | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Reader** | Lấy Profile / Logout | ❌ | ❌ | Chỉ bài viết đã xuất bản | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Author** | Lấy Profile / Logout | ❌ | ✅ | Bài đã xuất bản + Bài nháp của mình | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ (Chỉ trên post của mình) | ❌ |
| **Admin** | Lấy Profile / Logout | ✅ | ✅ | Xem toàn bộ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 3. Danh sách Endpoints & Ví dụ cURL

### 3.1 Authentication

#### Đăng ký tài khoản (Reader mặc định)
- **Endpoint:** `POST /api/auth/register`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/auth/register \
    -H "Content-Type: application/json" \
    -d '{"username": "newreader", "email": "reader@example.com", "password": "Reader@123", "password_confirmation": "Reader@123"}'
  ```

#### Đăng nhập
- **Endpoint:** `POST /api/auth/login`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{"username": "admin", "password": "Admin@123"}'
  ```

#### Xem Profile hiện tại
- **Endpoint:** `GET /api/auth/me`
- **Ví dụ cURL:**
  ```bash
  curl -X GET http://localhost/yii2-blog/web/api/auth/me \
    -H "Authorization: Bearer <your_access_token>"
  ```

---

### 3.2 Category & Tag

#### Tạo Danh mục (Chỉ Admin)
- **Endpoint:** `POST /api/categories`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/categories \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <admin_token>" \
    -d '{"name": "Lifestyle"}'
  ```

#### Lấy danh sách tags
- **Endpoint:** `GET /api/tags`
- **Ví dụ cURL:**
  ```bash
  curl -X GET http://localhost/yii2-blog/web/api/tags
  ```

---

### 3.3 Post (Bài viết)

#### Tạo Bài viết (Author / Admin)
- **Endpoint:** `POST /api/posts`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/posts \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <author_token>" \
    -d '{"category_id": 1, "title": "My First Post", "description": "Intro", "content": "Detailed body content", "status": 1, "tag_list": ["news", "php"]}'
  ```

#### Xem danh sách bài viết (Có phân trang & Lọc)
- **Endpoint:** `GET /api/posts?expand=category,tags,author`
- **Ví dụ cURL:**
  ```bash
  curl -X GET "http://localhost/yii2-blog/web/api/posts?category_id=1&status=1&expand=category,tags,author"
  ```

#### Sửa bài viết (Chỉ chủ bài viết hoặc Admin)
- **Endpoint:** `PUT /api/posts/<id>`
- **Ví dụ cURL:**
  ```bash
  curl -X PUT http://localhost/yii2-blog/web/api/posts/1 \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <author_token>" \
    -d '{"title": "Updated Title", "tag_list": ["updated", "tag"]}'
  ```

#### Xóa mềm bài viết (Chỉ chủ bài viết hoặc Admin)
- **Endpoint:** `DELETE /api/posts/<id>`
- **Ví dụ cURL:**
  ```bash
  curl -X DELETE http://localhost/yii2-blog/web/api/posts/1 \
    -H "Authorization: Bearer <author_token>"
  ```

#### Thích/Bỏ thích bài viết (Yêu cầu đăng nhập)
- **Endpoint:** `POST /api/posts/<id>/like`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/posts/1/like \
    -H "Authorization: Bearer <user_token>"
  ```

---

### 3.4 Comment (Bình luận)

#### Viết bình luận
- **Endpoint:** `POST /api/posts/<post_id>/comments`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/posts/1/comments \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <user_token>" \
    -d '{"content": "This is a comment"}'
  ```

#### Viết phản hồi bình luận (Nested Reply)
- **Endpoint:** `POST /api/posts/<post_id>/comments`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/posts/1/comments \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <user_token>" \
    -d '{"content": "This is a reply to comment 1", "parent_id": 1}'
  ```

#### Sửa bình luận (Chỉ chủ bình luận)
- **Endpoint:** `PUT /api/comments/<id>`
- **Ví dụ cURL:**
  ```bash
  curl -X PUT http://localhost/yii2-blog/web/api/comments/1 \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <comment_owner_token>" \
    -d '{"content": "Updated comment content"}'
  ```

#### Ẩn bình luận (Chỉ tác giả bài viết hoặc Admin)
- **Endpoint:** `POST /api/comments/<id>/hide`
- **Ví dụ cURL:**
  ```bash
  curl -X POST http://localhost/yii2-blog/web/api/comments/1/hide \
    -H "Authorization: Bearer <post_owner_token>"
  ```

#### Xóa bình luận (Chủ bình luận / Tác giả bài viết / Admin)
- **Endpoint:** `DELETE /api/comments/<id>`
- **Ví dụ cURL:**
  ```bash
  curl -X DELETE http://localhost/yii2-blog/web/api/comments/1 \
    -H "Authorization: Bearer <user_token>"
  ```

---

## 4. Sơ đồ thực thể quan hệ (ERD Diagram)

Sơ đồ ERD hiện tại biểu diễn các quan hệ mềm (Soft relationships) trong code:

https://drive.google.com/file/d/12jby51hJwU11mPKhxoqBWM-sI0slSl2f/view?usp=sharing
