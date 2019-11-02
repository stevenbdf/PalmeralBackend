<?php

use Palmeral\Validator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ReallySimpleJWT\Token;

require './models/users.php';

class UsersController extends Validator
{
    private $users;

    public function __construct()
    {
        $this->users = new Palmeral\User();
    }

    public function get(Request $req,  Response $res)
    {
        $user = $this->users->all();
        return $res->withJson($user);
    }

    public function create(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if ($this->validateEmail($body['email'])) {
            $this->users->email = $body['email'];
            if ($this->validatePassword($body['password'])) {
                $this->users->password = password_hash($body['password'], PASSWORD_DEFAULT);
                if ($this->users->save()) {
                    return $res
                        ->withStatus(200)
                        ->withJson(['message' => 'Usuario creado correctamente']);
                }
                return $res->withStatus(500)->withJson(['message' => 'Error al crear usuario']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Contraseña incorrecta']);
        }
        return $res->withStatus(400)->withJson(['message' => 'Correo incorrecto']);
    }

    public function update(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if ($user = $this->users->find($body['id_user'])) {
            if ($this->validateEmail($body['email'])) {
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
    }

    public function password(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if ($user = $this->users->find($body['id_user'])) {
            if (password_verify($body['password'], $user->password)) {
                if ($this->validatePassword($body['new_password'])) {
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
    }

    public function delete(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if ($user = $this->users->find($body['id_user'])) {
            if ($user->delete()) {
                return $res
                    ->withStatus(200)
                    ->withJson(['message' => 'Usuario eliminado correctamente']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Error al eliminar usuario']);
        }
    }

    public function login(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if ($user = $this->users->where('email', $body['email'])->first()) {
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
    }

    public function find(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($user = $this->users->find($body['id_user'])) {
                return $res->withStatus(200)->withJson(['message' => 'Usuario encontrado', 'data' => $user]);
            }
            return $res->withStatus(400)->withJson(['message' => 'Usuario no encontrado']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }
}
