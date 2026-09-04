# Examples 目录高度统一报告

**统计时间**: 2026-08-23
**目录**: `Modules/DcatAdmin/DcatAdmin/Metrics/Examples/`

---

## 高度统一结果

### ✅ 已符合标准（无需修改）

| 文件 | 高度 | 标准 | 内容类型 |
|------|------|------|---------|
| DataLabel.php | 300px | 大型 | 图表 |
| DataLabel2.php | 300px | 大型 | 图表 |
| ListDataColor.php | 150px | 小型 | 列表 |
| Number.php | 150px | 小型 | 数字 |
| Tickets.php | 400px | 超大型 | 图表+统计 |

### ✅ 已调整高度

| 文件 | 原高度 | 新高度 | 标准 | 调整原因 |
|------|--------|--------|------|---------|
| Number1.php | 140px | **150px** | 小型 | 统一到标准 |
| NumberS2.php | 130px | **150px** | 小型 | 统一到标准 |
| SingleButtonCardExample.php | 160px | **150px** | 小型 | 统一到标准 |
| DataBar.php | 190px | **200px** | 中型 | 符合内容复杂度 |
| DataNumber.php | 220px | **200px** | 中型 | 符合内容复杂度 |
| MultiButtonCardExample.php | 180px | **200px** | 中型 | 符合内容复杂度 |
| TreeMap.php | 250px | **300px** | 大型 | 统一到标准 |

### ✅ 新增高度设置

| 文件 | 新增高度 | 标准 | 内容类型 |
|------|---------|------|---------|
| AreaChart.php | **300px** | 大型 | 面积图 |
| NewUsersDou.php | **300px** | 大型 | 多线图 |
| Sessions.php | **300px** | 大型 | 柱状图 |
| Ranking.php | **200px** | 中型 | 排行榜 |
| TotalUsers.php | **150px** | 小型 | 数字+趋势 |

### ℹ️ 特殊处理（无需设置高度）

| 文件 | 原因 |
|------|------|
| DemoRanking.php | 继承 Ranking（已设置高度）|
| NewDevices.php | 继承 Donut（基类处理）|
| ProductOrders.php | 继承 Round（基类处理）|
| Round.php | Round 的简单继承 |
| LinkA.php | 继承 Box（非 Metric 卡片）|
| Link.php | 继承 Box（非 Metric 卡片）|
| Reload.php | 继承 Widget（非 Metric 卡片）|
| WaitGo.php | 继承 Widget（非 Metric 卡片）|
| NumberS.php | 组合卡片（内部包含多个 Number）|
| NewUsers.php | 已在 init() 设置 height(300) |

---

## 四级高度标准

| 级别 | 高度 | 内容区域 | 适用场景 |
|------|------|---------|----------|
| **小型** | 150px | ~105px | 简单数字展示 |
| **中型** | 200px | ~155px | 列表展示（3-4项）|
| **大型** | 300px | ~255px | 检查展示、图表 |
| **超大型** | 400px | ~355px | 复杂内容、大数据表 |
| **超大复杂型** | 500px | ~455px | 环形图+多统计、复杂组合 |

---

## 统计汇总

- **总文件数**: 27 个
- **已符合标准**: 5 个
- **已调整高度**: 7 个
- **新增高度设置**: 5 个
- **特殊处理**: 10 个
- **统一完成率**: 100%

---

## 验证命令

```bash
# 查看所有文件高度
for file in Modules/DcatAdmin/DcatAdmin/Metrics/Examples/*.php; do
    filename=$(basename "$file")
    height=$(grep -E "(protected \$height|->height\()" "$file" | head -1 | grep -oE "[0-9]+" || echo "继承/特殊")
    echo "$filename: ${height}px"
done
```

---

**维护者**: AI 开发团队
**更新内容**: Examples 目录所有 Metric 卡片高度统一为五级标准（150px/200px/300px/400px/500px）