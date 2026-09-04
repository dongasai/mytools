# 内置验证器和过滤器参考

## 内置验证器

| 验证器 | 说明 | 示例 |
|--------|------|------|
| `required` | 必填，不为空 | `['field', 'required']` |
| `int/integer` | 整数，支持范围 | `['field', 'int', 'min'=>1, 'max'=>100]` |
| `num/number` | 大于0的整数 | `['field', 'number']` |
| `bool/boolean` | 布尔值 | `['field', 'bool']` |
| `float` | 浮点数 | `['field', 'float']` |
| `string` | 字符串，支持长度 | `['field', 'string', 'min'=>5, 'max'=>100]` |
| `accepted` | 必须为 yes/on/1/true | `['agree', 'accepted']` |
| `url` | URL 格式 | `['field', 'url']` |
| `email` | 邮箱格式 | `['field', 'email']` |
| `alpha` | 仅字母 | `['field', 'alpha']` |
| `alphaNum` | 仅字母和数字 | `['field', 'alphaNum']` |
| `alphaDash` | 仅字母、数字、破折号、下划线 | `['field', 'alphaDash']` |
| `array/isArray` | 数组 | `['field', 'isArray']` |
| `map/isMap` | 关联数组（key-value） | `['field', 'isMap']` |
| `list/isList` | 自然数组（key从0开始） | `['field', 'isList']` |
| `each` | 数组每个元素验证 | `['goods.*', 'each', 'string']` |
| `hasKey` | 数组存在指定key | `['field', 'hasKey', 'key']` |
| `distinct` | 数组值唯一 | `['field', 'distinct']` |
| `ints/intList` | 整数列表 | `['field', 'intList']` |
| `strings/strList` | 字符串列表 | `['field', 'strList']` |
| `min` | 最小值/长度 | `['field', 'min', 10]` |
| `max` | 最大值/长度 | `['field', 'max', 100]` |
| `size/range/between` | 范围验证 | `['field', 'size', 'min'=>1, 'max'=>100]` |
| `length` | 长度验证 | `['field', 'length', 'min'=>5, 'max'=>20]` |
| `fixedSize` | 固定长度/大小 | `['field', 'fixedSize', 12]` |
| `startWith` | 以指定值开始 | `['field', 'startWith', 'prefix']` |
| `endWith` | 以指定值结束 | `['field', 'endWith', 'suffix']` |
| `in/enum` | 在枚举值中 | `['field', 'in', [1,2,3]]` |
| `notIn` | 不在枚举值中 | `['field', 'notIn', [4,5,6]]` |
| `inField` | 值存在于另一字段中 | `['field', 'inField', 'anotherField']` |
| `eq/mustBe` | 必须等于 | `['field', 'mustBe', 1]` |
| `ne/notBe` | 不能等于 | `['field', 'notBe', 0]` |
| `eqField` | 等于另一字段 | `['passwd', 'eqField', 'repasswd']` |
| `neqField` | 不等于另一字段 | `['field', 'neqField', 'another']` |
| `ltField` | 小于另一字段 | `['field1', 'ltField', 'field2']` |
| `gtField` | 大于另一字段 | `['field1', 'gtField', 'field2']` |
| `requiredIf` | 条件必填 | `['city', 'requiredIf', 'myCity', ['chengdu']]` |
| `requiredWith` | 有指定字段时必填 | `['city', 'requiredWith', ['field']]` |
| `requiredWithout` | 缺少指定字段时必填 | `['city', 'requiredWithout', ['field']]` |
| `date` | 日期格式 | `['field', 'date']` |
| `dateFormat` | 指定日期格式 | `['field', 'dateFormat', 'Y-m-d']` |
| `beforeDate` | 日期在指定日期之前 | `['field', 'beforeDate', '2025-01-01']` |
| `afterDate` | 日期在指定日期之后 | `['field', 'afterDate', '2025-01-01']` |
| `json` | JSON字符串 | `['field', 'json']` |
| `ip` | IP地址 | `['field', 'ip']` |
| `ipv4` | IPv4地址 | `['field', 'ipv4']` |
| `ipv6` | IPv6地址 | `['field', 'ipv6']` |
| `regex/regexp` | 正则验证 | `['field', 'regexp', '/^\w+$/']` |
| `safe` | 标记安全字段 | `['createdAt', 'safe']` |

## 内置过滤器

| 过滤器 | 说明 | 示例 |
|--------|------|------|
| `abs` | 绝对值 | `'filter' => 'abs'` |
| `int/integer` | 转整数，支持数组 | `'filter' => 'int'` |
| `bool/boolean` | 转布尔 | `'filter' => 'bool'` |
| `float` | 转浮点数 | `'filter' => 'float'` |
| `string` | 转字符串 | `'filter' => 'string'` |
| `trim` | 去除首尾空格，支持数组 | `'filter' => 'trim'` |
| `nl2br` | 换行转 `<br/>` | `'filter' => 'nl2br'` |
| `lower/lowercase` | 转小写 | `'filter' => 'lowercase'` |
| `upper/uppercase` | 转大写 | `'filter' => 'uppercase'` |
| `snake/snakeCase` | 转蛇形风格 | `'filter' => 'snakeCase'` |
| `camel/camelCase` | 转驼峰风格 | `'filter' => 'camelCase'` |
| `timestamp/strToTime` | 日期字符串转时间戳 | `'filter' => 'strToTime'` |
| `url` | URL过滤 | `'filter' => 'url'` |
| `email` | Email过滤 | `'filter' => 'email'` |
| `str2list/str2array` | 字符串转数组 | `'filter' => 'str2array'` |
| `unique` | 数组去重 | `'filter' => 'unique'` |
| `clearSpace` | 清理空格 | `'filter' => 'clearSpace'` |
| `clearNewline` | 清理换行符 | `'filter' => 'clearNewline'` |
| `clearTags/stripTags` | 清理HTML标签 | `'filter' => 'stripTags'` |
| `escape/specialChars` | HTML转义 | `'filter' => 'specialChars'` |
| `quotes` | SQL转义 | `'filter' => 'quotes'` |

## 多个过滤器组合

使用 `|` 分隔多个过滤器：

```php
['field', 'string', 'filter' => 'trim|lowercase']
```

或使用数组配置：

```php
['field', 'string', 'filter' => [
    'trim',
    'lowercase',
    function($val) {
        return str_replace(' ', '', $val);
    }
]]
```

## 数组字段验证

### 子级字段验证
```php
// 数据：['goods' => ['apple' => 34, 'pear' => 50]]
['goods.pear', 'max', 30]
```

### 通配符遍历验证
```php
// 数据：['users' => [['id'=>34], ['id'=>89]]]
['users.*.id', 'each', 'required']
['users.*.id', 'each', 'number', 'min' => 1]
```