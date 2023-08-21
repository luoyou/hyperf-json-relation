## 介绍

本扩展基于 [staudenmeir/eloquent-json-relations](https://github.com/staudenmeir/eloquent-json-relations) ，将其对 hyperf 做了适配，使其支持 mysql 中的 json 字段的关联

源库支持 MySQL,MariaDB,postgreSQL,SQLite,SQL Server，但 hyperf 的 ORM 目前仅支持 MySQL，所以目前仅针对 MySQL 做了适配，后续 hyperf 支持其他数据库时再进行适配

本扩展为 `BelongsTo`、`HasOne`、`HasMany`、`HasOneThrough`、`HasManyThrough`、`MorphTo`、`MorphOne` 和 `MorphMany` 关系添加了对 JSON 外键的支持。

它还为 [many-to-many](#many-to-many-relationships) 和 [has-many-through](#has-many-through-relationships) 关系提供了 JSON 数组支持。

## 兼容性

| 数据库     | Hyperf |
| :--------- | :----- |
| MySQL 5.7+ | 3.0+   |

## 安装

```shell
composer require "luoyou/hyperf-json-relation:^1.0"
```

## 使用方法

- [一对多关系](#一对多关系)
- [多对多关系](#多对多关系)
  - [查询性能](#mysql-查询性能)

### 一对多关系

在这个示例中，User 与 Locale 之间有一个 BelongsTo 关系。没有专用的列，但外键（locale_id）存储在 JSON 字段中（users.options）：

```php
class User extends Model
{
    use \Luoyou\HyperfJsonRelation\HasJsonRelationships;

    protected $casts = [
        'options' => 'json',
    ];

    public function locale()
    {
        return $this->belongsTo(Locale::class, 'options->locale_id');
    }
}

class Locale extends Model
{
    use \Luoyou\HyperfJsonRelation\HasJsonRelationships;

    public function users()
    {
        return $this->hasMany(User::class, 'options->locale_id');
    }
}
```

### 多对多关系

该扩展还引入了两种新的关系类型：BelongsToJson 和 HasManyJson。

使用它们可以实现包含 JSON 数组的多对多关系。

在这个示例中，User 与 Role 之间有一个 BelongsToMany 关系。没有中间表，但外键以数组形式存储在 JSON 字段中（users.options）：

1. ID 数组
   默认情况下，关系以 ID 数组的形式存储中间记录：

```php
class User extends Model
{
    use \Luoyou\HyperfJsonRelation\HasJsonRelationships;

    protected $casts = [
       'options' => 'json',
    ];

    public function roles()
    {
        return $this->belongsToJson(Role::class, 'options->role_ids');
    }
}

class Role extends Model
{
    use \Luoyou\HyperfJsonRelation\HasJsonRelationships;

    public function users()
    {
       return $this->hasManyJson(User::class, 'options->role_ids');
    }
}

```

2. 对象数据
   还可以将中间记录存储为带有福建属性的对象：

```php
class User extends Model
{
    use \Luoyou\HyperfJsonRelation\HasJsonRelationships;

    protected $casts = [
       'options' => 'json',
    ];

    public function roles()
    {
        return $this->belongsToJson(Role::class, 'options->roles[]->role_id');
    }
}

class Role extends Model
{
    use \Luoyou\HyperfJsonRelation\HasJsonRelationships;

    public function users()
    {
       return $this->hasManyJson(User::class, 'options->roles[]->role_id');
    }
}
```

在这里，options->roles 是 JSON 数组的路径。role_id 是记录对象内部的外键属性的名称：

## MySQL 查询性能

在 MySQL 8.0.17+ 上，我们可以通过[多值索引](https://dev.mysql.com/doc/refman/8.0/en/create-index.html#create-index-multi-valued)来提高查询性能。

当数组是列本身时（例如 users.role_ids），使用以下迁移：

```php
Schema::create('users', function (Blueprint $table) {
    // ...

    // ID 数组
    $table->rawIndex('(cast(`role_ids` as unsigned array))', 'users_role_ids_index');

    // 对象数组
    $table->rawIndex('(cast(`roles`->\'$[*]."role_id"\' as unsigned array))', 'users_roles_index');
});
```

当数组嵌套在对象内部时（例如 users.options->role_ids），使用以下迁移：

```php
Schema::create('users', function (Blueprint $table) {
    // ...

    // ID 数组
    $table->rawIndex('(cast(`options`->\'$."role_ids"\' as unsigned array))', 'users_role_ids_index');

    // 对象数组
    $table->rawIndex('(cast(`options`->\'$."roles"[*]."role_id"\' as unsigned array))', 'users_roles_index');
});
```

创建多值索引与普通的索引不同，其中在已经运行的业务上添加多值索引后，很可能引起当前代码插入报错（未满足 json 字段转换为多值索引的结果要求）,查询的时候只有`member of`,`json_contains`,`json_overlaps`三个函数可以走到索引，添加索引前请务必仔细测试代码，避免插入数据报错，通过 explain 来检查能否走到索引。

---

更多详细的使用文档，请参照源代码库：https://github.com/staudenmeir/eloquent-json-relations
