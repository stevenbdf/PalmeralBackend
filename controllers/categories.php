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
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
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
                            ->withJson(['status' => 1, 'message' => 'Categoria creada correctamente']);
                    }
                    return $res->withJson(['status' => 0, 'message' => 'Error al crear categoria']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Descripción incorrecta']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Nombre incorrecto']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
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
                                ->withJson(['status' => 1, 'message' => 'Categoria modificada correctamente']);
                        }
                        return $res->withJson(['status' => 0, 'message' => 'Error al modificar categoria']);
                    }
                    return $res->withJson(['status' => 0, 'message' => 'Descripción incorrecta']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Nombre incorrecto']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Categoria no encontrada']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }

    public function delete(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($category = $this->categories->find($body['id_category'])) {
                if ($category->delete()) {
                    return $res
                        ->withJson(['status' => 1, 'message' => 'Categoria eliminada correctamente']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Error al eliminar categoria']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Categoria no encontrada']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }

    public function find(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($category = $this->categories->find($body['id_category'])) {
                return $res->withJson(['status' => 1, 'message' => 'Categoria encontrada', 'data' => $category]);
            }
            return $res->withJson(['status' => 0, 'message' => 'Categoria no encontrada']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }
}
