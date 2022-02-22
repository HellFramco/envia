<?php

namespace EnviaColvanes;
include_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// import webservice class

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;


class Client
{
    const SANDBOX_URL_LIQUIDATION = 'http://200.69.100.66/ServicioLiquidacionRESTpruebas/Service1.svc/Liquidacion/';
    const URL_LIQUIDATION = 'http://200.69.100.66/ServicioLiquidacionREST/Service1.svc/Liquidacion/';

    const SANDBOX_URL_GUIDES = 'http://200.69.100.66/ServicioLiquidacionRESTpruebas/Service1.svc/Generacion/';
    const URL_GUIDES = 'http://200.69.100.66/ServicioLiquidacionREST/Service1.svc/Generacion/';

    const SANDBOX_URL_GET_GUIDES = 'http://200.69.100.66/ServicioRESTConsultaEstadospruebas/Service1Consulta.svc/ConsultaGuia/';
    const URL_GET_GUIDES = 'http://200.69.100.66/ServicioRESTConsultaEstados/Service1Consulta.svc/ConsultaGuia/';

    private $user;
    private $password;
    private $codeAccount;
    protected static $_sandbox = false;

    public function __construct($user, $password, $codeAccount)
    {
        $this->user = $user;
        $this->password = $password;
        $this->codeAccount = $codeAccount;
    }

    public function sandboxMode($status = false)
    {
        self::$_sandbox = $status;
    }

    protected static function getUrlLiquidation()
    {
        if(self::$_sandbox)
            return self::SANDBOX_URL_LIQUIDATION;
        return self::URL_LIQUIDATION;
    }

    protected static function getUrlGuides()
    {
        if(self::$_sandbox)
            return self::SANDBOX_URL_GUIDES;
        return self::URL_GUIDES;
    }

    public static function getUrlConsultGuides()
    {
        if(self::$_sandbox)
            return self::SANDBOX_URL_GET_GUIDES;
        return self::URL_GET_GUIDES;
    }

    public function client()
    {
        return new GuzzleClient();
    }

    public function liquidation(array $params)
    {
        try{
            $params = array_merge($params,  [
                'cod_cuenta' => $this->codeAccount,
            ]);
            $response = $this->client()->post(self::getUrlLiquidation(), [
                "headers" => [
                    "Authorization" => ["Basic " . $this->encodeCredentials()],
                    "Accept" => "application/json"
                ],
                "json" => $params
            ]);
            return self::responseJson($response);
        }catch(RequestException $exception){
            $response = $exception->getResponse();
            if (isset($response))
                $response = self::responseJson($response);
            $exceptionMessage = $response->respuesta ?? $exception->getMessage();
            throw new \Exception($exceptionMessage);
        }
    }

    public function generateGuide(array $params)
    {
        try{
            $params = array_merge($params,  [
                'cod_cuenta' => $this->codeAccount,
            ]);
            $response = $this->client()->request('POST', self::getUrlGuides(), [
                "headers" => [
                    "Authorization" => "Basic " . "{$this->encodeCredentials()}",
                    "Accept" => "application/json"
                ],
                "json" => $params
            ]);
            return self::responseJson($response);
        }catch(RequestException $exception){
            $response = $exception->getResponse();
            if (isset($response))
                $response = self::responseJson($response);
            $exceptionMessage = $response->respuesta ?? $exception->getMessage();
            throw new \Exception($exceptionMessage);
        }
    }

    public function getGuide($guide)
    {
        try{
            $response = $this->client()->request('GET', self::getUrlConsultGuides() . $guide);
            return self::responseJson($response);
        }catch(RequestException $exception){
            $response = $exception->getResponse();
            if (isset($response))
                $response = self::responseJson($response);
            $exceptionMessage = $response->respuesta ?? $exception->getMessage();
            throw new \Exception($exceptionMessage);
        }
    }

    public function encodeCredentials()
    {
        $access = "$this->user:$this->password";
        return base64_encode($access);
    }

    public static function responseJson($response)
    {
        return \GuzzleHttp\json_decode(
            $response->getBody()->getContents()
        );
    }
}

$user = 'EMPCAR01'; // your user of Envia Colvanes
$password = 'EMPCAR1'; // your password of Envia Colvanes
$codAccount = '30'; // code Account of Envia Colvanes

try{

    $enviaColvanes = new Client($user, $password, $codAccount);
    $enviaColvanes->sandboxMode(true);

    $cart_prods = [];
    $cart_prods[] = array(
    'cantidad' => 2,
    'largo' => 5.0,
    'ancho' => 5.0,
    'alto' => 10.0,
    'peso' => 10,
    'declarado' => 10000
    ); 


    $params = array (
                'ciudad_origen' => '05001', //Code city DANE, Medellín 
                'ciudad_destino' => '11001', //Code city DANE, Bogotá
                'cod_formapago' => 7,
                'cod_servicio' => 3,
                'info_cubicacion' => $cart_prods
            );

    try{
        $data = $enviaColvanes->liquidation($params);
        print_r($data);
    }
    catch (\Exception $exception){
        echo $exception->getMessage();
    }

}
catch (\Exception $exception){
    echo $exception->getMessage();
}

?>