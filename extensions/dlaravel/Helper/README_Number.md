# NumberWan 万分位数字助手类使用说明

## 概述

`DLaravel\Helper\NumberWan` 类专注于万分位和中文数位单位的数字格式化、转换功能。

**✨ 核心特性**：
- 支持40位精度数值处理（20位整数 + 20位小数）
- 使用BC数学函数确保精度不丢失
- 专注于万分位转换，移除千分位相关功能
- 支持中文大数位单位（万、亿、兆、京、垓）
- 简洁专一的设计，避免功能冗余

## 主要功能

### 1. 万分位数据表示转换 `formatToWan()`

支持40位精度的万分位转换：

```php
use DLaravel\Helper\NumberWan;

// 超大精度数值转换
echo NumberWan::formatToWan('12345678901234567890.12345678901234567890');
// 输出: 1234567890123456万7890.123456789

echo NumberWan::formatToWan('999999999999999999990000.12345678901234567890');
// 输出: 99999999999999999999万.123456789

// 指定小数精度
echo NumberWan::formatToWan('100020.123456789', 5);
// 输出: 10万20.12345
```

### 2. 万分位反向转换 `parseFromWan()`

将万分位表示转换回高精度数字：

```php
echo NumberWan::parseFromWan('10万20');   // 输出: 100020
echo NumberWan::parseFromWan('5万');      // 输出: 50000
echo NumberWan::parseFromWan('1万');      // 输出: 10000
echo NumberWan::parseFromWan('12万3456'); // 输出: 123456
echo NumberWan::parseFromWan('-10万20');  // 输出: -100020
echo NumberWan::parseFromWan('1万.123456789'); // 输出: 10000.123456789
```



### 3. 智能格式化 `smartFormat()`

根据数字大小自动选择合适的格式化方式：

```php
// 大于等于1万使用万分位表示
echo NumberWan::smartFormat('12345678901234567890.12345678901234567890');
// 输出: 1234567890123456万7890.123456789

// 小于1万直接显示
echo NumberWan::smartFormat('9999.99999999999999999999');
// 输出: 9999.99999999999999999999

// 启用中文数位单位
echo NumberWan::smartFormat('12345678901234567890', 20, true);
// 输出: 1234京5678901234567890
```

### 4. 精度验证和处理

#### 精度验证 `validate()`

```php
// 验证数字是否符合精度要求（默认20位整数+20位小数）
echo NumberWan::validate('12345678901234567890.12345678901234567890'); // true
echo NumberWan::validate('123456789012345678901.12345678901234567890'); // false (21位整数)

// 自定义精度要求
echo NumberWan::validate('1234567890.1234567890', 10, 10); // true
```

#### 精度截取 `truncate()`

```php
// 截取到指定精度（默认20位整数+20位小数）
echo NumberWan::truncate('123456789012345678901.123456789012345678901');
// 输出: 23456789012345678901.1234567890123456789

// 自定义精度截取
echo NumberWan::truncate('1234567890.1234567890', 5, 5);
// 输出: 67890.12345
```

### 5. 中文大数位单位转换

#### 中文数位单位转换 `formatToChineseUnits()`

支持万、亿、兆、京、垓等中文数位单位：

```php
// 完整模式显示所有单位
echo NumberWan::formatToChineseUnits('12345678901234567890');
// 输出: 1234京5678兆9012亿3456万7890

echo NumberWan::formatToChineseUnits('123456789012345678901234');
// 输出: 1234垓5678京9012兆3456亿7890万1234

// 简化模式只显示最大单位
echo NumberWan::formatToChineseUnits('12345678901234567890', 20, true);
// 输出: 1234京5678901234567890

// 带小数的转换
echo NumberWan::formatToChineseUnits('12345678901234567890.123456789');
// 输出: 1234京5678兆9012亿3456万7890.123456789
```

#### 智能中文数位单位转换 `formatToSmartChineseUnits()`

自动选择合适的中文单位显示：

