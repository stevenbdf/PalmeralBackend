<?php
error_reporting(E_ERROR | E_PARSE);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ReallySimpleJWT\Token;

require '../../models/users.php';
$app = new \Slim\App();

$app->get('/', function (Request $req,  Response $res) {
    $users = new Palmeral\User();
    $user = $users->all();
    return $res->withJson($user);
});

$app->post('/create', function (Request $req,  Response $res) {
    $users = new Palmeral\User();
    $validator = new Palmeral\Validator;

    $body = $req->getParsedBody();

    if ($validator->validateEmail($body['email'])) {
        $users->email = $body['email'];
        if ($validator->validatePassword($body['password'])) {
            $users->password = password_hash($body['password'], PASSWORD_DEFAULT);
            if ($users->save()) {
                return $res
                    ->withStatus(200)
                    ->withJson(['message' => 'Usuario creado correctamente']);
            }
            return $res->withStatus(500)->withJson(['message' => 'Error al crear usuario']);
        }
        return $res->withStatus(400)->withJson(['message' => 'Contraseña incorrecta']);
    }
    return $res->withStatus(400)->withJson(['message' => 'Correo incorrecto']);
});

$app->post('/update', function (Request $req,  Response $res) {
    $users = new Palmeral\User();
    $validator = new Palmeral\Validator;

    $body = $req->getParsedBody();

    if ($user = $users->find($body['id_user'])) {
        if ($validator->validateEmail($body['email'])) {
            $user->email = $body['email'];
            if ($user->save()) {
                return $res
                    ->withStatus(200)
                    ->withJson(['message' => 'Usuario modificado correctamente']);
            }
            return $res->withStatus(500)->withJson(['message' => 'Error al modificar usuario']);
        }
        return $res->withStatus(400)->withJson(['message' => 'Correo incorrecto']);
    }
    return $res->withStatus(400)->withJson(['message' => 'Usuario no encontrado']);
});

$app->post('/password', function (Request $req,  Response $res) {
    $users = new Palmeral\User();
    $validator = new Palmeral\Validator;

    $body = $req->getParsedBody();

    if ($user = $users->find($body['id_user'])) {
        if (password_verify($body['password'], $user->password)) {
            if ($validator->validatePassword($body['new_password'])) {
                $user->password = password_hash($body['new_password'], PASSWORD_DEFAULT);
                if ($user->save()) {
                    return $res
                        ->withStatus(200)
                        ->withJson(['message' => 'Contraseña modificado correctamente']);
                }
                return $res->withStatus(500)->withJson(['message' => 'Error al modificar contraseña']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Contraseña nueva invalida, 
                debe contener mayusculas, minusculas, numeros y caracteres especiales']);
        }
        return $res->withStatus(400)->withJson(['message' => 'Contraseña incorrecta']);
    }
    return $res->withStatus(400)->withJson(['message' => 'Usuario no encontrado']);
});

$app->delete('/delete', function (Request $req,  Response $res) {
    $users = new Palmeral\User();

    $body = $req->getParsedBody();

    if ($user = $users->find($body['id_user'])) {
        if ($user->delete()) {
            return $res
                ->withStatus(200)
                ->withJson(['message' => 'Usuario eliminado correctamente']);
        }
        return $res->withStatus(400)->withJson(['message' => 'Error al eliminar usuario']);
    }
    return $res->withStatus(400)->withJson(['message' => 'Usuario no encontrado']);
});

$app->post('/login', function (Request $req,  Response $res) {
    $users = new Palmeral\User();

    $body = $req->getParsedBody();

    if ($user = $users->where('email', $body['email'])->first()) {
        if (password_verify($body['password'], $user->password)) {
            $payload = array(
                "iat" => time(),
                "id_user" => $user->id_user,
                "exp" => time() + 1.296e+6
            );
            $token = Token::customPayload($payload, $_ENV['SECRET_KEY']);
            return $res->withStatus(200)->withJson([
                'token' => $token, 'id_user' => $user->id_user,
                'message' => 'Bienvenido'
            ]);
        }
        return $res->withStatus(400)->withJson(['message' => 'Contraseña incorrecta']);
    }
    return $res->withStatus(400)->withJson(['message' => 'Usuario no encontrado']);
});

$app->get('/find', function (Request $req,  Response $res) {
    $users = new Palmeral\User();
    $validator = new Palmeral\Validator();

    $body = $req->getParsedBody();

    if ($token = $validator->getBearerToken($req->getHeader('HTTP_AUTHORIZATION')[0])) {
        if (Token::validate($token, $_ENV['SECRET_KEY'])) {
            if ($user = $users->find($body['id_user'])) {
                return $res->withStatus(200)->withJson(['message' => 'Usuario encontrado', 'data' => $user]);
            }
            return $res->withStatus(400)->withJson(['message' => 'Usuario no encontrado']);
        }
    }
    return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
});

$app->run();
