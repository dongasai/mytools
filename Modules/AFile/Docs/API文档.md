# AFile 文件模块 · 前端 API 手册

## 快速开始

| 项目 | 详情                           |
|------|--------------------------------|
| 接口地址 | `{域名}/api/file`             |
| 认证方式 | `Authorization: Bearer {token}` |
| 请求方式 | POST 用 `multipart/form-data`，GET 无需参数 |
| 响应格式 | JSON `{ success, message, data, code }` |

---

## API 接口列表

| 接口 | 方法 | 认证 | 说明 |
|------|------|:--:|------|
| `/upload/public` | POST | ✅ | 上传公共文件 |
| `/upload/private` | POST | ✅ | 上传私有文件 |
| `/upload/temp` | POST | ✅ | 上传文件到临时储存 |
| `/image/upload/public` | POST | ✅ | 上传公共图片 |
| `/image/upload/private` | POST | ✅ | 上传私有图片 |
| `/download/{id}` | GET | ❌ | 下载文件 |
| `/image/{id}` | GET | ❌ | 查看图片 |
| `/image/{id}/download` | GET | ❌ | 下载公共图片 |
| `/image/{id}/download/private` | GET | ✅ | 下载私有图片 |

**注意**：
- ✅ 需认证：请求头需携带 `Authorization: Bearer {token}`
- ❌ 无需认证：公开访问，无需登录
- 所有路径前缀为 `/api/file`
- **已移除删除API**：文件和图片删除功能已下线，由业务模块管理
- **⚠️ 悬空文件清理机制**：公共文件上传后1小时未使用将被标记悬空，24小时后自动删除

---

## ⚠️ 重要说明：文件使用标记机制

**核心原则**：
- 用户上传公共文件后得到 `file_id` 或 `image_id`
- **必须在1小时内**将 `file_id/image_id` 传给业务API使用
- **业务API负责调用标记服务**，否则文件将被悬空清理机制删除
- 前端不直接调用标记接口，由后端业务模块处理

**正确流程**：
```
1. 用户上传图片 → 得到 image_id=123
2. 用户创建章节 → POST /novel/chapters {title, content, image_id: 123}
3. Novel API自动关联图片 → 业务逻辑处理
```

**错误流程** ❌：
```
用户上传图片 → 用户调用PUT修改关联（已废弃）
```

---

## 一、认证

需要认证的接口在请求头带上 Token：

```js
function authFetch(url, options = {}) {
  return fetch(url, {
    ...options,
    headers: {
      ...options.headers,
      Authorization: `Bearer ${token}`
    }
  });
}
```

> Token 由后端统一管理，前端从登录接口获取后存入本地，每次请求携带即可。过期返回 401 时跳转登录页。

---

## 二、文件上传（公共）

```
POST /api/file/upload/public
```

### 使用方式

```js
const form = new FormData();
form.append('file', fileInput.files[0]);       // 必填

const res = await authFetch('/api/file/upload/public', {
  method: 'POST',
  body: form
}).then(r => r.json());

// ⭐ 得到file_id后，必须在1小时内传给业务API标记使用
if (res.success) {
  const fileId = res.data.id;
  // 调用业务API：createChapter({ title, content, file_id: fileId })
  // 业务API会自动调用markFileAsUsed标记文件已使用
}
```

### 参数

| 参数 | 类型 | 必填 | 说明 |
|------|------|:--:|------|
| `file` | File | ✅ | 上传的文件 |

### 限制

| 项目 | 值 |
|------|-----|
| 最大 | 10MB |
| 允许类型 | pdf / doc / docx / txt / xlsx / xls / ppt / pptx |

### ⚠️ 悬空清理机制

**重要**：公共文件上传后有自动清理机制：
- **1小时内未使用**：自动标记为"悬空"（dangling）
- **24小时后自动删除**：悬空文件会被物理删除

**必须的操作**：
- 上传后立即将 `file_id` 传给业务API
- 业务API会调用 `markFileAsUsed()` 标记文件已使用
- 标记后文件不会被悬空清理

### 返回

```json
{
  "success": true,
  "message": "文件上传成功。文件ID需传给业务API建立使用标记。",
  "code": 200,
  "data": {
    "id": 123,
    "original_name": "文档.pdf",
    "file_size": 102400,
    "file_type": "pdf",
    "url": "/storage/xxx.pdf",
    "status": "normal",
    "created_at": "2026-05-05T12:00:00.000000Z"
  }
}
```

