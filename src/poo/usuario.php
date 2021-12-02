<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\NullLogger;

require "IUsuario.php";

class Usuario implements IUsuario
{
    public $id;
    public $foto;
    public $clave;
    public $nombre;
    public $correo;
    public $perfil;
    public $apellido;

    public function AltaUsuario(Request $request, Response $response, array $args): Response 
	{
        $arrayDeParametros = $request->getParsedBody();

		$json = json_decode($arrayDeParametros['usuario']);     

        $path = "";
        
        $miUsuario = new Usuario();
        $miUsuario -> clave    = $json -> clave;
		$miUsuario -> correo   = $json -> correo;	
        $miUsuario -> nombre   = $json -> nombre;
        $miUsuario -> perfil   = $json -> perfil;
		$miUsuario -> apellido = $json -> apellido;	

        $retorno = new stdClass();
        $retorno -> exito   = false;
        $retorno -> status  = 418;
        $retorno -> mensaje = "Error al ingresar el usuario en la base de datos...";

		$archivos = $request->getUploadedFiles();
        $destino = __DIR__ . "/../fotos/";

        $nombreAnterior = $archivos['foto']->getClientFilename();
        $extension = explode(".", $nombreAnterior);
        $extension = array_reverse($extension);
        $path = $miUsuario -> correo . "_";

        $id_agregado = $miUsuario->InsertarUsuario($miUsuario, $path, $extension);

		$archivos['foto'] -> moveTo($destino . $miUsuario -> correo . "_" . $id_agregado  . "." . $extension[0]);

        if($id_agregado > 0)
        {
            $retorno -> exito   = true;
            $retorno -> status  = 200;
            $retorno -> mensaje = "Exito al ingresar el usuario en la base de datos!";
        }
		
        $retorno = json_encode($retorno);
        $response -> getBody() -> write($retorno);
      	return $response;
    }

    public function InsertarUsuario($miUsuario, $path, $extension)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT into usuarios (id, foto, clave, nombre, correo, perfil, apellido)
                                                                values(:id, :foto, :clave, :nombre, :correo, :perfil, :apellido)");

        $todosLosUsuarios = Usuario::TraerTodoLosUsuarios();
        $lastId = $todosLosUsuarios[count($todosLosUsuarios)-1]->id + 1;

        $consulta -> bindValue(':id'      , 0, PDO::PARAM_INT);
        $consulta -> bindValue(':foto'    , "xd", PDO::PARAM_STR);
        $consulta -> bindValue(':clave'   , $miUsuario -> clave   , PDO::PARAM_STR);
		$consulta -> bindValue(':perfil'  , $miUsuario -> perfil  , PDO::PARAM_STR);
        $consulta -> bindValue(':correo'  , $miUsuario -> correo  , PDO::PARAM_STR);
        $consulta -> bindValue(':nombre'  , $miUsuario -> nombre  , PDO::PARAM_INT);
        $consulta -> bindValue(':apellido', $miUsuario -> apellido, PDO::PARAM_STR);

		$consulta -> execute();	

		$retorno = $objetoAccesoDato -> RetornarUltimoIdInsertado();

        if($retorno)
        {
            $todosLosUsuarios = Usuario::TraerTodoLosUsuarios();

            if($todosLosUsuarios != false)
            {
                $lastId = $todosLosUsuarios[count($todosLosUsuarios)-1]->id;
            }

            $pathxd =  $path.$lastId.".".$extension[0];

            $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE usuarios SET foto='".$pathxd."' WHERE id=".$lastId."");

            $consulta -> execute();
        }