```php
echo NumberWan::formatToSmartChineseUnits('123456789');
// 输出: 1亿23456789

echo NumberWan::formatToSmartChineseUnits('12345678901234567');
// 输出: 1京2345678901234567

echo NumberWan::formatToSmartChineseUnits('9999');
// 输出: 9999 (小于万直接显示)
```

#### 中文数位单位反向转换 `parseFromChineseUnits()`

将中文数位单位表示转换回数字：

```php
echo NumberWan::parseFromChineseUnits('1万2345');
// 输出: 12345

echo NumberWan::parseFromChineseUnits('12亿3456万7890');
// 输出: 1234567890

echo NumberWan::parseFromChineseUnits('1234京5678兆9012亿3456万7890');
// 输出: 12345678901234567890

echo NumberWan::parseFromChineseUnits('1万.123456789');
// 输出: 10000.123456789
```

#### 支持的中文数位单位

| 单位 | 名称 | 数值 |
|------|------|------|
| 万 | 万 | 10^4 |
| 亿 | 亿 | 10^8 |
| 兆 | 兆 | 10^12 |
| 京 | 京 | 10^16 |
| 垓 | 垓 | 10^20 |

## 在后台控制器中的使用示例

### 替换现有的 number_format

原代码：
```php
$balance = number_format($account->balance);
```

使用万分位表示：
```php
use DLaravel\Helper\Number;

$balance = Number::formatToWan($account->balance);
```

### 在Grid中使用

```php
// 使用万分位表示
$grid->column('balance', '余额')->display(function ($value) {
    return Number::formatToWan($value);
});

// 使用高精度万分位表示
$grid->column('balance', '余额')->display(function ($value) {
    return Number::formatToWanPrecision($value);
});

// 使用中文数位单位
$grid->column('amount', '金额')->display(function ($value) {
    return Number::formatToSmartChineseUnits($value);
});

// 智能格式化（启用中文数位单位）
$grid->column('total', '总计')->display(function ($value) {
    return Number::smartFormatPrecision($value, true, 20, true);
});
```

### 在Show页面中使用

```php
// 使用万分位表示
$show->field('balance', '余额')->as(function ($value) {
    return Number::formatToWan($value);
});

// 使用中文数位单位
$show->field('amount', '金额')->as(function ($value) {
    return Number::formatToChineseUnits($value, 20, true);
});
```

## 特性

### 高精度特性
- **40位精度支持**：支持20位整数 + 20位小数的超大精度数值
- **BC数学函数**：使用BC数学函数确保精度不丢失
- **精度验证**：提供精度验证和截取功能
- **高精度格式化**：千分位和万分位格式化都支持高精度

### 通用特性
- **支持多种数据类型**：整数、浮点数、字符串数字
- **处理负数**：自动识别和处理负数
- **小数支持**：保留小数部分并智能格式化
- **双向转换**：支持数字到万分位和万分位到数字的转换
- **智能选择**：根据数字大小自动选择最合适的显示方式
- **中文友好**：使用中文"万"字符，符合中文数字表示习惯
- **向后兼容**：保留原有方法，新增高精度版本

## 注意事项

### 高精度方法
1. 高精度方法使用字符串进行数值传递和返回，避免精度丢失
2. 默认支持20位整数和20位小数，可通过参数自定义
3. 万分位转换只在数字大于等于10000时生效
4. 小数部分自动去除末尾的0，保持简洁显示

### 兼容方法
1. 万分位转换只在数字大于等于10000时生效
2. 小数部分会保留最多2位小数，自动去除末尾的0
3. 反向转换支持标准的万分位格式字符串
4. 所有方法都是静态方法，可以直接调用

## 性能建议

1. **超大数值**：使用高精度方法（`*Precision`）
2. **普通数值**：使用兼容方法，性能更好
3. **批量处理**：考虑精度需求选择合适的方法
4. **存储建议**：超大精度数值建议以字符串形式存储
