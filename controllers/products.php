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
            $products = $this->products
                ->join('suppliers', 'products.id_supplier', '=', 'suppliers.id_supplier')
                ->join('categories', 'products.id_category', '=', 'categories.id_category')
                ->select('products.id_product', 'image', 'products.description', 'categories.name as category', 'suppliers.name as supplier')
                ->get();
            return $res->withJson($products);
        }
        return $res->withJson(['status' => 1, 'message' => 'Acceso no autorizado']);
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
                                        ->withJson(['status' => 1, 'message' => 'Producto creado correctamente']);
                                }
                                return $res
                                    ->withJson(['status' => 1, 'message' => 'Producto creado, no se guardo la imagen']);
                            }
                            return $res->withJson(['status' => 0, 'message' => 'Error al crear producto']);
                        }
                        return $res->withJson(['status' => 0, 'message' => $this->getImageError()]);
                    }
                    return $res->withJson(['status' => 0, 'message' => 'Nombre incorrecto']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Categoria incorrecta']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Proveedor incorrecto']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
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

                                            ->withJson(['status' => 1, 'message' => 'Producto modificado correctamente']);
                                    }
                                    return $res->withJson(['status' => 1, 'message' => 'Producto modificado, no se modifico la imagen']);
                                }
                                return $res
                                    ->withJson(['status' => 1, 'message' => 'Producto modificado, no se guardo la imagen']);
                            }
                            return $res->withJson(['status' => 0, 'message' => 'Error al modificar producto']);
                        }
                        return $res->withJson(['status' => 0, 'message' => 'Nombre incorrecto']);
                    }
                    return $res->withJson(['status' => 0, 'message' => 'Categoria incorrecta']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Proveedor incorrecto']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Producto no encontrado']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }

    public function delete(Request $req, Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($product = $this->products->find($body['id_product'])) {
                if ($product->delete()) {
                    if ($this->deleteFile($this->PATH, $product->image)) {
                        return $res->withJson(['status' => 1, 'message' => 'Producto eliminado correctamente']);
                    }
                    return $res->withJson(['status' => 1, 'message' => 'Producto eliminado, no se pudo eliminar la imagen']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Error al aliminar producto']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Producto no encontrado']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }

    public function find(Request $req, Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($product = $this->products->find($body['id_product'])) {
                return $res->withJson(['status' => 1, 'message' => 'Producto encontrado', 'data' => $product]);
            }
            return $res->withJson(['status' => 0, 'message' => 'Producto no encontrado']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }
}
