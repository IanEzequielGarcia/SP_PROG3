<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use \Slim\Routing\RouteCollectorProxy;
    use Slim\Factory\AppFactory;

    require __DIR__ . '../../../vendor/autoload.php';

    $app = AppFactory::create();

    require "../src/poo/AccesoDatos.php";
    require "../src/poo/auto.php";
    require "../src/poo/usuario.php";
    require "../src/poo/MW.php";
    
    $app->post('/usuarios', \Usuario::class . ':AltaUsuario')->add(\MW::class.":NoEstanDB")->add(\MW::class.":EstanVacios");
    $app->get('/', Usuario::class . ':ListarUsuarios');

    $app->post('/', \Auto::class . ':AltaAuto');
    $app->get('/autos', Auto::class . ':ListarAutos');

    $app->post('/login', Usuario::class . ':LoginUsuarioJson')->add(\MW::class.":EstanDB")->add(\MW::class.":EstanVacios");

    $app->group('/cars', function (\Slim\Routing\RouteCollectorProxy $cars)
    {
        $cars->delete('/{id_auto}', \Auto::class . ':BorrarAuto');
        $cars->put('/{auto}', \Auto::class . ':ModificarAuto');
    });
    $app->group('/users', function (\Slim\Routing\RouteCollectorProxy $usuarios)
    {
        $usuarios->post('/delete', \Usuario::class . ':BorrarUsuario');
        $usuarios->post('/edit', \Usuario::class . ':ModificarUsuario');
    });
    $app->group('/tablas', function (\Slim\Routing\RouteCollectorProxy $tablas)
    {
        $tablas->get('/usuarios', Usuario::class . ':ListarUsuarios')->add(\MW::class.":MostrarUsuarios");
        $tablas->post('/usuarios', Usuario::class . ':ListarUsuarios')->add(\MW::class.":MostrarUsuarios");
        $tablas->get('/autos', Auto::class . ':ListarAutos')->add(\MW::class.":MostrarAutos");
    });
    $app->run();

?>