**状态说明**：
- `normal`：刚上传，等待被使用
- `linked`：已被业务API标记使用（安全）
- `dangling`：1小时未使用，悬空状态（即将删除）

### React 示例

```tsx
function FileUploader() {
  const upload = async (file: File) => {
    const fd = new FormData();
    fd.append('file', file);

    const res = await fetch('/api/file/upload/public', {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: fd
    }).then(r => r.json());

    if (res.success) {
      // ⭐ 关键：立即传给业务API，避免悬空清理
      const fileId = res.data.id;
      await createNovelChapter({ title, content, file_id: fileId });
    } else {
      message.error(res.message);
    }
  };

  return <input type="file" accept=".pdf,.doc,.docx,.txt,.xlsx,.xls,.ppt,.pptx" onChange={e => upload(e.target.files[0])} />;
}
```

---

## 三、文件上传（私有）

```
POST /api/file/upload/private
```

**说明**：当前文件模块暂不支持私有概念，此接口保留为保持API一致性，实际功能与公共上传相同。

---

## 四、文件上传（临时储存）

```
POST /api/file/upload/temp
```

接收 multipart/form-data 的 file 上传，保存到临时储存（不写数据库），返回相对路径 path（如 temp/202608/14/xxx.xlsx）。
临时文件可用于 FeatureExcel 导入等需要文件路径的场景。

### 使用方式

```js
const form = new FormData();
form.append('file', fileInput.files[0]);       // 必填

const res = await authFetch('/api/file/upload/temp', {
  method: 'POST',
  body: form
}).then(r => r.json());

// 得到相对路径，可传给业务接口使用
if (res.success) {
  const relativePath = res.data.path;
  // 调用业务接口：{ file_path: relativePath }
}
```

### 参数

| 参数 | 类型 | 必填 | 说明 |
|------|------|:--:|------|
| `file` | File | ✅ | 上传的文件 |

### 限制

| 项目 | 值 |
|------|-----|
| 最大 | 10MB |
| 允许类型 | pdf / doc / docx / txt / xlsx / xls / ppt / pptx / csv |

**注意**：临时文件由 `afile:clean-temp` 命令定期清理，默认保留3天。

### 返回

```json
{
  "success": true,
  "message": "文件已保存到临时储存",
  "code": 200,
  "data": {
    "path": "temp/202608/14/xxx.csv",
    "original_name": "数据.csv",
    "file_size": 10240,
    "ext": "csv"
  }
}
```

**说明**：
- `path` 为临时储存的相对路径，可传给业务接口的 `file_path` 参数，由后端解析为实际存储路径
- 不返回 `url`，临时文件不对外提供访问链接

---

## 五、文件列表 **⚠️ 临时屏蔽**

```
POST /api/file/upload
```

### 使用方式

```js
const form = new FormData();
form.append('file', fileInput.files[0]);       // 必填

const res = await authFetch('/api/file/upload', {
  method: 'POST',
  body: form
}).then(r => r.json());

// ⭐ 得到file_id后，传给业务API
if (res.success) {
  const fileId = res.data.id;
  // 调用业务API：createChapter({ title, content, file_id: fileId })
}
```

### 参数

| 参数 | 类型 | 必填 | 说明 |
|------|------|:--:|------|
| `file` | File | ✅ | 上传的文件 |
| `re_type` | string | | **仅用于验证**，不保存到数据库 |
| `re_id` | number | | **仅用于验证**，不保存到数据库 |

### 限制

| 项目 | 值 |
|------|-----|
| 最大 | 10MB |
| 允许类型 | pdf / doc / docx / txt / xlsx / xls / ppt / pptx |

### 返回

```json
{
  "success": true,
  "message": "文件上传成功。文件ID需传给业务API建立关联。",
  "code": 200,
  "data": {
    "id": 123,
    "original_name": "文档.pdf",
    "file_size": 102400,
    "file_type": "pdf",
    "url": "/storage/xxx.pdf",
    "created_at": "2026-04-27T12:00:00.000000Z"
  }
}
```

### React 示例

