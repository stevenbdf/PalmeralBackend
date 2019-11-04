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
                    return $res->withStatus(200)->withJson(['message' => 'Se ha enviado tu nueva contraseña, revisa tu correo electronico']);
                } else {
                    return $res->withStatus(500)->withJson(['message' => 'Error al enviar correo, solicita ayuda en stevenbdf@gmail.com']);
                }
            } catch (Exception $e) {
                return $res->withStatus(500)->withJson(['message' => 'Error al enviar correo, solicita ayuda en stevenbdf@gmail.com']);
            }
        }

        return $res->withStatus(400)->withJson(['message' => 'Usuario no encontrado']);
    }

    public function validateToken(Request $req,  Response $res)
    {
        if (Token::validate($this->getBearerToken($req), $_ENV['SECRET_KEY'])) {
            return $res->withStatus(200)->withJson(['message' => 'Token valido']);
        }
        return $res->withStatus(403)->withJson(['message' => 'Acceso no autorizado']);
    }
}
