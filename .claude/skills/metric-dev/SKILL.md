# Dcat Admin Metric 开发技能

创建/修改 Dcat Admin 超管后台的 Metric 统计卡片/图表。

## 怎么选基础 Metric

根据数据形态选类型，全部示例在 `Modules/DcatAdmin/DcatAdmin/Metrics/Examples/`：

| 数据形态 | 选什么 | 继承基类 | 示例文件 | column |
|---------|--------|---------|---------|--------|
| 单个数字 | Number1 | `Card` | Number1.php | 1-2 |
| 数字+趋势折线 | Line | `Line` | NewUsers.php | 4-6 |
| 累积趋势(填充) | AreaChart | `Line` | AreaChart.php | 4-6 |
| 多条折线对比 | Line | `Line` | NewUsersDou.php | 4-6 |
| 数值+柱状对比 | Bar | `Bar` | Sessions.php | 4 |
| 横向条形 | DataBar | `RadialBar` | DataBar.php | 4 |
| 占比(甜甜圈) | Donut | `Donut` | NewDevices.php | 4 |
| 多状态环比例 | Round | `Round` | ProductOrders.php | 4 |
| 单环进度+底部统计 | RadialBar | `RadialBar` | Tickets.php | 4 |

选择前先看 `Modules/DcatAdmin/DcatAdmin/Metrics/图表.md` 的图表选择表。

## 怎么实现

### 步骤
1. 复制最接近的示例文件，改类名和命名空间
2. `init()` 改 title，设置高度，需要时间范围筛选就加 `dropdown()`
3. `handle()` 调用 Service 获取真实数据，调用 `withContent()` + `withChart()`
4. `withContent()` / `withChart()` 只改数据变量，**保留 HTML 结构和 class 不动**

### 通用结构（除 Donut 外）

```php
class XxxMetric extends Card  // 基类按类型选
{
    protected function init()
    {
        parent::init();
        $this->title('名称');
        $this->height(150);  // ⚠️ 必须设置高度
        $this->dropdown(['7' => '近7天', '30' => '近1月']);  // 可选
    }

    public function handle(Request $request)
    {
        $data = XxxService::get($request->get('option', 7));
        $this->withContent($data['total']);
        $this->withChart($data['series']);
    }

    public function withChart(array $data)
    {
        return $this->chart([/* 从示例复制 */]);
    }

    public function withContent($content)
    {
        return $this->content(<<<HTML
<!-- 从示例复制 HTML 结构 -->
HTML
        );
    }
}
```

### NumberS 特殊（嵌套子卡片，400px）

```php
/**
 * 嵌套多个 Number 的卡片
 * 高度计算：Number 高度(150) * n + 85
 */
class NumberS extends Card
{
    protected $height = 400;  // 2 个 Number: 150 * 2 + 85 = 385 ≈ 400

    public function handle(Request $request): void
    {
        $this->withContent([
            '统计' => 162,
            '近日' => 75,
        ]);
    }

    public function withContent(array $contents)
    {
        $string = '';
        foreach ($contents as $title => $number) {
            $c = new Number($title);
            $c->withContent($number);
            $string .= $c->render();
        }

        return $this->content($string);
    }
}
```

### Tickets 特殊（环形图+多统计，500px）

```php
class XxxMetric extends RadialBar
{
    protected function init(): void
    {
        parent::init();
        $this->title('名称');
        $this->height(500);  // 卡片总高度 500px
        $this->chartHeight(300);  // 图表高度
        $this->dropdown(['7' => '近7天', '30' => '近1月']);  // 可选
    }

    public function handle(Request $request): void
    {
        $data = XxxService::get($request->get('option', 7));
        $this->withContent($data['total']);
        $this->withChart($data['percent']);
        $this->withFooter($data['stats']);  // 底部多个统计
    }

    public function withChart(int $data)
    {
        return $this->chart([
            'series' => [$data],
        ]);
    }

    public function withContent($content)
    {
        return $this->content(<<<HTML
<!-- 从示例复制 HTML 结构 -->
HTML
        );
    }

    public function withFooter($stat1, $stat2, $stat3)
    {
        return $this->footer(<<<HTML
<div class="d-flex justify-content-between p-1">
    <div class="text-center">
        <p>统计1</p>
        <span class="font-lg-1">{$stat1}</span>
    </div>
    <div class="text-center">
        <p>统计2</p>
        <span class="font-lg-1">{$stat2}</span>
    </div>
    <div class="text-center">
        <p>统计3</p>
        <span class="font-lg-1">{$stat3}</span>
    </div>
</div>
HTML
        );
    }
}
```

## 高度规范（重要）

### 核心理解

**`height()` 方法设置的是整个卡片的 BOX 总高度，不是内容高度！**

```
总高度 = 标题栏（~45px）+ 内容区域
```

### 四级高度标准

参考 `Modules/DcatAdmin/DcatAdmin/Metrics/HEIGHT_STANDARDS.md`

| 级别 | 卡片总高度 | 内容区域 | 适用场景 | 行数建议 |
|------|----------|---------|----------|---------|
| **小型** | 150px | ~105px | 简单数字展示 | 2行 |
| **中型** | 200px | ~155px | 列表展示 | 3-4行 |
| **大型** | 300px | ~255px | 检查展示、图表 | 4-6行 |
| **超大型** | 400px | ~355px | 复杂内容、大数据表 | 6+行 |
| **超大复杂型** | 500px | ~455px | 环形图+多统计、复杂组合 | 极复杂 |
| **嵌套型** | 计算值 | 动态 | 嵌套子卡片 | 按公式计算 |

