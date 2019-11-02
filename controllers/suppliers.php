<?php

use Palmeral\Validator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ReallySimpleJWT\Token;

require './models/suppliers.php';

class SuppliersController extends Validator
{
    private $suppliers;

    public function __construct()
    {
        $this->suppliers = new Palmeral\Supplier();
    }

    public function get(Request $req,  Response $res)
    {
        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            $supplier = $this->suppliers->all();
            return $res->withJson($supplier);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function create(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($this->validateAlphanumeric($body['name'], 5, 1000)) {
                $this->suppliers->name = $body['name'];
                if (strlen($body['address']) > 4) {
                    $this->suppliers->address = $body['address'];
                    if ($this->validatePhone($body['phone'])) {
                        $this->suppliers->phone = $body['phone'];
                        if ($this->validateEmail($body['email'])) {
                            $this->suppliers->email = $body['email'];
                        }
                        if ($this->suppliers->save()) {
                            return $res
                                ->withStatus(200)
                                ->withJson(['message' => 'Proveedor creado correctamente']);
                        }
                        return $res->withStatus(500)->withJson(['message' => 'Error al crear proveedor']);
                    }
                    return $res->withStatus(400)->withJson(['message' => 'Teléfono incorrecto']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Dirección incorrecta']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Nombre incorrecto']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function update(Request $req, Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($supplier = $this->suppliers->find($body['id_supplier'])) {
                if ($this->validateAlphanumeric($body['name'], 5, 1000)) {
                    $supplier->name = $body['name'];
                    if (strlen($body['address']) > 4) {
                        $supplier->address = $body['address'];
                        if ($this->validatePhone($body['phone'])) {
                            $supplier->phone = $body['phone'];
                            if ($this->validateEmail($body['email'])) {
                                $supplier->email = $body['email'];
                            } else {
                                $supplier->email = null;
                            }

                            if ($supplier->save()) {
                                return $res
                                    ->withStatus(200)
                                    ->withJson(['message' => 'Proveedor modificado correctamente']);
                            }
                            return $res->withStatus(500)->withJson(['message' => 'Error al modificar proveedor']);
                        }
                        return $res->withStatus(400)->withJson(['message' => 'Teléfono incorrecto']);
                    }
                    return $res->withStatus(400)->withJson(['message' => 'Dirección incorrecta']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Nombre incorrecto']);
            }
            return $res->withStatus(403)->withJson(['message' => 'Proveedor no encontrado']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function delete(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($supplier = $this->suppliers->find($body['id_supplier'])) {
                if ($supplier->delete()) {
                    return $res
                        ->withStatus(200)
                        ->withJson(['message' => 'Proveedor eliminado correctamente']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Error al eliminar proveedor']);
            }
            return $res->withStatus(403)->withJson(['message' => 'Proveedor no encontrado']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function find(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($supplier = $this->suppliers->find($body['id_supplier'])) {
                return $res->withStatus(200)->withJson(['message' => 'Proveedor encontrado', 'data' => $supplier]);
            }
            return $res->withStatus(400)->withJson(['message' => 'Proveedor no encontrado']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }
}
