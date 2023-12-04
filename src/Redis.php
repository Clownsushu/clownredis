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
    private $connect;



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
            $password = env('REDIS_PASS', '');
        }
        //要查询的表
        if(empty($select)){
            $select = env('REDIS_SELECT', 0);
        }
        //超时时间
        if(empty($timeout)){
            $timeout = env('REDIS_TIMEOUT', 0);
        }
        //缓存过期时间
        if(empty($exprie)){
            $exprie = env('REDIS_EXPIRE', 0);
        }
        //前缀
        if(empty($prefix)){
            $prefix = env('REDIS_PREFIX', '');
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

    /**
     * 获取锁
     * @param $key string 键名
     * @param $timeout int 锁超时时间
     * @return bool|\Redis
     */
    public function getLock($key = '', $timeout = 10)
    {
        $key = $this->prefix . $key;
        return $this->connect->set($key, 1, ['NX', 'EX' => $timeout]);
    }

    /**
     * 利用lua脚本进行原子解锁
     * @param $key string 需要解锁的键名
     * @return mixed|\Redis
     */
    public function unLock($key = '')
    {
        $key = $this->prefix . $key;
        $script = "
            if redis.call('get', KEYS[1]) == ARGV[1] then
                return redis.call('del', KEYS[1])
            else
                return 0
            end
        ";

        return $this->connect->eval($script, [$key, 1], 1);
    }
    
    /**
     * 返回当前链接
     * @return \Redis
     */
    public function getRedis()
    {
        return $this->connect;
    }

}