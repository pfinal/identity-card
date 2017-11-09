中国（大陆）公民身份证类

> 安装

```
composer require pfinal/identity-card
```


```php

<?php

require_once __DIR__ . '/vendor/autoload.php';

use PFinal\IdentityCard\IDCard;

//是否有效
$isPass = IDCard::validate('440105199101301233');

//生日
$birthday = IDCard::getBirthday('440105199101301233');

//性别
$gender = IDCard::getGender('440105199101301233');

//地区信息
//需要开启了pdo sqlite扩展
//composer require "douyasi/identity-card"
$area = IDCard::getArea('440105199101301233');


```