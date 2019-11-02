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

        $image = $_FILES['image'];

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
}
