<?php

use Palmeral\Validator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ReallySimpleJWT\Token;

require './models/categories.php';

class CategoriesController extends Validator
{
    private $categories;

    public function __construct()
    {
        $this->categories = new Palmeral\Category();
    }

    public function get(Request $req,  Response $res)
    {
        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            $categories = $this->categories->all();
            return $res->withJson($categories);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function create(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($this->validateAlphanumeric($body['name'], 5, 1000)) {
                $this->categories->name = $body['name'];
                if ($this->validateAlphanumeric($body['description'], 5, 1000)) {
                    $this->categories->description = $body['description'];
                    if ($this->categories->save()) {
                        return $res
                            ->withStatus(200)
                            ->withJson(['message' => 'Categoria creada correctamente']);
                    }
                    return $res->withStatus(500)->withJson(['message' => 'Error al crear categoria']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Descripción incorrecta']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Nombre incorrecto']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function update(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($category = $this->categories->find($body['id_category'])) {
                if ($this->validateAlphanumeric($body['name'], 5, 1000)) {
                    $category->name = $body['name'];
                    if ($this->validateAlphanumeric($body['description'], 5, 1000)) {
                        $category->description = $body['description'];
                        if ($category->save()) {
                            return $res
                                ->withStatus(200)
                                ->withJson(['message' => 'Categoria modificada correctamente']);
                        }
                        return $res->withStatus(500)->withJson(['message' => 'Error al modificar categoria']);
                    }
                    return $res->withStatus(400)->withJson(['message' => 'Descripción incorrecta']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Nombre incorrecto']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Categoria no encontrada']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function delete(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($category = $this->categories->find($body['id_category'])) {
                if ($category->delete()) {
                    return $res
                        ->withStatus(200)
                        ->withJson(['message' => 'Categoria eliminada correctamente']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Error al eliminar categoria']);
            }
            return $res->withStatus(403)->withJson(['message' => 'Categoria no encontrada']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function find(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($category = $this->categories->find($body['id_category'])) {
                return $res->withStatus(200)->withJson(['message' => 'Categoria encontrada', 'data' => $category]);
            }
            return $res->withStatus(400)->withJson(['message' => 'Categoria no encontrada']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }
}
