<?php
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Slim\Psr7\Response as ResponseMW;


    class MW{
        public static function EstanVacios(Request $request, RequestHandler $handler):ResponseMW
        {
            $retorno = new stdClass();
            $retorno -> status  = 409;
            $retorno -> mensaje = "correo y la clave vacios..";

            $datosUsuario = $request->getParsedBody();

            if(isset($datosUsuario['usuario']))
            {
                $json = json_decode($datosUsuario['usuario']);
            }
            else
            {
                $json = json_decode($datosUsuario['user']);
            }
            
            $response = new ResponseMW();

            if($json -> correo == "" && $json -> clave == "")
            {
                $response -> getBody() -> write(json_encode($retorno));
                $response -> withStatus(409);
            }
            else if($json -> correo == "")
            {
                $retorno -> mensaje = "El correo vacio..";
                $response -> getBody() -> write(json_encode($retorno));
                $response -> withStatus(409);
            }
            else if($json -> clave == "")
            {
                $retorno -> mensaje = "La clave vacia..";
                $response -> getBody() -> write(json_encode($retorno));
                $response -> withStatus(409);
            }
            else
            {
                $response = $handler -> handle($request);
            }

            return $response;
        }

        public function EstanDB(Request $request, RequestHandler $handler):ResponseMW
        {
            $retorno = new stdClass();
            $retorno -> status  = 403;
            $retorno -> mensaje = "Correo y Clave NO estan en la BD..";

            $datosUsuario = $request -> getParsedBody();

            $json = json_decode($datosUsuario['user']);

            $response = new ResponseMW();
		
            if(Usuario::TraerUnUsuarioCorreoClave($json -> correo, $json -> clave) == false)
            {
                $response -> getBody() -> write(json_encode($retorno));
                $response -> withStatus(403);
            }
            else
            {
                $response = $handler -> handle($request);
            }
            
            return $response;
        }

        public static function NoEstanDB(Request $request, RequestHandler $handler):ResponseMW
        {
            $retorno = new stdClass();
            $retorno -> status  = 403;
            $retorno -> mensaje = "El correo ya fue ingresado en la BD..";

            $datosUsuario = $request->getParsedBody();
            $json = json_decode($datosUsuario['usuario']);
            
            $response = new ResponseMW();

            if(Usuario::TraerUnUsuarioCorreoClave($json -> correo, $json -> clave))
            {
                $response = $response -> withStatus(403);
        
                $response -> getBody() -> write(json_encode($retorno));
            }
            else
            {
                $response = $handler -> handle($request);
            }

            return $response;
        }
        public static function MostrarUsuarios(Request $request, RequestHandler $handler):ResponseMW
        {
            $datosUsuario = $request->getParsedBody();
            $json = json_decode($datosUsuario['usuario']);

            $response = new ResponseMW();
            $todosLosUsuarios = Usuario::TraerTodoLosUsuarios();
            if($request->getMethod()=="GET")
            {
                $tabla = "";
                $tabla.= '<table align="center">';
                $tabla.= "<tr>";
                $tabla.= "<th>ID</th>";
                $tabla.= "<th>Nombre</th>";
                $tabla.= "<th>Apellido</th>";
                $tabla.= "<th>Correo</th>";
                $tabla.= "<th>Perfil</th>";
                $tabla.= "<th>Foto</th>";
                $tabla.= "</tr>";
                foreach($todosLosUsuarios as $usuario)
                {
                    $tabla.= "<tr>";
                    $tabla.= "<td>".$usuario->id."</td>";
                    $tabla.= "<td>".$usuario->nombre."</td>";
                    $tabla.= "<td>".$usuario->apellido."</td>";
                    $tabla.= "<td>".$usuario->correo."</td>";
                    $tabla.= "<td>".$usuario->perfil."</td>";
                    $tabla .='<td style="margin: 0 auto; width: 130px"><img src="../src/fotos/'.$usuario->foto.'" width="50" height="50"></td>';
                    $tabla.= "</tr>";
                }
                $tabla.= '</table>';
                $response -> getBody() -> write($tabla);
            }else if($request->getMethod()=="POST"&&$json->perfil=="propietario")
            {
                header('content-type:application/pdf');
                $mpdf = new \Mpdf\Mpdf(['orientation' => 'P']);
                $tabla = "";
                ob_start();
                $tabla.= '<table align="center">';
                $tabla.= "<tr>";
                $tabla.= "<th>ID</th>";
                $tabla.= "<th>Nombre</th>";
                $tabla.= "<th>Apellido</th>";
                $tabla.= "<th>Correo</th>";
                $tabla.= "<th>Perfil</th>";
                $tabla.= "<th>Foto</th>";
                $tabla.= "</tr>";
                foreach($todosLosUsuarios as $usuario)
                {
                    $tabla.= "<tr>";
                    $tabla.= "<td>".$usuario->id."</td>";
                    $tabla.= "<td>".$usuario->nombre."</td>";
                    $tabla.= "<td>".$usuario->apellido."</td>";
                    $tabla.= "<td>".$usuario->correo."</td>";
                    $tabla.= "<td>".$usuario->perfil."</td>";
                    $tabla .= '<td style="margin: 0 auto; width: 130px"><img src="../src/fotos/'.$usuario->foto.'" width="50" height="50"></td>';
                    $tabla.= "</tr>";
                }
                $tabla.= '</table>';
                ob_end_clean();
                $response -> getBody() -> write($tabla);
                $mpdf->WriteHTML($tabla);
                $mpdf->Output();
            }
            return $response;
        }
        public static function MostrarAutos(Request $request, RequestHandler $handler):ResponseMW
        {
            $response = new ResponseMW();
            $todosLosAutos = Auto::TraerTodosLosAutos();
            $tabla = "";
            $tabla.= '<table align="center">';
            $tabla.= "<tr>";
            $tabla.= "<th>ID</th>";
            $tabla.= "<th>Color</th>";
            $tabla.= "<th>Marca</th>";
            $tabla.= "<th>Precio</th>";
            $tabla.= "<th>Modelo</th>";
            $tabla.= "</tr>";
            foreach($todosLosAutos as $auto)
            {
                $tabla.= "<tr>";
                $tabla.= "<td>".$auto->id."</td>";
                $tabla.= "<td>".$auto->color."</td>";
                $tabla.= "<td>".$auto->marca."</td>";
                $tabla.= "<td>".$auto->precio."</td>";
                $tabla.= "<td>".$auto->modelo."</td>";
                $tabla.= "</tr>";
            }
            $tabla.= '</table>';
            $response -> getBody() -> write($tabla);
            return $response;
        }
    }

?>