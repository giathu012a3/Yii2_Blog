# Yii2 REST API Blog System

Dự án REST API Blog System được phát triển trên nền tảng framework **Yii2** kết hợp các tiêu chuẩn thiết kế RESTful hiện đại, quản lý phân quyền **RBAC (Role-Based Access Control)** và tích hợp cấu trúc quan hệ mềm dẻo.

---

## ⚡ Hướng dẫn cài đặt trong 5 phút (Quick Setup)

Để chạy nhanh dự án trên môi trường cục bộ (ví dụ: Laragon, XAMPP hoặc PHP built-in server):

### Bước 1: Thiết lập cấu hình môi trường
Sao chép file cấu hình mẫu `.env.example` thành `.env` ở thư mục gốc:
```bash
cp .env.example .env
```
Mở file `.env` vừa tạo và cấu hình thông tin kết nối Cơ sở dữ liệu:
```env
DB_DSN="mysql:host=localhost;dbname=yii2_blog"
DB_USERNAME="root"
DB_PASSWORD=""
```
*(Yii2 sẽ tự động đọc cấu hình này từ file `.env`)*

### Bước 2: Tạo database và cài đặt dependencies
Tạo một cơ sở dữ liệu trống có tên trùng với cấu hình trong `.env` (ví dụ: `yii2_blog` với bảng mã `utf8mb4_unicode_ci`). Sau đó chạy lệnh cài đặt thư viện:
```bash
composer install
```

### Bước 3: Thực thi Migration (Khởi tạo bảng & Gieo hạt dữ liệu mẫu)
Chạy lệnh migration để tự động sinh cấu trúc bảng, các quyền RBAC và tài khoản mẫu:
```bash
php yii migrate
```
> [!NOTE]
> Hệ thống sử dụng **soft relations** (quan hệ mềm qua ActiveRecord). File migration được tối ưu để chạy sạch từ DB trống mà không sử dụng ràng buộc khóa ngoại cứng ở tầng database.

### Bước 4: Chạy dự án & Import Postman
1. Cấu hình Virtual Host trỏ vào thư mục `web/` (ví dụ: `http://yii2-app-basic.test`) hoặc chạy nhanh bằng server tích hợp:
   ```bash
   php yii serve
   ```
2. Import bộ tài liệu API mẫu tại đường dẫn [yii2-blog-api.postman_collection.json](docs/yii2-blog-api.postman_collection.json) vào Postman để kiểm thử lập tức.

---

## 👥 Tài khoản thử nghiệm (Seed Accounts)

Sau khi chạy migration thành công, hệ thống đã chuẩn bị sẵn các tài khoản mẫu với mật khẩu và phân quyền tương ứng:

