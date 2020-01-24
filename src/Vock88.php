<?php
/**
 * @package     IdnPlay\Vock88
 * @author      singkek
 * @copyright   Copyright(c) 2019
 * @version     1
 * @created     2020-01-24
 * @updated     2020-01-24
 **/

namespace IdnPlay\Vock88;

use Carbon\Carbon;
use IdnPlay\Vock88\Repository\Vock88Repository;

class Vock88
{
    /**
     * config variable
     *
     * @var object
     */
    protected $config;

    /**
     * header variable
     *
     * @var array
     */
    protected $header;

    /**
     * locale variable
     *
     * @var mixed
     */
    protected $locale;

    /**
     * load repository
     *
     * @var Vock88Repository
     */
    protected $repository;

    /**
     * @var object
     */
    protected $raw;

    /**
     * @var bool
     */
    protected $access_token = false;

    /**
     * Vock88 constructor.
     * @param array $config
     */
    public function __construct($config = array())
    {
        /*
         * define construct variable
         */
        $this->config = (object)$config;
        $this->header = $this->header();
        $this->locale = $this->locale();
        $this->repository = new Vock88Repository($this->config);

    }

    /**
     * set default header
     *
     * @return array
     */
    protected function header()
    {
        return [
            'Content-Type: application/json'
        ];
    }

    /**
     * get locale from config laravel
     *
     * @return mixed
     */
    protected function locale()
    {
        return app('config')->get('app.timezone');
    }

    /**
     * convert local date to another locale
     *
     * @param $date
     * @param string $local_tz
     * @return object
     */
    protected function toLocale($date, $local_tz = 'UTC')
    {
        $datetime = date('Y-m-d H:i:s',strtotime($date));
        $datetime_locale = Carbon::createFromFormat('Y-m-d H:i:s', $datetime, $this->locale);
        $datetime_locale->setTimezone($local_tz);

        $datetime_utc = Carbon::createFromFormat('Y-m-d H:i:s', $datetime, $this->locale);
        $datetime_utc->setTimezone('UTC');

        return (object) ['utc_datetime' => strtotime($datetime_utc), 'datetime' => strtotime($datetime_locale)];
    }

    /**
     * convert second to time by date now
     *
     * @param $seconds
     * @return object
     * @throws \Exception
     */
    protected function secondsToTime($seconds)
    {
        $dtF = new Carbon('@0');
        $dtT = new Carbon("@$seconds");
        $format = $dtF->diff($dtT)->format('%y year %m month %a days %h hours %i minutes %s seconds');

        $original_datetime = date('Y-m-d H:i:s', strtotime($format));

        $local_datetime = $this->toLocale($original_datetime)->datetime;

        return (object) [
            'format' => $format,
            'original_datetime' => $original_datetime,
            'local_datetime' => date('Y-m-d H:i:s', $local_datetime)
        ];
    }

    /**
     * run curl to server
     *
     * @param string $path
     * @param string $data
     * @param string $method
     * @return $this
     */
    protected function run($path = '', $data = '', $method = 'POST')
    {
        $data = is_array($data) ? json_encode($data) : $data;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config->host.$path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = json_decode(curl_exec($ch));
        $info = curl_getinfo($ch);
        $raw_data = ['result' => $result, 'info' => $info, 'header' => $this->header, 'post' => $data];
        $this->raw = (object)$raw_data;

        return $this;
    }

    /**
     * get token oauth
     *
     * @param bool $request
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getToken($request = FALSE)
    {
        /*
         * get saved token from storage
         */
        $token = $this->repository->getToken();

        if(!isset($token->access_token) || $request == TRUE)
        {
            $getToken = $this->run('/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config->client_id,
                'client_secret' => $this->config->client_secret
            ]);

            if(isset($this->raw->result->access_token))
            {
                $this->repository->setToken([
                    'access_token' => $this->raw->result->access_token,
                    'created' => date('Y-m-d H:i:s'),
                    'expired' => $this->secondsToTime($this->raw->result->expires_in)->local_datetime
                ]);

                $this->access_token = $this->raw->result->access_token;
            }
        }
        else
        {
            $this->access_token = $token->access_token;
        }

        return $this;
    }

    /**
     * access api
     *
     * @param string $path
     * @param string $data
     * @param string $method
     * @param bool $request
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function fetch($path = '', $data = '', $method = 'POST', $request = FALSE)
    {
        $this->getToken($request);

        if(!$this->access_token)
        {
            return $this;
        }

        $this->header = [$this->header[0]];
        array_push($this->header, 'Authorization: Bearer '.$this->access_token);

        $fetch = $this->run('/oauth'.$path, $data, $method);

        if($this->raw->result->success === FALSE)
        {
            if($this->raw->result->response_code == 401)
            {
                /*
                 * Request Ulang
                 */
                return $this->fetch($path, $data, $method, TRUE);
            }
        }

        return $this;
    }

    /**
     * get response data
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->raw->result;
    }

    /**
     * debug raw response data
     */
    public function dd()
    {
        dd($this->raw);
    }
}
