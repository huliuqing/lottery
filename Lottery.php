<?php

namespace PhpZendo;


/**
* 抽奖算法
* 
* @version 1.0 2017-05-17
* @author huliuqing huiuqing@droi.com
*/
class Lottery
{
    private $data = array();

    /**
     * [$debug 是否开启调试功能]
     * @var boolean
     */
    private $debug = false;

    /**
     * [$config 默认配置]
     * @var array
     */
    private $config = array(
		'indexField'  => 'id',
		'weightField' => 'weight',
		'debug'       => false,
    );

    /**
     * [$indexField 识别抽奖配置的主键字段名]
     * @var string
     */
    private $indexField   = 'id';

    /**
     * [$weightField 识别抽奖配置的权重字段名]
     * @var string
     */
    private $weightField = 'weight';

    /**
     * [$instance 实例]
     * @var array
     */
    private static $instance = array();

    /**
     * [make 实例化]
     * @param  string $name   [实例名]
     * @param  array  $config [配置参数]
     * @return [Lottery]         [description]
     */
    public static function make($name = "self", $config = array())
    {
		$parameters = self::getMakeRealArgs(func_num_args(), func_get_args());

		$realName   = $parameters['name'];
		$userConfig = $parameters['config'];

        if (isset(self::$instance[$realName])) {
            return self::$instance[$realName];
        }

        self::$instance[$realName] = new static();

        return self::$instance[$realName]->setConfig($userConfig);
    }

    /**
     * [getMakeRealArgs 解析方法参数，获取实际参数]
     * @param  [type] $count [description]
     * @param  [type] $args  [description]
     * @return [type]        [description]
     */
    private static function getMakeRealArgs($count, $args)
    {
    	$paramaters = array(
    		'name'   => 'self',
    		'config' => array(),
    	);

    	switch ($count) {
    		case 0:
    			return $paramaters;

    		case 1:
    			$arg = $args[0];
    			if (is_string($arg)) {
    				$paramaters['name'] = $arg;
    			}

    			if (is_array($arg)) {
    				$paramaters['config'] = $arg;    				
    			}

    			return $paramaters;

    		case 2:
    			$arg1 = $args[0];
    			$arg2 = $args[1];

    			if (is_string($arg1)) {
					$paramaters['name']   = $arg1;
					$paramaters['config'] = $arg2;
    			} else if (is_string($arg2)) {
					$paramaters['name']   = $arg2;
					$paramaters['config'] = $arg1;
    			}

    			return $paramaters;

    		default:
    			return $paramaters;
    	}
    }

    /**
     * [setConfig 配置设置]
     * @param [type] $config [配置设置]
     */
    private function setConfig($config)
    {
    	$this->config = array_merge($this->config, $config);

    	$this->setFields($this->config['indexField'], $this->config['weightField']);
    	$this->debug($this->config['debug']);

    	return $this;
    }

    public function setFields($indexField = 'id', $weightField = 'weight')
    {
        $this->setIndexField($indexField);
        $this->setWeightField($weightField);

        return $this;
    }

    public function setIndexField($indexField)
    {
        $this->indexField = $indexField;
    }

    public function setWeightField($weightField)
    {
        $this->weightField = $weightField;
    }

    /**
     * go 执行抽奖操作
     * @param array $pSetting 抽奖概率配置数据
     */
    public function go($pSetting)
    {
        $this->data['setting'] = $pSetting;

        $this->reindexSettings($pSetting);

        $id = $this->random($this->data['reindexSettings']);

        $this->data['prize'] = $this->data['reindexSettings'][$id];

        $this->dump($this->data['prize']);

        return $this->data['prize'];

    }

    /**
     * [reindexSettings 格式化配置数据]
     * 格式化后
     * $setting = array(
     *      '0' => array('id'=>1, 'v'=>1),
     *      '1' => array('id'=>2, 'v'=>5),
     *      '2' => array('id'=>3, 'v'=>10),
     *      '3' => array('id'=>4, 'v'=>12),
     *      '4' => array('id'=>5, 'v'=>22),
     *      '5' => array('id'=>6, 'v'=>50),
     *  );
     * @param  [type] $pSetting [description]
     * @return [type]           [description]
     */
    private function reindexSettings($pSetting)
    {
        $settings = array();

        foreach ($pSetting as $key => $val) {
        	if (!array_key_exists($this->indexField, $val)) {
        		$this->exception(sprintf("字段 [%s] 不存在!", $this->indexField));
        	}

            // $settings[$val['gs_id']]['id'] = $val['gs_id'];
            // $settings[$val['gs_id']]['v']  = $val['gs_weight'];

            $settings[$val[$this->indexField]] = $val;
        }

        $this->data['reindexSettings'] = $settings;
    }

    /**
     * [generateWeights 将奖品数据转换为 id => weight 的索引数组]
     * @param  [type] $pSetting [description]
     * @return [type]           [description]
     */
    private function generateWeights($pSetting)
    {
        $weights = array();

        foreach ($pSetting as $idx => $val) {
        	if (!array_key_exists($this->indexField, $val)) {
        		$this->exception(sprintf("字段 [%s] 不存在!", $this->indexField));
        	}

        	if (!array_key_exists($this->weightField, $val)) {
        		$this->exception(sprintf("字段 [%s] 不存在!", $this->weightField));
        	}

            $weights[$val[$this->indexField]] = $val[$this->weightField];
        }

        return $weights;
    }

    /**
     * random 随机获取配置 ID
     * 
     * 抽奖概率算法
     * 经典的概率算法，
     * $weights是一个预先设置的数组，
     * 假设数组为：array(100,200,300,400)，
     * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内，
     * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间，
     * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。
     * 这样 筛选到最终，总会有一个数满足要求。
     * 就相当于去一个箱子里摸东西，
     * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。
     * 这个算法简单，而且效率非常 高，
     * 关键是这个算法已在我们以前的项目中有应用，尤其是大数据量的项目中效率非常棒。
     * 
     * @param  [array] $settings [格式化后配置]
     * @return [int]             [随机 ID]
     */
    private function random($settings)
    {
		$id        = 0;
		$weights   = $this->generateWeights($settings);		
		$sumWeight = array_sum($weights);

        //概率数组循环
        foreach ($weights as $idx => $weight) {
            $random = mt_rand(1, $sumWeight);

            if ($random <= $weight) {
                $id = $idx;
                break;

            } else {
                $sumWeight -= $weight;
            }
        }

        return $id;
    }

    /**
     * [debug 开启/关闭 调试]
     * @param  boolean $boolean [description]
     * @return [type]           [description]
     */
    public function debug($boolean = true)
    {
        $this->debug = $boolean;

        return $this;
    }

    public function dump($data, $title = "DEBUG")
    {
        if (! $this->debug) {
            return;
        }
        
        echo("<span style='color:green'> ------ [". $title ."] ------ </span><br/>");

        echo("<pre>");

        if (is_string($data)) {
	        echo $data;
        } else {
			var_dump($data);        	
        }

        echo("</pre>");
        echo("<span style='color:green'> ------ [". $title ."] END ------ </span><br/>");
    }

    private function exception($message)
    {
    	throw new \Exception($message);
    }
}
