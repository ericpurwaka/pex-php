<?php namespace Favor\Pex;

//use Illuminate\Support\Facades\Config;
use Illuminate\Config\FileLoader;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;

use \Guzzle\Http\Client;
use \Guzzle\Http\Subscriber\History;

class PexConnection {

    private $auth;
    private $config;

    public function __construct($config)
    {
        $this->setConfiguration($config);
    }

    public function setConfiguration($config)
    {
        $basePath = str_finish(dirname(__FILE__), '/../../');
        $defaultConfigPath = $basePath . 'config';

        $defaultLoader = new FileLoader(new Filesystem, $defaultConfigPath);
        $this->config = new Repository($defaultLoader, 'production');

        if($_SERVER['APPLICATION_ENVIRONMENT'] == 'production') {
            $this->base_url = $this->config->get('pexconnection.BASE_PRODUCTION');
        } else {
            $this->base_url = $this->config->get('pexconnection.BASE_SANDBOX');
        }

        $this->auth = [
            'Authorization' => 'token ' . $config['token']
        ];
    }

    public function masterAccount()
    {
        $url = $this->config->get('pexconnection.urls.masteraccountdetails');

        return self::get($url);
    }

    public function findAccount($id)
    {
        $url = str_replace('{id}', $id, $this->config->get('pexconnection.urls.accountdetails'));

        $postData = array('id' => $id);

        return self::get($url, $postData);
    }

    public function fund($id, $amount)
    {
        if (!is_numeric($amount)) {
            throw new PexException('Bad Amount');
        }

        $url = str_replace('{id}', $id, $this->config->get('pexconnection.urls.accountfund'));

        $postData = array('Amount' => $amount);

        $resp = self::post($url, $postData);

        return $this->findAccount($resp['AccountId']);
    }

    public function defundAll()
    {
        $url = $this->config->get('pexconnection.urls.defundall');

        return self::post($url, null);
    }

    public function updateCardStatus($id, $status)
    {
        if (!in_array($status, Card::$updateableCardStatuses)) {
            throw new PexException('Bad Card Status');
        }

        $url =  str_replace('{id}', $id, $this->config->get('pexconnection.urls.cardupdatestatus'));

        $putData = array('Status' => $status);

        $response = self::put($url, $putData);

        return $response->getStatusCode() == 200;
    }

    public function activateCard($id)
    {
        $url =  str_replace('{id}', $id, $this->config->get('pexconnection.urls.cardactivate'));

        $response = self::post($url, null);

        return $response->getStatusCode() == 200;
    }

    public function post($url, $data)
    {
        $client = new Client();
        $client->setDefaultOption('headers',$this->auth);

        $request = $client->post($this->base_url . $url);

        $request->setBody(json_encode($data), 'application/json');

        $response = $request->send();

        return $response->json();
    }

    public function put($url, $data)
    {
        $client = new Client();
        $client->setDefaultOption('headers',$this->auth);

        $request = $client->put($this->base_url . $url);

        $request->setBody(json_encode($data), 'application/json');

        $response = $request->send();

        return $response;
    }

    public function get($url)
    {
        $client = new Client();
        $client->setDefaultOption('headers',$this->auth);

        $request = $client->get($this->base_url . $url);

        $response = $request->send();

        return $response->json();
    }
}