```tsx
function FileUploader() {
  const upload = async (file: File) => {
    const fd = new FormData();
    fd.append('file', file);

    const res = await fetch('/api/file/upload', {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: fd
    }).then(r => r.json());

    if (res.success) {
      // ⭐ 关键：得到file_id，传给业务API
      const fileId = res.data.id;
      await createNovelChapter({ title, content, file_id: fileId });
    } else {
      message.error(res.message);
    }
  };

  return <input type="file" accept=".pdf,.doc,.docx,.txt,.xlsx,.xls,.ppt,.pptx" onChange={e => upload(e.target.files[0])} />;
}
```

---

## 六、文件列表 **⚠️ 临时屏蔽**

> **注意**：此接口路由已临时注释，暂时不对外开放。如需启用请联系后端开发。

```
GET /api/file
```

**查询当前认证用户自己的文件列表**（不会泄露其他用户的文件）：

```js
const res = await authFetch('/api/file?page=1&per_page=20')
  .then(r => r.json());

if (res.success) {
  console.log(res.data.items);  // 当前用户的文件列表
  console.log(res.data.total);  // 当前用户的文件总数
}
```

**重要说明**：
- ✅ 需要认证（未认证返回401）
- ✅ 仅返回当前用户上传的文件（user_id筛选）
- ❌ 不会返回其他用户的文件（隐私保护）

### 参数

| 参数 | 类型 | 必填 | 说明 |
|------|------|:--:|------|
| `page` | number | | 页码，默认1 |
| `per_page` | number | | 每页数量，默认20，最大100 |
| `re_type` | string | | 筛选关联类型 |
| `re_id` | number | | 筛选关联ID |
| `type1` | string | | 筛选文件类型 |

### 返回

```json
{
  "success": true,
  "message": "获取文件列表成功",
  "code": 200,
  "data": {
    "items": [
      {
        "id": 123,
        "original_name": "文档.pdf",
        "file_size": 102400,
        "file_type": "pdf",
        "re_type": "novel_chapter",
        "re_id": 456,
        "url": "/storage/xxx.pdf",
        "created_at": "2026-04-27T12:00:00.000000Z"
      }
    ],
    "total": 50,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3
  }
}
```

---

## 七、文件下载

```
GET /api/file/download/{id}
```

无需认证，直接用文件 ID 下载：

```js
// 方式一：直接打开下载
window.open(`/api/file/download/${fileId}`);

// 方式二：a 标签下载
const a = document.createElement('a');
a.href = `/api/file/download/${fileId}`;
a.download = '';  // 使用服务器返回的文件名
a.click();
```

---

## 八、图片列表 **⚠️ 临时屏蔽**

> **注意**：此接口路由已临时注释，暂时不对外开放。如需启用请联系后端开发。

```
GET /api/file/image
```

查询用户的图片列表：

```js
const res = await authFetch('/api/file/image?page=1&per_page=20&private=0')
  .then(r => r.json());

if (res.success) {
  console.log(res.data.items);  // 图片列表
}
```

### 参数

| 参数 | 类型 | 必填 | 说明 |
|------|------|:--:|------|
| `page` | number | | 页码，默认1 |
| `per_page` | number | | 每页数量，默认20 |
| `private` | number | | 筛选私有状态：0=公共，1=私有 |
| `re_type` | string | | 筛选关联类型 |
| `re_id` | number | | 筛选关联ID |

### 返回

```json
{
  "success": true,
  "message": "获取图片列表成功",
  "code": 200,
  "data": {
    "items": [
      {
        "id": 456,
        "original_name": "photo.jpg",
        "file_size": 51200,
        "width": 1920,
        "height": 1080,
        "file_type": "webp",
        "is_private": false,
        "re_type": "user_avatar",
        "re_id": 789,
        "url": "/storage/xxx.webp",
        "created_at": "2026-04-27T12:00:00.000000Z"
      }
    ],
    "total": 30,
    "per_page": 20,
    "current_page": 1,
    "last_page": 2
  }
}
```

---

## 九、图片上传（公开）

```
POST /api/file/image/upload/public
```

上传后所有人可见，适合头像、商品图、文章配图。

```js
const fd = new FormData();
fd.append('file', imageFile);

const res = await authFetch('/api/file/image/upload/public', {
  method: 'POST',
  body: fd
}).then(r => r.json());

// ⭐ 得到image_id，传给业务API
if (res.success) {
  const imageId = res.data.id;
  await updateUserProfile({ avatar_image_id: imageId });
}
```