| Vai trò (Role) | Username | Mật khẩu (Password) | Quyền hạn chính |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin_root` | `admin123456` | Toàn quyền hệ thống, quản lý danh mục/tag, xóa mọi bài viết/bình luận. |
| **Author (1)** | `author_one` | `author123456` | Đăng bài viết, quản lý bài viết của mình, viết bình luận. |
| **Author (2)** | `author_two` | `author123456` | Đăng bài viết, quản lý bài viết của mình, viết bình luận. |
| **Reader (User)** | `user15` | `password123` | Người dùng đã đăng nhập. Chỉ có thể viết bình luận, chỉnh sửa/xóa bình luận của mình, thích bài viết. |

---

## 🔑 Ma trận vai trò & quyền hạn (RBAC Matrix)

Hệ thống phân quyền chi tiết đến từng dòng dữ liệu (Row-level/Owner checking) thông qua `rbac\AuthorRule`:

| Tính năng | Khách (Guest) | Reader (User) | Author | Admin | Chi tiết kiểm tra quyền (RBAC Rule) |
| :--- | :---: | :---: | :---: | :---: | :--- |
| Đăng ký & Đăng nhập | ✅ | ✅ | ✅ | ✅ | Public endpoints |
| Xem Danh mục & Tags | ✅ | ✅ | ✅ | ✅ | Public endpoints |
| Quản lý Danh mục & Tags | ❌ | ❌ | ❌ | ✅ | Yêu cầu quyền `manageCategory` / `manageTags` |
| Xem Bài viết công khai | ✅ | ✅ | ✅ | ✅ | Chỉ hiển thị các bài viết đã xuất bản (`Published`) |
| Tạo bài viết mới | ❌ | ❌ | ✅ | ✅ | Yêu cầu quyền `createPost` |
| Sửa bài viết | ❌ | ❌ | Chỉ của mình | ✅ | Kiểm tra quyền `updatePost` (kèm `AuthorRule` cho Author) |
| Xóa bài viết | ❌ | ❌ | Chỉ của mình | ✅ | Kiểm tra quyền `deletePost` (kèm `AuthorRule` cho Author) |
| Thích bài viết | ❌ | ✅ | ✅ | ✅ | Quyền `likePost` (logged-in) |
| Xem Bình luận | ✅ | ✅ | ✅ | ✅ | Public, hiển thị cây phân cấp (1-level nesting) |
| Tạo bình luận | ❌ | ✅ | ✅ | ✅ | Quyền `createComment` |
| Sửa bình luận | ❌ | Chỉ của mình | Chỉ của mình | Chỉ của mình | Quyền `updateOwnComment` (Chỉ chủ sở hữu, kể cả Admin) |
| Xóa bình luận | ❌ | Chỉ của mình | Chỉ của mình | ✅ | Quyền `deleteComment` (Owner hoặc Admin bất kỳ) |

---

## 🗄️ Sơ đồ quan hệ thực thể (Soft ERD)

👉 [Xem sơ đồ ERD chi tiết trên Google Drive](https://drive.google.com/file/d/1m-qB_-vV0Lkhe6UJ3ZtW2b6mYc76JR5c/view?usp=sharing)

---

## 📋 Danh sách Endpoints & Cấu trúc API

Mọi API Response đều được tự động chuẩn hóa qua middleware/behavior về một cấu trúc Envelope thống nhất:
```json
{
  "status": "success",
  "code": 200,
  "message": "Success",
  "data": { ... }
}
```

| Phương thức | Đường dẫn (Endpoint) | Chức năng | Phân quyền (Auth) |
| :--- | :--- | :--- | :---: |
| **POST** | `/api/auth/register` | Đăng ký tài khoản | ❌ Public |
| **POST** | `/api/auth/login` | Đăng nhập nhận Token | ❌ Public |
| **GET** | `/api/auth/me` | Lấy thông tin cá nhân | 🔑 Logged-in |
| **PUT** | `/api/auth/change-password` | Đổi mật khẩu | 🔑 Logged-in |
| **POST** | `/api/auth/logout` | Đăng xuất | 🔑 Logged-in |
| **GET** | `/api/categories` | Lấy danh sách danh mục | ❌ Public |
| **POST** | `/api/categories` | Tạo danh mục mới | 👑 Admin |
| **PUT** | `/api/categories/<id>` | Cập nhật danh mục | 👑 Admin |
| **DELETE** | `/api/categories/<id>` | Xóa mềm danh mục | 👑 Admin |
| **GET** | `/api/tags` | Lấy danh sách nhãn dán | ❌ Public |
| **POST** | `/api/tags` | Tạo nhãn dán mới | 👑 Admin |
| **PUT** | `/api/tags/<id>` | Cập nhật nhãn dán | 👑 Admin |
| **DELETE** | `/api/tags/<id>` | Xóa mềm nhãn dán | 👑 Admin |
| **GET** | `/api/posts` | Danh sách bài viết công khai | ❌ Public |
| **GET** | `/api/posts/<slug>` | Chi tiết bài viết theo Slug | ❌ Public |
| **POST** | `/api/posts` | Tạo bài viết mới | ✍️ Author / Admin |
| **PUT** | `/api/posts/<id>` | Cập nhật bài viết | ✍️ Owner / Admin |
| **DELETE** | `/api/posts/<id>` | Xóa mềm bài viết | ✍️ Owner / Admin |
| **GET** | `/api/posts/manage` | Danh sách bài viết quản lý | ✍️ Author / Admin |
| **GET** | `/api/posts/<id>/manage` | Chi tiết bài viết quản lý | ✍️ Owner / Admin |
| **POST** | `/api/posts/<id>/like` | Thích / Bỏ thích bài bài viết | 🔑 Logged-in |
| **GET** | `/api/posts/<postId>/comments` | Lấy cây bình luận (Threaded) | ❌ Public |
| **POST** | `/api/posts/<postId>/comments` | Viết bình luận / Phản hồi | 🔑 Logged-in |
| **PUT** | `/api/comments/<id>` | Cập nhật bình luận | 👤 Chỉ chủ sở hữu |
| **DELETE** | `/api/comments/<id>` | Xóa bình luận | 👤 Owner / 👑 Admin |
