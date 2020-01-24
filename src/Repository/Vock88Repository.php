<?php
/**
 * @package     IdnPlay\Vock88\Repository - Vock88Repository
 * @author      singkek
 * @copyright   Copyright(c) 2019
 * @version     1
 * @created     2020-01-24
 * @updated     2020-01-24
 **/

namespace IdnPlay\Vock88\Repository;

use IdnPlay\Vock88\Vock88Exceptions;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class Vock88Repository extends Repository
{
    /**
     * config variable
     *
     * @var object
     */
    protected $config;

    /**
     * Vock88Repository constructor.
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = (object)$config;
    }

    /**
     * get token from storage
     *
     * @return bool|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getToken()
    {
        try {
            if (isset($this->config->storage))
            {
                switch ($this->config->storage){
                    case 'file':
                        return $this->getTokenFile();
                        break;
                    case 'redis':
                        return $this->getTokenRedis();
                        break;
                    case 'db':
                        return $this->getTokenDb();
                        break;
                    default:
                        throw new Vock88Exceptions("Invalid Config! storage config not found");
                }
            }
            else
            {
                throw new Vock88Exceptions("Invalid Config! storage config not found");
            }
        }catch (Vock88Exceptions $exception)
        {
            return false;
        }
    }

    /**
     * save token to storage
     *
     * @param $param
     * @return bool|void
     */
    public function setToken($param)
    {
        try {
            if (isset($this->config->storage))
            {
                switch ($this->config->storage){
                    case 'file':
                        return $this->setTokenFile($param);
                        break;
                    case 'redis':
                        return $this->setTokenRedis($param);
                        break;
                    case 'db':
                        return $this->setTokenDb($param);
                        break;
                    default:
                        throw new Vock88Exceptions("Invalid Config! storage config not found");
                }
            }
            else
            {
                throw new Vock88Exceptions("Invalid Config! storage config not found");
            }
        }catch (Vock88Exceptions $exception)
        {
            return false;
        }
    }

    /**
     * get token from storage file
     *
     * @return bool|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getTokenFile()
    {
        $storage = Storage::disk('local');

        if ($storage->exists($this->config->storage_key.'.json')){
            $content = $storage->get($this->config->storage_key.'.json');

            if ($this->is_json($content))
            {
                $content = json_decode($content);

                return isset($content->access_token) && isset($content->expired) ? $content : false;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * save token to storage file
     *
     * @param array $param
     */
    protected function setTokenFile($param = array())
    {
        $storage = Storage::disk('local');
        $storage->put($this->config->storage_key.'.json', json_encode($param));
    }

    /**
     * get token from storage redis
     *
     * @return bool|mixed
     */
    protected function getTokenRedis()
    {
        if (Redis::exists($this->config->storage_key)){
            $content = Redis::get($this->config->storage_key);

            if ($this->is_json($content))
            {
                $content = json_decode($content);

                return isset($content->access_token) && isset($content->expired) ? $content : false;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * save token to storage redis
     *
     * @param array $param
     */
    protected function setTokenRedis($param = array())
    {
        Redis::set($this->config->storage_key, json_encode($param));
    }

    /**
     * get token from storage DB
     *
     * @return bool|mixed
     */
    protected function getTokenDb()
    {
        $get_data = $this->table($this->config->storage_key)->get();

        if ($get_data->isNotEmpty()){
            return $get_data->first();
        }
        else
        {
            return false;
        }
    }

    /**
     * save token from storage DB
     *
     * @param $param
     */
    protected function setTokenDb($param)
    {
        if($this->has_table($this->config->storage_key))
        {
            $this->table($this->config->storage_key)->delete();
            $this->table($this->config->storage_key)->insert($param);
        }
    }

    /**
     * check sting is json or not
     *
     * @param $string
     * @return bool
     */
    protected function is_json($string)
    {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }

}