| 参数 | 类型 | 必填 | 说明 |
|------|------|:--:|------|
| `file` | File | ✅ | 图片文件 |
| `re_type` | string | | **仅用于验证**，不保存 |
| `re_id` | number | | **仅用于验证**，不保存 |

| 限制 | 值 |
|------|-----|
| 最大 | 5MB |
| 允许 | jpeg / png / gif / webp / bmp |

**注意**：上传后服务器会自动转成 WebP 格式，体积减少约50%。

### 返回

```json
{
  "success": true,
  "message": "图片上传成功。图片ID需传给业务API建立关联。",
  "code": 200,
  "data": {
    "id": 456,
    "original_name": "photo.jpg",
    "file_size": 51200,
    "width": 1920,
    "height": 1080,
    "file_type": "webp",
    "url": "/storage/xxx.webp",
    "is_private": false,
    "created_at": "2026-04-27T12:00:00.000000Z"
  }
}
```

---

## 十、图片上传（私有）

```
POST /api/file/image/upload/private
```

仅上传者自己可以下载，适合用户身份证、隐私照片等。

```js
// 用法同公共图片，只是 URL 不同
const res = await authFetch('/api/file/image/upload/private', {
  method: 'POST',
  body: fd
}).then(r => r.json());

// ⭐ 得到image_id，传给业务API
const imageId = res.data.id;
```

| 与公共的区别 | 说明 |
|-------------|------|
| `is_private` | 返回 `true` |
| 查看权限 | 公开接口看不到，只有上传者能下载 |
| URL | 同样返回，但公开访问会被拦截 |

---

## 十一、展示图片

```
GET /api/file/image/{id}
```

302 重定向到真实图片地址，直接在 `<img>` 中使用：

```html
<img src="/api/file/image/456" alt="产品图片" />
```

私有图片通过此接口访问会返回 403。

---

## 十二、下载图片

```
GET /api/file/image/{id}/download
```

公开图片的下载，无需认证：

```js
window.open(`/api/file/image/${imageId}/download`);
```

私有图片会返回 403。

---

## 十三、下载私有图片

```
GET /api/file/image/{id}/download/private
```

需要认证，只有上传者可以下载：

```js
// 需要在请求中带 token，不能用 window.open
const res = await authFetch(`/api/file/image/${imageId}/download/private`);
const blob = await res.blob();
const url = URL.createObjectURL(blob);

// 显示或下载
setPreviewUrl(url);
const a = document.createElement('a');
a.href = url;
a.download = '';
a.click();
```

---

## 十四、业务API集成示例

### Novel模块 - 创建章节关联文件

**前端代码**：
```tsx
async function createChapter(formData) {
  // 步骤1: 上传文件（如果有）
  let fileId = null;
  if (formData.file) {
    const fileRes = await authFetch('/api/file/upload/public', {
      method: 'POST',
      body: new FormData().append('file', formData.file)
    }).then(r => r.json());

    if (!fileRes.success) return message.error('文件上传失败');
    fileId = fileRes.data.id;  // ⭐ 得到file_id
  }

  // 步骤2: 创建章节（关联文件）
  const chapterRes = await authFetch('/api/file/chapters', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      title: formData.title,
      content: formData.content,
      file_id: fileId  // ⭐ 传给业务API
    })
  }).then(r => r.json());

  if (chapterRes.success) {
    message.success('章节创建成功');
  }
}

### User模块 - 更新头像

**前端代码**：
```tsx
async function updateAvatar(file: File) {
  // 步骤1: 上传图片
  const fd = new FormData();
  fd.append('file', file);

  const imgRes = await authFetch('/api/file/image/upload/public', {
    method: 'POST',
    body: fd
  }).then(r => r.json());

  if (!imgRes.success) return message.error('图片上传失败');
  const imageId = imgRes.data.id;  // ⭐ 得到image_id

  // 步骤2: 更新用户资料（关联头像）
  const profileRes = await authFetch('/api/file/user/profile', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nickname: '新昵称',
      avatar_image_id: imageId  // ⭐ 传给业务API
    })
  }).then(r => r.json());

  if (profileRes.success) {
    setAvatar(`/api/file/image/${imageId}`);
  }
}
```

---

## 十五、错误处理

### 统一格式

失败时返回 JSON，`success` 为 `false`：

```json
{
  "success": false,
  "message": "文件不存在",
  "code": 404
}
```

### 状态码速查

| code | 含义 | 前端处理 |
|------|------|----------|
| 200 | 成功 | `res.data` 取数据 |
| 400 | 参数不对 | `res.message` 提示用户 |
| 401 | 没登录或 token 过期 | 跳转登录页 |
| 403 | 没权限 | 提示"无权访问" |
| 404 | 资源不存在 | 提示"文件/图片已删除" |

### 全局拦截示例

```js
const res = await fetch(url, options).then(r => r.json());