### 内容区域有限

小型卡片只有 **~105px** 的内容空间，设计样式时必须紧凑：

```php
// ✅ 推荐：小型卡片紧凑样式（150px）
<div style="padding: 0.5rem; line-height: 1.3;">
    <div class="text-warning mb-2">
        <i class="fa fa-bolt" style="font-size: 1.75rem;"></i>
    </div>
    <div style="font-size: 1.5rem; font-weight: 500;">3 种</div>
</div>

// ✅ 推荐：中型卡片适中样式（200px）
<div style="padding: 0.75rem; line-height: 1.4;">
    <div class="list-group list-group-flush">
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted">项目1</span>
            <span class="text-primary">值1</span>
        </div>
        <!-- 3-4 个列表项 -->
    </div>
</div>

// ✅ 推荐：大型卡片舒适样式（300px）
<div style="padding: 1rem; line-height: 1.5;">
    <div class="list-group list-group-flush">
        <div class="list-group-item d-flex justify-content-between py-2 px-3">
            <span class="text-muted">项目1</span>
            <span class="text-primary">值1</span>
        </div>
        <!-- 4-6 个列表项 -->
    </div>
</div>

// ❌ 避免：撑开高度
<div class="card-body">  <!-- 默认 padding 太大 -->
    <h2>...</h2>  <!-- 默认间距大 -->
    <i class="fa fa-2x"></i>  <!-- 图标太大 -->
</div>
```

### 选择建议

根据内容复杂度选择高度级别：
- **2 行内容** → 150px（小型）
- **3-4 行列表** → 200px（中型）
- **4-6 行列表/图表** → 300px（大型）
- **复杂内容/大数据表** → 400px（超大型）
- **环形图+多统计/复杂组合** → 500px（超大复杂型）
- **嵌套子卡片** → 按公式计算：`子卡片高度 * n + 85`

## PHP 8.4 类型声明限制

**如果父类属性没有类型声明，子类也不能添加类型声明！**

```php
// ❌ 错误：父类 Card 的 $height 无类型，子类不能添加
protected int $height = 150;

// ✅ 正确：保持无类型声明
protected $height = 150;

// ✅ 或在 init() 中设置
$this->height(150);
```

## 数据库操作规范

### 使用 Model 而非 DB::table()

```php
// ✅ 推荐：使用 Model
$record = ContinuousTimes::create([...]);
$readRecord = ContinuousTimes::find($testId);
$readRecord->forceDelete();

// ❌ 避免：直接 DB 操作
DB::table('application_continuous_times')->insertGetId([...]);
```

### 事务处理要完整

```php
DB::transaction(function () use (&$result) {
    // Create
    $record = Model::create([...]);

    // Read
    $readRecord = Model::find($record->id);
    if ($readRecord) {
        // Update
        $readRecord->update([...]);

        // Delete
        $readRecord->forceDelete();
        $result['crud_test'] = '通过';
    } else {
        // ⚠️ Read 失败必须抛异常，让事务回滚
        throw new \RuntimeException('CRUD Read test failed');
    }
});
```

## 安全规范

### XSS 防护

```php
// ✅ 必须转义所有变量输出
$connection = e($content['connection']);
$tables = e((string) $content['tables']);

return $this->content(<<<HTML
<div class="text-primary">{$connection}</div>
HTML
);
```

### 异常处理

**遵循项目规范"非必要不能 try-catch，会掩盖错误"**

```php
// ❌ 禁止：吞掉异常
try {
    DB::connection()->getPdo();
} catch (\Exception $e) {
    $result['connection'] = '异常: ' . $e->getMessage();  // 信息泄露
}

// ✅ 正确：让异常自然抛出，由全局处理器接管
DB::connection()->getPdo();
```

## 注意事项

- **handle() 不 return** — 直接调 withContent/withChart
- **不调 content()/chart() 直接传值** — 必须经 withContent/withChart 包装 HTML
- **不改 withContent 的 HTML class** — 只替换数据变量，样式已封装好
- **不用 icon()** — 会生成奇怪的圆形头像容器
- **不自己写样式** — 只用示例里的 Bootstrap class
- **dropdown 选项** — handle() 里用 `$request->get('option')` 读取，default 分支兜底
- **Service 静态方法** — 不在 Metric 里直接查数据库
- **代码必须有 PHPDoc** — 符合项目规范
- **必须设置高度** — 避免内容撑开卡片
- **使用 Dcat Admin 的 CSS 类** — 如 `card-body`、`list-group` 等

## 常见问题

### 卡片高度不一致

原因：未设置 `height()` 或内容撑开

解决：
1. 在 `init()` 中设置高度
2. 精简内容样式，使用紧凑布局
3. 参考高度标准文档

### 内容撑开卡片

原因：padding、margin、图标太大

解决：
1. 使用 inline style 精确控制 padding
2. 图标用 `font-size: 1.5rem` 或 `fa-lg`
3. 数字用 `div` + `font-size`，避免 `h1-h3`
4. 行间距用 `line-height: 1.2`

### 类型声明错误

原因：PHP 8.4 严格限制，父类无类型子类不能添加

解决：移除属性的类型声明，或在 `init()` 中使用 `height()` 方法

---

**更新时间**: 2026-08-23
**维护者**: AI 开发团队
**更新内容**: 新增高度规范、PHP 8.4 限制、数据库操作规范、安全规范、常见问题