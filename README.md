# Yii2 REST API Blog System

REST API Blog System xây dựng trên **Yii2**, phân quyền **RBAC native**, tích hợp **Cloudflare R2** (lưu ảnh) và **Cloudflare Workers AI** (sinh nội dung).

---

## ⚡ Cài đặt trong 5 phút

### 1. Clone & cài dependencies
```bash
git clone <repo-url>
cd yii2-app-basic
composer install
```

### 2. Cấu hình môi trường
```bash
cp .env.example .env
```
Mở `.env` và điền thông tin:
```env
# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=yii2_blog
DB_USER=root
DB_PASS=

# Cookie (bắt buộc)
COOKIE_VALIDATION_KEY=random-secret-key-here

# Cloudflare R2 (tuỳ chọn — bỏ trống nếu chưa cần upload)
CF_ACCOUNT_ID=
R2_ACCESS_KEY=
R2_SECRET_KEY=
R2_BUCKET=
R2_PUBLIC_URL=

# Cloudflare Workers AI (tuỳ chọn — bỏ trống nếu chưa cần AI)
AI_WORKER_URL=
AI_WORKER_TOKEN=
AI_WORKER_MODEL=@cf/meta/llama-3.1-8b-instruct
```

### 3. Tạo database & chạy migration
Tạo database trống tên `yii2_blog` (hoặc tên khác khớp với `.env`), sau đó:
```bash
php yii migrate
```
> Migration sẽ tự động tạo bảng, thiết lập RBAC (3 role), và seed tài khoản admin + author mẫu.

### 4. Chạy server
```bash
# PHP built-in server (dev)
php yii serve

# Hoặc trỏ Virtual Host (Laragon/XAMPP) vào thư mục web/
```

### 5. Import Postman
Import file [`docs/yii2-blog-api.postman_collection.json`](docs/yii2-blog-api.postman_collection.json) vào Postman.

> [!NOTE]
> Migration **không dùng foreign key constraint** ở tầng DB. Quan hệ được quản lý hoàn toàn qua ActiveRecord relations và Form Model validation.

---

## 👥 Tài khoản seed mặc định

| Role | Username | Password | Quyền |
|:---|:---|:---|:---|
| **admin** | `admin_root` | `admin123456` | Toàn quyền |
| **author** | `author_one` | `author123456` | Tạo/sửa/xóa bài của mình |
| **author** | `author_two` | `author123456` | Tạo/sửa/xóa bài của mình |
| **reader** | `reader_one` | `reader123456` | Comment + like |

---

## 🔑 RBAC Matrix

| Tính năng | Guest | Reader | Author | Admin |
|:---|:---:|:---:|:---:|:---:|
| Đăng ký / Đăng nhập | ✅ | ✅ | ✅ | ✅ |
| Xem post công khai | ✅ | ✅ | ✅ | ✅ |
| Xem comment | ✅ | ✅ | ✅ | ✅ |
| Like / Unlike post | ❌ | ✅ | ✅ | ✅ |
| Tạo comment | ❌ | ✅ | ✅ | ✅ |
| Sửa / Xóa comment của mình | ❌ | ✅ | ✅ | ✅ |
| Xóa comment bất kỳ | ❌ | ❌ | ❌ | ✅ |
| Tạo post | ❌ | ❌ | ✅ | ✅ |
| Sửa / Xóa post của mình | ❌ | ❌ | ✅ | ✅ |
| Sửa / Xóa post bất kỳ | ❌ | ❌ | ❌ | ✅ |
| Upload ảnh | ❌ | ❌ | ✅ | ✅ |
| Gọi AI (generate/improve) | ❌ | ❌ | ✅ | ✅ |
| CRUD Category (kể cả xem) | ❌ | ❌ | ❌ | ✅ |
| CRUD Tag (kể cả xem) | ❌ | ❌ | ❌ | ✅ |

> Row-level check (owner check) thực hiện qua `rbac\AuthorRule` — Yii RBAC native `DbManager`.

---

## 🗄️ ERD

