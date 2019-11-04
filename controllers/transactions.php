<?php

use Palmeral\Validator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ReallySimpleJWT\Token;

require './models/transactions.php';

class TransactionsController extends Validator
{
    private $transactions;

    public function __construct()
    {
        $this->transactions = new Palmeral\Transaction();
    }

    public function get(Request $req,  Response $res)
    {
        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            $transactions = $this->transactions->all();
            return $res->withJson($transactions);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function create(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($this->validateId($body['id_product'])) {
                $this->transactions->id_product = $body['id_product'];
                if ($this->validateLength($body['description'], 5, 1000)) {
                    $this->transactions->description = $body['description'];
                    if ($body['type'] == 0 || $body['type'] == 1) {
                        $this->transactions->type = $body['type'];
                        if ($this->validateDate($body['date'])) {
                            $this->transactions->date = $body['date'];
                            if ($this->validateMoney($body['purchase_price'])) {
                                $this->transactions->purchase_price = $body['purchase_price'];
                                if ($body['profit'] >= 0 || 100 >= $body['profit']) {
                                    $this->transactions->profit = $body['profit'];
                                    if ($this->validateId($body['quantity'])) {
                                        $this->transactions->quantity = $body['quantity'];

                                        $this->transactions->sale_price = ($body['purchase_price']) * (100 / (100 - $body['profit']));

                                        if ($transaction = $this->transactions
                                            ->where('id_product', $body['id_product'])
                                            ->orderBy('id_transaction', 'desc')
                                            ->first()
                                        ) {
                                            if ($body['type'] == 1) {
                                                $this->transactions->stock = $transaction['stock'] + $body['quantity'];
                                            } else if ($body['type'] == 0) {
                                                if ($transaction['stock'] >= $body['quantity']) {
                                                    $this->transactions->stock = $transaction['stock'] - $body['quantity'];
                                                } else {
                                                    return $res->withStatus(400)->withJson(['message' => 'No hay suficiente stock para restar']);
                                                }
                                            }
                                        } else {
                                            $this->transactions->stock = $body['quantity'];
                                        }

                                        if ($this->transactions->save()) {
                                            return $res
                                                ->withStatus(200)
                                                ->withJson(['message' => 'Transacción creada correctamente']);
                                        }
                                        return $res->withStatus(500)->withJson(['message' => 'Error al crear transacción']);
                                    }
                                    return $res->withStatus(400)->withJson(['message' => 'Cantidad incorrecta, debe ser un valor númerico']);
                                }
                                return $res->withStatus(400)->withJson(['message' => '% de ganancia incorrecto, debe ser un valor entre 0 y 100']);
                            }
                            return $res->withStatus(400)->withJson(['message' => 'Precio de compra incorrecto']);
                        }
                        return $res->withStatus(400)->withJson(['message' => 'Fecha incorrecta']);
                    }
                    return $res->withStatus(400)->withJson(['message' => 'Tipo de transacción incorrecta']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Descripción incorrecta, debe contener entre 5 y 1000 carácteres']);
            }
            return $res->withStatus(400)->withJson(['message' => 'Producto incorrecto']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }

    public function delete(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($transaction = $this->transactions->find($body['id_transaction'])) {
                if ($transaction->delete()) {
                    return $res
                        ->withStatus(200)
                        ->withJson(['message' => 'Transacción eliminada correctamente']);
                }
                return $res->withStatus(400)->withJson(['message' => 'Error al eliminar transacción']);
            }
            return $res->withStatus(403)->withJson(['message' => 'Transacción no encontrada']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }
}
