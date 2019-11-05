<?php

use Palmeral\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ReallySimpleJWT\Token;

require './models/users.php';
require './helpers/PHPMailer/Exception.php';
require './helpers/PHPMailer/PHPMailer.php';
require './helpers/PHPMailer/SMTP.php';

class UsersController extends Validator
{
    private $users;

    public function __construct()
    {
        $this->users = new Palmeral\User();
    }

    public function get(Request $req,  Response $res)
    {
        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            $payload = Token::getPayload($this->getBearerToken($req), $_ENV['SECRET_KEY']);
            $user = $this->users->select('id_user', 'email')->where('id_user', '!=', $payload['id_user'])->get();
            return $res->withJson($user);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
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
                        ->withJson(['status' => 1, 'message' => 'Usuario creado correctamente']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Error al crear usuario']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Contraseña incorrecta']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Correo incorrecto']);
    }

    public function update(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if ($user = $this->users->find($body['id_user'])) {
            if ($this->validateEmail($body['email'])) {
                $user->email = $body['email'];
                if ($user->save()) {
                    return $res
                        ->withJson(['status' => 1, 'message' => 'Usuario modificado correctamente']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Error al modificar usuario']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Correo incorrecto']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Usuario no encontrado']);
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
                            ->withJson(['status' => 1, 'message' => 'Contraseña modificado correctamente']);
                    }
                    return $res->withJson(['status' => 0, 'message' => 'Error al modificar contraseña']);
                }
                return $res->withJson(['status' => 0, 'message' => 'Contraseña nueva invalida, 
                        debe contener mayusculas, minusculas, numeros y caracteres especiales']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Contraseña incorrecta']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Usuario no encontrado']);
    }

    public function delete(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if ($user = $this->users->find($body['id_user'])) {
            if ($user->delete()) {
                return $res
                    ->withJson(['status' => 1, 'message' => 'Usuario eliminado correctamente']);
            }
            return $res->withJson(['status' => 0, 'message' => 'Error al eliminar usuario']);
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
                return $res->withJson([
                    'token' => $token, 'id_user' => $user->id_user,
                    'status' => 1, 'message' => 'Bienvenido'
                ]);
            }
            return $res->withJson(['status' => 0, 'message' => 'Contraseña incorrecta']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Usuario no encontrado']);
    }

    public function find(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();

        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            if ($user = $this->users->select('id_user', 'email')->where('id_user', $body['id_user'])->first()) {
                return $res->withJson(['status' => 1, 'message' => 'Usuario encontrado', 'data' => $user]);
            }
            return $res->withJson(['status' => 0, 'message' => 'Usuario no encontrado']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }

    public function resetPassword(Request $req,  Response $res)
    {
        $body = $req->getParsedBody();


        $mail = new PHPMailer(true);

        if ($user = $this->users->where('email', $body['email'])->first()) {
            $contrasenaCorrecta = false;
            while (!$contrasenaCorrecta) {
                $nuevaContrasena = $this->generateRandomString(9);
                if ($this->validatePassword($nuevaContrasena)) {
                    $user->password
                        = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
                    $contrasenaCorrecta = true;
                    $user->save();
                }
            }

            try {
                //Server settings
                $mail->SMTPDebug = 0;                                       // Enable verbose debug output
                $mail->isSMTP();                                            // Set mailer to use SMTP
                $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'inventario.palmeral@gmail.com';        // SMTP username
                $mail->Password   = 'Qwerty123$';                             // SMTP password
                $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
                $mail->Port       = 587;
                $mail->CharSet = 'UTF-8';                                // TCP port to connect to

                //Recipients
                $mail->setFrom('inventario.palmeral@gmail.com', 'Inventario Palmeral');
                $mail->addAddress($body['email']);

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Recuperación de Contraseña';
                $mail->Body    = "Solicitaste reestablecer tu contraseña, tu nueva contraseña es: <b>$nuevaContrasena</b>";
                $mail->AltBody = "Solicitaste reestablecer tu contraseña, tu nueva contraseña es: $nuevaContrasena";

                if ($mail->send()) {
                    return $res->withJson(['status' => 1, 'message' => 'Se ha enviado tu nueva contraseña, revisa tu correo electronico']);
                } else {
                    return $res->withJson(['status' => 0, 'message' => 'Error al enviar correo, solicita ayuda en stevenbdf@gmail.com']);
                }
            } catch (Exception $e) {
                return $res->withJson(['status' => 0, 'message' => 'Error al enviar correo, solicita ayuda en stevenbdf@gmail.com']);
            }
        }

        return $res->withJson(['status' => 0, 'message' => 'Usuario no encontrado']);
    }

    public function validateToken(Request $req,  Response $res)
    {
        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            return $res->withJson(['status' => 1, 'message' => 'Token valido']);
        }
        return $res->withJson(['status' => 0, 'message' => 'Acceso no autorizado']);
    }
}