if (!res.success) {
  switch (res.code) {
    case 400:
      return message.warning(res.message);
    case 401:
      router.push('/login');
      return;
    case 403:
      return message.error('没有访问权限');
    case 404:
      return message.error('资源不存在');
    default:
      return message.error(res.message);
  }
}

return res.data;
```

---

## 十六、典型场景

### 场景一：文章编辑器上传图片

```tsx
async function onPaste(e: ClipboardEvent) {
  const file = e.clipboardData?.files[0];
  if (!file?.type.startsWith('image/')) return;

  const fd = new FormData();
  fd.append('file', file);

  const res = await authFetch('/api/file/image/upload/public', {
    method: 'POST', body: fd
  }).then(r => r.json());

  if (res.success) {
    // ⭐ 插入编辑器
    editor.insertImage(`/api/file/image/${res.data.id}`);

    // ⭐ 后续创建文章时，将image_id传给业务API
  }
}
```

### 场景二：用户上传头像

```tsx
async function uploadAvatar(file: File) {
  const fd = new FormData();
  fd.append('file', file);

  const res = await authFetch('/api/file/image/upload/public', {
    method: 'POST', body: fd
  }).then(r => r.json());

  if (res.success) {
    // ⭐ 立即显示预览
    setAvatar(`/api/file/image/${res.data.id}`);

    // ⭐ 后续调用user/profile API更新头像
  }
}
```

### 场景三：私密文档上传 + 查看

```tsx
// 上传（仅自己能看）
const res = await authFetch('/api/file/image/upload/private', {
  method: 'POST', body: fd
}).then(r => r.json());

const privateImageId = res.data.id;

// ⭐ 显示时需要token（不能直接放img src）
const showPrivateImage = async (imageId: number) => {
  const res = await authFetch(`/api/file/image/${imageId}/download/private`);
  const blob = await res.blob();
  const url = URL.createObjectURL(blob);
  setPreviewUrl(url);  // 显示在页面中
};
```

---

## 附录

### 文件大小换算

| 限制 | 字节 |
|------|------|
| 文件 10MB | 10,485,760 |
| 图片 5MB | 5,242,880 |

### 图片存储说明

- 上传的图片会自动转为 **WebP** 格式，体积减少约50%
- 返回的 `url` 可直接作为图片源使用
- `width` / `height` 是转换后的尺寸

### 接口完整列表

| 接口 | 认证 | 说明 |
|------|:--:|------|
| POST /upload/public | ✅ | 上传公共文件 |
| POST /upload/private | ✅ | 上传私有文件 |
| POST /upload/temp | ✅ | 上传文件到临时储存 |
| POST /image/upload/public | ✅ | 上传公共图片 |
| POST /image/upload/private | ✅ | 上传私有图片 |
| GET /download/{id} | ❌ | 下载文件 |
| GET /image/{id} | ❌ | 查看图片 |
| GET /image/{id}/download | ❌ | 下载公共图片 |
| GET /image/{id}/download/private | ✅ | 下载私有图片 |

**注意**：所有路径前缀为 `/api/file`

**已移除的API**：
- ❌ DELETE /{id} - 文件删除（已下线）
- ❌ DELETE /image/{id} - 图片删除（已下线）

**临时屏蔽的API**：
- ⚠️ GET / - 文件列表（路由已注释，暂不开放）
- ⚠️ GET /image - 图片列表（路由已注释，暂不开放）

---

**最后更新**: 2026-08-14
**版本**: v4.1（新增临时储存上传接口 /upload/temp，支持 csv 类型，返回相对路径 path，不写数据库）
