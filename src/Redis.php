<?php
namespace clown\redis;


class Redis
{
    /**
     * @var string 前缀
     */
    public $prefix = '';

    /**
     * @var int 链接超时时间
     */
    public $timeout = 0;

    /**
     * @var int 缓存到期时间 0不到期
     */
    public $expire = 0;

    /**
     * @var \Redis 当前链接
     */
    public $connect;



    /**
     * 初始化 .env文件配置前缀
     * [CACHE]
     *   DRIVER = redis
     *   HOST = redis
     *   PORT = 6379
     *   PASSWORD =
     *   SELECT = 0
     *   TIMEOUT = 0
     *   EXPIRE = 0
    PREFIX = EVTP6_
     * @param $host string 主机
     * @param $port string 端口
     * @param $password string 密码
     * @param $select int 查询数据库
     * @param $timeout int 链接超时时间
     * @param $exprie int 缓存超市时间
     * @param $prefix string 前缀
     * @throws \Exception
     * @return \Redis
     */
    public function __construct($host = '', $port = '', $password = '', $select = 0, $timeout = 0, $exprie = 0, $prefix = '')
    {
        //主机地址
        if(empty($host)){
            $host = env('REDIS_HOST');
            if(!$host) throw new \Exception('请传入host或在.env文件下配置REDIS_HOST参数');
        }
        //端口
        if(empty($port)){
            $port = env('REDIS_PORT');
            if(!$port) throw new \Exception('请传入port或在.env文件下配置REDIS_PORT参数');
        }
        //密码
        if(empty($password)){
            $password = env('REDIS_PASSWORD');
        }
        //要查询的表
        if(empty($select)){
            $select = env('REDIS_SELECT');
        }
        //超时时间
        if(empty($timeout)){
            $timeout = env('REDIS_TIMEOUT');
        }
        //超时时间
        if(empty($exprie)){
            $exprie = env('REDIS_EXPIRE');
        }
        //前缀
        if(empty($prefix)){
            $prefix = env('REDIS_PREFIX');
        }

        $this->prefix = $prefix;

        $this->expire = $exprie;

        $this->timeout = $timeout;
        //实例化redis
        $this->connect = new \Redis();
        //创建链接
        $this->connect->connect($host, $port);
        //密码不为空就授权
        !empty($password) && $this->connect->auth($password);
        //要查询的数据库
        $this->connect->select($select);

        return $this->connect;
    }


}