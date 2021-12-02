<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface  IAuto
{
    function AltaAuto(Request $request, Response $response, array $args) : Response;
    function ListarAutos(Request $request, Response $response, array $args) : Response;
    function ModificarAuto(Request $request, Response $response, array $args) : Response;
    function BorrarAuto(Request $request, Response $response, array $args) : Response;
    /*
    function TraerUno(Request $request, Response $response, array $args) : Response;
    function AgregarUno(Request $request, Response $response, array $args) : Response;
    */
}

?>