        return $retorno;
	}

    public function ListarUsuarios(Request $request, Response $response, array $args): Response 
	{
		$todosLosUsuarios = Usuario::TraerTodoLosUsuarios();

        $retorno = new stdClass();
        $retorno -> exito   = false;
        $retorno -> status  = 424;
        $retorno -> mensaje = "Error no se encontraron usuarios en la base de datos...";
        $retorno -> dato    = "No hay datos...";

        if($todosLosUsuarios != false)
        {
            $retorno -> exito   = true;
            $retorno -> status  = 200;
            $retorno -> mensaje = "Exito al traer los usuarios de a base de datos!";
            $retorno -> dato    = json_encode($todosLosUsuarios);
        }
  
		$newResponse = $response->withStatus(200, "OK");
        $newResponse->getBody()->write(json_encode($retorno));
		return $newResponse->withHeader('Content-Type', 'application/json');	
	}

    public static function TraerTodoLosUsuarios()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 

		$consulta = $objetoAccesoDato -> RetornarConsulta("select id, foto as foto, clave as clave, nombre as nombre, correo as correo, perfil as perfil, apellido as apellido from usuarios");
		$consulta -> execute();			

		return $consulta -> fetchAll(PDO::FETCH_CLASS, "Usuario");		
	}

    public function LoginUsuarioJson(Request $request, Response $response, array $args): Response 
	{
        $arrayDeParametros = $request->getParsedBody();
		$json = json_decode($arrayDeParametros['user']);    

        $retorno = new stdClass();
        $retorno -> status = 403;
        $retorno -> exito   = false;
		$elUsuario = Usuario::TraerUnUsuarioCorreoClave($json -> correo, $json -> clave);

        if($elUsuario)
        {
            $retorno -> status = 200;  
            $retorno -> exito  = true;
            $retorno -> mensaje    = json_encode($elUsuario);
		    $newResponse = $response->withStatus(200, "OK");
        }else{
            $retorno -> mensaje    = null;
		    $newResponse = $response->withStatus(403, "ERROR");
        }

		$newResponse->getBody()->write(json_encode($retorno));	

		return $newResponse->withHeader('Content-Type', 'application/json');
	}

    public static function TraerUnUsuarioCorreo($correo) 
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 

		$consulta = $objetoAccesoDato -> RetornarConsulta("select * from usuarios where correo = :correo");
        $consulta -> bindValue(':correo'  , $correo  , PDO::PARAM_STR);
		$consulta->execute();

		$usuarioBuscado = $consulta->fetchObject('Usuario');
		return $usuarioBuscado;		
	}

    public static function TraerUnUsuarioCorreoClave($correo, $clave) 
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 

		$consulta = $objetoAccesoDato -> RetornarConsulta("select * from usuarios where correo = :correo AND clave = :clave");
        $consulta -> bindValue(':correo'  , $correo  , PDO::PARAM_STR);
        $consulta -> bindValue(':clave'   , $clave   , PDO::PARAM_STR);
		$consulta->execute();

		$usuarioBuscado = $consulta->fetchObject('Usuario');
		return $usuarioBuscado;		
	}

    //Parte 3
    public function ModificarUsuario(Request $request, Response $response, array $args) : Response
	{
        $arrayDeParametros = $request->getParsedBody();
        $decoded = json_decode($arrayDeParametros["usuario"]);

        $retorno = new stdClass();
        $retorno -> status = 418;
        $retorno -> mensaje = "Error al modificar";
        $retorno -> exito   = false;

        $archivos = $request->getUploadedFiles();
        $nombreAnterior = $archivos['foto']->getClientFilename();
        $extension = explode(".", $nombreAnterior);
        $extension = array_reverse($extension);
        $destino = __DIR__ . "./fotos/". $decoded -> correo . "_" . $decoded -> id_decoded."_modificacion" . ".".$extension[0];

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE usuarios SET correo=:correo,clave=:clave,nombre=:nombre,apellido=:apellido,perfil=:perfil,foto=:foto WHERE id=:id");
        $consulta -> bindValue(':id'      , $decoded ->id_usuario, PDO::PARAM_INT);
        $consulta -> bindValue(':correo'      , $decoded ->correo, PDO::PARAM_STR);
        $consulta -> bindValue(':clave'   , $decoded -> clave   , PDO::PARAM_STR);
        $consulta -> bindValue(':nombre'   , $decoded -> nombre   , PDO::PARAM_STR);
		$consulta -> bindValue(':apellido'  , $decoded -> apellido  , PDO::PARAM_STR);
        $consulta -> bindValue(':perfil'  , $decoded -> perfil  , PDO::PARAM_STR);
        $consulta -> bindValue(':foto'  , $destino  , PDO::PARAM_STR);

        $consulta -> execute();
        if($consulta->rowCount()>0)
        {
            $archivos['foto'] -> moveTo(__DIR__ . "/../fotos/". $decoded -> correo . "_" . $decoded -> id_decoded."_modificacion" . ".".$extension[0]);

            $newResponse = $response->withStatus(200, "OK");
            $retorno -> mensaje = "Exito al modificar";
            $retorno -> exito   = true;
            $retorno -> status = 200;

        }else{
            $newResponse = $response->withStatus(418, "ERROR");
        }
       
		$newResponse->getBody()->write(json_encode($retorno));	

		return $newResponse->withHeader('Content-Type', 'application/json');
	}
    public function BorrarUsuario(Request $request, Response $response, array $args) : Response
    {
        $arrayDeParametros = $request->getParsedBody();

        $decoded = json_decode($arrayDeParametros["usuario"]);

        $retorno = new stdClass();
        $retorno -> status = 418;
        $retorno -> mensaje = "Error al borrar";
        $retorno -> exito   = false;

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM usuarios WHERE id=:id");
        $consulta -> bindValue(':id'      , $decoded->id_usuario, PDO::PARAM_INT);

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