<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as ResponseMW;
require "IAuto.php";

class Auto implements IAuto
{
    public $id;
    public $color;
    public $marca;
    public $precio;
    public $modelo;

    public function AltaAuto(Request $request, Response $response, array $args): Response 
	{
        $arrayDeParametros = $request->getParsedBody();
		$json = json_decode($arrayDeParametros['auto']);     
        
        $miAuto = new Auto();
        $miAuto -> marca    = $json -> marca;	
        $miAuto -> color    = $json -> color;
        $miAuto -> precio   = $json -> precio;
        $miAuto -> modelo   = $json -> modelo;	

        $retorno = new stdClass();
        $retorno -> éxito   = false;
        $retorno -> status  = 418;
        $retorno -> mensaje = "Error al ingresar el auto en la base de datos...";

        $id_agregado = $miAuto -> InsertarAuto($miAuto);

        if($id_agregado > 0)
        {
            $retorno -> éxito   = true;
            $retorno -> status  = 200;
            $retorno -> mensaje = "Exito al ingresar el auto en la base de datos!";
        }
		
        $retorno = json_encode($retorno);

        $response -> getBody() -> write($retorno);

      	return $response;
    }

    public function InsertarAuto($miAuto)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("INSERT into autos (id, color, marca, precio, modelo)
                                                                values(:id, :color, :marca, :precio, :modelo)");

        $consulta -> bindValue(':id'      , 0, PDO::PARAM_INT);
        $consulta -> bindValue(':color'   , $miAuto -> color  , PDO::PARAM_STR);
        $consulta -> bindValue(':marca'   , $miAuto -> marca   , PDO::PARAM_STR);
		$consulta -> bindValue(':precio'  , $miAuto -> precio  , PDO::PARAM_STR);
        $consulta -> bindValue(':modelo'  , $miAuto -> modelo  , PDO::PARAM_STR);
        
		$consulta -> execute();	

		return $objetoAccesoDato -> RetornarUltimoIdInsertado();
	}
    
    public function ListarAutos(Request $request, Response $response, array $args): Response 
	{
		$todosLosAutos = Auto::TraerTodosLosAutos();

        $retorno = new stdClass();
        $retorno -> éxito   = false;
        $retorno -> status  = 424;
        $retorno -> mensaje = "Error no se encontraron autos en la base de datos...";
        $retorno -> dato    = "No hay datos...";

        if(count($todosLosAutos) > 0)
        {
            $retorno -> éxito   = true;
            $retorno -> status  = 200;
            $retorno -> mensaje = "Exito al traer los autos de a base de datos!";
            $retorno -> dato    = json_encode($todosLosAutos);
        }
  
		$newResponse = $response->withStatus(200, "OK");
        $newResponse->getBody()->write(json_encode($retorno));
		return $newResponse->withHeader('Content-Type', 'application/json');	
	}

    public static function TraerTodosLosAutos()
	{
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 

        $consulta = $objetoAccesoDato -> RetornarConsulta("select id, marca as marca, color as color, precio as precio, modelo as modelo from autos");

        $consulta -> execute();

        return $consulta -> fetchAll(PDO::FETCH_CLASS, "Auto");
	}
    public function ModificarAuto(Request $request, Response $response, array $args) : Response
	{
        $decoded = json_decode($args["auto"]);
        $retorno = new stdClass();
        $retorno -> status = 418;
        $retorno -> mensaje = "Error al modificar";
        $retorno -> exito   = false;

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE autos SET color=:color,marca=:marca,precio=:precio,modelo=:modelo WHERE id=:id");

        $consulta -> bindValue(':id'      , $decoded -> id_auto, PDO::PARAM_INT);
        $consulta -> bindValue(':color'   , $decoded -> color   , PDO::PARAM_STR);
        $consulta -> bindValue(':marca'   , $decoded -> marca   , PDO::PARAM_STR);
		$consulta -> bindValue(':precio'  , $decoded -> precio  , PDO::PARAM_STR);
        $consulta -> bindValue(':modelo'  , $decoded -> modelo  , PDO::PARAM_STR);

        $consulta -> execute();
        if($consulta->rowCount()>0)
        {
            $newResponse = $response->withStatus(200, "OK");
            $retorno -> status = 200;
            $retorno -> mensaje = "Exito al modificar";
            $retorno -> exito   = true;
        }else{
            $newResponse = $response->withStatus(418, "ERROR");
        }
       
		$newResponse->getBody()->write(json_encode($retorno));	

		return $newResponse->withHeader('Content-Type', 'application/json');
	}
    public function BorrarAuto(Request $request, Response $response, array $args) : Response
    {
        $id_auto =$args["id_auto"];
        $retorno = new stdClass();
        $retorno -> status = 418;
        $retorno -> mensaje = "Error al borrar";
        $retorno -> exito   = false;

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM autos WHERE id=:id");
        $consulta -> bindValue(':id'      , $id_auto, PDO::PARAM_INT);
        $consulta -> execute();
        if($consulta->rowCount()>0)
        {
            $newResponse = $response->withStatus(200, "OK");
            $retorno -> mensaje = "Exito al borrar";
            $retorno -> exito   = true;
            $retorno -> status = 200;
        }else{
            $newResponse = $response->withStatus(418, "ERROR");
        }
       
		$newResponse->getBody()->write(json_encode($retorno));	

		return $newResponse->withHeader('Content-Type', 'application/json');
    }
}