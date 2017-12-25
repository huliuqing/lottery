# lottery
一个经典抽奖概率算法实现

## 使用

1. make 以默认配置及实例化名称实例化

```
// 抽奖配置
$settings = array(
    array("id" => 1, 'weight' => 10, 'score' => 1),
    array("id" => 2, 'weight' => 20, 'score' => 2),
    array("id" => 3, 'weight' => 30, 'score' => 3),
);
$result = Lottery::make()->go($settings);
```

2. make 设置自定义配置选项并实例化
将开启内部调试输出工具，默认关闭

```
// 抽奖配置
$settings = array(
    array("id" => 1, 'weight' => 10, 'score' => 1),
    array("id" => 2, 'weight' => 20, 'score' => 2),
    array("id" => 3, 'weight' => 30, 'score' => 3),
);
$result = Lottery::make($config = array("debug" => true))->go($settings);
```

3. make 设置实例名称并实例化

```
// 抽奖配置
$settings = array(
    array("id" => 1, 'weight' => 10, 'score' => 1),
    array("id" => 2, 'weight' => 20, 'score' => 2),
    array("id" => 3, 'weight' => 30, 'score' => 3),
);

$result = Lottery::make($name = "instance", $config = array("debug" => true))->go($settings);
```

4. make 设置配置选项和实例名称并实例化
```
$settings = array(
    array("id" => 1, 'weight' => 10, 'score' => 1),
    array("id" => 2, 'weight' => 20, 'score' => 2),
    array("id" => 3, 'weight' => 30, 'score' => 3),
);

$result = Lottery::make($name = "instance", $config = array("debug" => true))->go($settings);

// 或

$result = Lottery::make($config = array("debug" => true), $name = "inatance")->go($settings);
```

## 配置选项
```
$config = array(
    "indexField" => 'pid',  // 抽奖配置中的主键字段，默认 id
    "weightField" => "weights", // 抽奖配置中的权重字段，默认 weight
    "debug" => true, //开启调试，默认关闭
);
```

## 方法
1. 设置主键字段和权重字段
```
setFields($indexField = 'id', $weightField = 'weight') 
```

2. 开启/关闭 调试
```
debug($boolean = true) 
```

3. 执行抽奖
```
go($settings)
```
