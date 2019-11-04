<?php

use Palmeral\Validator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ReallySimpleJWT\Token;

require './models/products.php';

class ProductsController extends Validator
{
    private $products;
    private $PATH = './images/';

    public function __construct()
    {
        $this->products = new Palmeral\Product();
    }

    public function get(Request $req,  Response $res)
    {
        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            $products = $this->products->all();
            return $res->withJson($products);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function create(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        isset($_FILES['image']) ? $image = $_FILES['image'] : $image = false;

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($this->validateId($body['id_supplier'])) {
                $this->products->id_supplier = $body['id_supplier'];
                if ($this->validateId($body['id_category'])) {
                    $this->products->id_category = $body['id_category'];
                    if (strlen($body['description']) > 5) {
                        $this->products->description = $body['description'];
                        if ($this->validateImageFile($image, $this->PATH, null, 300, 500)) {
                            $this->products->image = $this->getImageName();
                            if ($this->products->save()) {
                                if ($this->saveFile($image, $this->PATH, $this->products->image)) {
                                    return $res
                                        ->withStatus(200)
                                        ->withJson(['message' => 'Producto creado correctamente']);
                                }
                                return $res
                                    ->withStatus(200)
                                    ->withJson(['message' => 'Producto creado, no se guardo la imagen']);
                            }
                            return $res->withStatus(400)->withJson(['message' => 'Error al crear producto']);
                        }
                        return $res->withStatus(400)->withJson(['message' => $this->getImageError()]);
                    }
                    return $res->withStatus(400)->withJson(['message' => 'Nombre incorrecto']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Categoria incorrecta']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Proveedor incorrecto']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function update(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        isset($_FILES['image']) ? $image = $_FILES['image'] : $image = false;

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($product = $this->products->find($body['id_product'])) {
                if ($this->validateId($body['id_supplier'])) {
                    $product->id_supplier = $body['id_supplier'];
                    if ($this->validateId($body['id_category'])) {
                        $product->id_category = $body['id_category'];
                        if (strlen($body['description']) > 5) {
                            $product->description = $body['description'];
                            $isImage = false;
                            if ($this->validateImageFile($image, $this->PATH, $product->image, 300, 500)) {
                                $isImage = true;
                            }

                            if ($product->save()) {
                                if ($isImage) {
                                    if ($this->saveFile($image, $this->PATH, $product->image)) {
                                        return $res
                                            ->withStatus(200)
                                            ->withJson(['message' => 'Producto modificado correctamente']);
                                    }
                                    return $res->withStatus(200)->withJson(['message' => 'Producto modificado, no se modifico la imagen']);
                                }

                                return $res
                                    ->withStatus(200)
                                    ->withJson(['message' => 'Producto modificado, no se guardo la imagen']);
                            }
                            return $res->withStatus(500)->withJson(['message' => 'Error al modificar producto']);
                        }
                        return $res->withStatus(400)->withJson(['message' => 'Nombre incorrecto']);
                    }
                    return $res->withStatus(400)->withJson(['message' => 'Categoria incorrecta']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Proveedor incorrecto']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Producto no encontrado']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function delete(Request $req, Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($product = $this->products->find($body['id_product'])) {
                if ($product->delete()) {
                    if ($this->deleteFile($this->PATH, $product->image)) {
                        return $res->withStatus(200)->withJson(['message' => 'Producto eliminado correctamente']);            
                    }
                    return $res->withStatus(200)->withJson(['message' => 'Producto eliminado, no se pudo eliminar la imagen']);            
                }
                return $res->withStatus(500)->withJson(['message' => 'Error al aliminar producto']);    
            }
            return $res->withStatus(400)->withJson(['message' => 'Producto no encontrado']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function find(Request $req, Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($product = $this->products->find($body['id_product'])) {
                return $res->withStatus(200)->withJson(['message' => 'Producto encontrado', 'data' => $product]);
            }
            return $res->withStatus(400)->withJson(['message' => 'Producto no encontrado']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }
}