👉 [Xem ERD trên Google Drive](https://drive.google.com/file/d/1m-qB_-vV0Lkhe6UJ3ZtW2b6mYc76JR5c/view?usp=sharing)

**Các bảng chính:**
`user` · `post` · `category` · `tag` · `post_tag` · `comment` · `post_like` · `media` · `media_link` · `ai_log`

---

## 📋 Danh sách Endpoints

Tất cả response đều theo envelope:
```json
{
  "status": "success",
  "code": 200,
  "message": "Success",
  "data": { ... }
}
```
List endpoint trả thêm:
```json
{
  "data": {
    "items": [...],
    "pagination": { "total": 50, "page": 1, "limit": 10, "total_page": 5 }
  }
}
```

### 🔐 Authentication
| Method | Endpoint | Mô tả | Auth |
|:---|:---|:---|:---:|
| POST | `/api/auth/register` | Đăng ký tài khoản | ❌ |
| POST | `/api/auth/login` | Đăng nhập → nhận Bearer token | ❌ |
| GET | `/api/auth/me` | Thông tin user hiện tại | 🔑 |
| PUT | `/api/auth/change-password` | Đổi mật khẩu | 🔑 |
| POST | `/api/auth/logout` | Đăng xuất (revoke token) | 🔑 |

### 📁 Categories
> Toàn bộ CRUD category (kể cả đọc) yêu cầu quyền **admin** — theo đề bài "Category: CRUD (admin)".

| Method | Endpoint | Mô tả | Auth |
|:---|:---|:---|:---:|
| GET | `/api/categories` | Danh sách category | 👑 Admin |
| GET | `/api/categories/<id>` | Chi tiết category | 👑 Admin |
| POST | `/api/categories` | Tạo category | 👑 Admin |
| PUT | `/api/categories/<id>` | Cập nhật category | 👑 Admin |
| DELETE | `/api/categories/<id>` | Xóa mềm category | 👑 Admin |

### 🏷️ Tags
> Toàn bộ CRUD tag (kể cả đọc) yêu cầu quyền **admin** — theo đề bài "Tag: many-to-many với post, auto-create khi dùng tag mới".

| Method | Endpoint | Mô tả | Auth |
|:---|:---|:---|:---:|
| GET | `/api/tags` | Danh sách tag | 👑 Admin |
| GET | `/api/tags/<id>` | Chi tiết tag | 👑 Admin |
| POST | `/api/tags` | Tạo tag | 👑 Admin |
| PUT | `/api/tags/<id>` | Cập nhật tag | 👑 Admin |
| DELETE | `/api/tags/<id>` | Xóa mềm tag | 👑 Admin |

### 📝 Posts
| Method | Endpoint | Mô tả | Auth |
|:---|:---|:---|:---:|
| GET | `/api/posts` | Danh sách post đã publish (search/filter/paginate) | ❌ |
| GET | `/api/posts/<slug>` | Chi tiết post theo slug | ❌ |
| POST | `/api/posts` | Tạo post mới | ✍️ Author/Admin |
| PUT | `/api/posts/<id>` | Cập nhật post | ✍️ Owner/Admin |
| DELETE | `/api/posts/<id>` | Xóa mềm post | ✍️ Owner/Admin |
| POST | `/api/posts/<id>/publish` | Xuất bản post | ✍️ Owner/Admin |
| POST | `/api/posts/<id>/like` | Toggle like/unlike | 🔑 |
| GET | `/api/posts/manage` | Danh sách post (quản lý — kể cả draft) | ✍️ Author/Admin |
| GET | `/api/posts/<id>/manage` | Chi tiết post (quản lý) | ✍️ Owner/Admin |

**Query params cho `GET /api/posts`:**
```
?title=keyword       # Tìm theo tiêu đề (LIKE)
?category_id=1       # Lọc theo category
?tag=php             # Lọc theo tên tag
?tag_id=3            # Lọc theo tag ID
?status=0            # Lọc theo trạng thái (chỉ dùng ở manage)
?expand=category,tags,author,thumbnail   # Eager load relations
?limit=10&page=1     # Phân trang
?sort=-view_count    # Sắp xếp (dấu - = DESC)
```

### 💬 Comments
| Method | Endpoint | Mô tả | Auth |
|:---|:---|:---|:---:|
| GET | `/api/posts/<postId>/comments` | Lấy cây comment (threaded) | ❌ |
| POST | `/api/posts/<postId>/comments` | Thêm comment / reply | 🔑 |
| PUT | `/api/comments/<id>` | Sửa comment | 👤 Owner |
| DELETE | `/api/comments/<id>` | Xóa mềm comment | 👤 Owner / 👑 Admin |

### 🖼️ Media (Upload ảnh → Cloudflare R2)
| Method | Endpoint | Mô tả | Auth |
|:---|:---|:---|:---:|
| POST | `/api/media` | Upload ảnh (≤5MB, jpeg/png/webp) | ✍️ Author/Admin |
| DELETE | `/api/media/<id>` | Xóa ảnh | ✍️ Owner/Admin |

### 🤖 AI Assistant (Cloudflare Workers AI)
| Method | Endpoint | Mô tả | Auth |
|:---|:---|:---|:---:|
| POST | `/api/ai/generate-title` | Sinh 5 gợi ý tiêu đề từ mô tả | ✍️ Author/Admin |
| POST | `/api/ai/generate-summary` | Tóm tắt nội dung dài | ✍️ Author/Admin |
| POST | `/api/ai/improve-text` | Cải thiện / rewrite đoạn văn | ✍️ Author/Admin |

---

## 🧪 Curl Examples

### Đăng ký & Đăng nhập
```bash
# Đăng ký
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"john","email":"john@example.com","password":"Secret123!"}'

# Đăng nhập → lấy token
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin_root","password":"admin123456"}'
```

### Tạo Category (Admin)
```bash
curl -X POST http://localhost/api/categories \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Technology","slug":"technology"}'
```

### Tạo Post (Author)
```bash
curl -X POST http://localhost/api/posts \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Yii2 Tips & Tricks",
    "content": "Nội dung bài viết...",
    "category_id": 1,
    "status": 0,
    "tagNames": ["yii2", "php", "backend"]
  }'
```

### List Posts với filter + expand
```bash
curl "http://localhost/api/posts?expand=category,tags,author,thumbnail&tag=php&limit=5&page=1"
```

### Upload ảnh lên R2
```bash
curl -X POST http://localhost/api/media \
  -H "Authorization: Bearer <token>" \
  -F "file=@/path/to/image.jpg"
```

### AI: Sinh tiêu đề
```bash
curl -X POST http://localhost/api/ai/generate-title \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"prompt":"Hướng dẫn tối ưu hiệu năng database MySQL cho ứng dụng web lớn"}'
```

### AI: Tóm tắt nội dung
```bash
curl -X POST http://localhost/api/ai/generate-summary \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"prompt":"<nội dung bài viết dài...>"}'
```

### Comment với reply
```bash
# Comment gốc
curl -X POST http://localhost/api/posts/5/comments \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"content":"Bài viết hay lắm!"}'

# Reply vào comment (parent_id = id comment gốc)
curl -X POST http://localhost/api/posts/5/comments \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"content":"Cảm ơn bạn!","parent_id":3}'
```

### Like / Unlike post
```bash
curl -X POST http://localhost/api/posts/5/like \
  -H "Authorization: Bearer <token>"
```

---

## 🏗️ Kiến trúc

```
modules/api/
├── controllers/          # Controllers (orchestration only, no business logic)
│   ├── AuthController    # register, login, logout, me, change-password
│   ├── PostController    # CRUD post + like + publish
│   ├── CategoryController
│   ├── TagController
│   ├── CommentController
│   ├── MediaController   # Upload lên Cloudflare R2
│   └── AiController      # generate-title, generate-summary, improve-text
└── models/
    ├── forms/            # Form Models (validation + upload fields tách khỏi AR)
    └── search/           # Search Models (filter + eager load)

models/                   # AR Models (DB mapping, relations, behaviors)
behaviors/
├── SoftDeleteBehavior    # Soft-delete tự viết
└── LoginRateLimiter      # Rate limit login (5 fail/60s)
components/
├── R2Component           # Cloudflare R2 upload/delete/presigned URL
└── AiWorkerComponent     # Cloudflare Workers AI (timeout 30s, error 502)
rbac/
└── AuthorRule            # Owner check (post.author_id / comment.user_id)
migrations/               # Toàn bộ schema + index + seed (chạy sạch từ DB trống)
```

**Design decisions:**
- AR model không chứa upload field — tách vào Form Model
- Business logic post-save (sync tag, set published_at, slug) nằm trong `beforeSave`/`afterSave`
- Soft-delete qua `SoftDeleteBehavior` custom (không dùng FK constraint)
- Eager loading bằng `with()` trong Search model — tránh N+1
- Response envelope chuẩn hóa tại `web.php` `response.beforeSend`

---

## 🔒 Bảo mật

- Bearer token authentication (`HttpBearerAuth`)
- Password hash bằng `Yii::$app->security->generatePasswordHash()` (bcrypt)
- Token revoke khi logout
- Rate limit login: **5 lần thất bại / 60 giây** → block IP
- Không có secret/credential nào trong source code (đọc từ `.env`)
- RBAC check bằng `Yii::$app->user->can()` — không hardcode trong action

---

## 📦 Tech Stack

| Thành phần | Công nghệ |
|:---|:---|
| Framework | Yii2 (PHP 8.x) |
| Database | MySQL 8+ |
| Auth | Bearer Token (Yii2 native) |
| RBAC | `yii\rbac\DbManager` |
| File storage | Cloudflare R2 (S3-compatible, `aws/aws-sdk-php`) |
| AI | Cloudflare Workers AI |
| Env | `vlucas/phpdotenv` |
