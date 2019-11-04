<?php

namespace Palmeral;

class Validator
{
    /**Declaración de variables privadas de la clase */
    private $imageError = null;
    private $imageName = null;
    private $claveError = null;

    /**
     * Retorna el nombre de una imagen
     * @return {string} - imagenName
     */
    public function getImageName()
    {
        return $this->imageName;
    }
    /**
     * Retorna el error de una contraseña
     * @return {string} - error
     */
    public function getClaveError()
    {
        switch ($this->claveError) {
            case 1:
                $error = 'La contraseña debe contener números entre 0 - 9';
                break;
            case 2:
                $error = 'La contraseña debe tener una letra mayúscula';
                break;
            case 3:
                $error = 'La contraseña debe tener una letra minúscula';
                break;
            case 4:
                $error = 'La contraseña debe tener caracteres especiales';
                break;
            case 5:
                $error = 'La contraseña debe tener al menos 8 caracterers';
                break;
            default:
                $error = 'Ocurrió un problema con la contraseña';
        }
        return $error;
    }

    public function getImageError()
    {
        switch ($this->imageError) {
            case 1:
                $error = 'El tipo de la imagen debe ser gif, jpg o png';
                break;
            case 2:
                $error = 'La dimensión de la imagen es incorrecta';
                break;
            case 3:
                $error = 'El tamaño de la imagen debe ser menor a 2MB';
                break;
            case 4:
                $error = 'El archivo de la imagen no existe';
                break;
            default:
                $error = 'Ocurrió un problema con la imagen';
        }
        return $error;
    }

    /**
     * Valida espacios vacios de un formulario
     * @param {array} - $fields - Conjunto de campos de un formulario
     * @return {array} - $fields - Conjunto de campos de un formulario validados
     */
    public function validateForm($fields)
    {
        foreach ($fields as $index => $value) {
            $value = strip_tags(trim($value));
            $fields[$index] = $value;
        }
        return $fields;
    }

    /**
     * Valida un numero entero mayor a 1
     * @param {int} $value -  Numero entero
     * @return {boolean} - Caso exito o error
     */
    public function validateId($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT, array('min_range' => 1))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida un formato de correo
     * @param {string} $email - Correo string
     * @return {boolean} - Caso exito o error
     */
    public function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida valores Alfabeticos
     * @param {string} $value -  Texto a evaluar
     * @param {int} $minimum - Longitud minima
     * @param {int} $maximum - Longitud maxima
     * @return {boolean} - Caso exito o error
     */
    public function validateAlphabetic($value, $minimum, $maximum)
    {
        if (preg_match('/^[a-zA-ZñÑáÁéÉíÍóÓúÚ\s\.-]{' . $minimum . ',' . $maximum . '}$/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida valores Alfabeticos y numericos
     * @param {string} $value -  Texto a evaluar
     * @param {int} $minimum - Longitud minima
     * @param {int} $maximum - Longitud maxima
     * @return {boolean} - Caso exito o error
     */
    public function validateAlphanumeric($value, $minimum, $maximum)
    {
        if (preg_match('/^[a-zA-Z0-9ñÑáÁéÉíÍóÓúÚ\s\.-]{' . $minimum . ',' . $maximum . '}$/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida formato de dinero
     * @param {string} $value -  Texto a evaluar
     * @return {boolean} - Caso exito o error
     */
    public function validateMoney($value)
    {
        if (preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida formato de telefono
     * @param {string} $value -  Texto a evaluar
     * @return {boolean} - Caso exito o error
     */
    public function validatePhone($value)
    {
        if (preg_match('/^[0-9]{8}$/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida formato de DUI
     * @param {int} $value -  Texto a evaluar
     * @return {boolean} - Caso exito o error
     */
    public function validateDui($value)
    {
        if (preg_match('/^[0-9]{8}+(-)+([0-9]{1})$/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida longitud de texto mayor a 6 caracteres
     * @param {string} $value -  Texto a evaluar
     * @return {boolean} - Caso exito o error
     */
    public function validatePassword($value)
    {
        if (strlen($value) > 8) {
            if (preg_match('#[0-9]+#', $value)) {
                if (preg_match('#[a-z]+#', $value)) {
                    if (preg_match('#[A-Z]+#', $value)) {
                        if (preg_match("/[`'\"~!@# $*()<>,.:;+{}\|]/", $value)) {
                            return true;
                        } else {
                            $this->claveError = 4;
                            return false;
                        }
                    } else {
                        $this->claveError = 2;
                        return false;
                    }
                } else {
                    $this->claveError = 3;
                    return false;
                }
            } else {
                $this->claveError = 1;
                return false;
            }
        } else {
            $this->claveError = 5;
            return false;
        }
    }

    /**
     * Valida tipo de imagen , tamaño, entre otras propiedades
     * @param {Object} $file - Imagen a evaluar
     * @param {string} $path - Ruta de la imagen
     * @param {string} $name - Nombre de la imagen
     * @param {int} $minWidth - Ancho maximo de imagen
     * @param {int} $maxHeight - Alto maximo de imagen
     * @return {boolean} - Caso exito o error
     */
    public function validateImageFile($file, $path, $name, $minWidth, $maxWidth)
    {
        if ($file) {
            if ($file['size'] <= 2097152) {
                list($width, $height, $type) = getimagesize($file['tmp_name']);

                if (($minWidth <= $width && $minWidth <= $height) && ($maxWidth >= $width && $maxWidth >= $height)) {

                    //Tipos de imagen: 2 - JPG y 3 - PNG
                    if ($type == 2 || $type == 3) {
                        if ($name) {
                            if (file_exists($path . $name)) {
                                $this->imageName = $name;
                                return true;
                            } else {
                                $this->imageError = 4;
                                return false;
                            }
                        } else {
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $this->imageName = uniqid() . '.' . $extension;
                            return true;
                        }
                    } else {
                        $this->imageError = 1;
                        return false;
                    }
                } else {
                    $this->imageError = 2;
                    return false;
                }
            } else {
                $this->imageError = 3;
                return false;
            }
        } else {
            if ($path . $name == './images/') {
                $this->imageError = 4;
                return false;
            } else {
                if (file_exists($path . $name)) {
                    $this->imageName = $name;
                    return true;
                } else {
                    $this->imageError = 4;
                    return false;
                }
            }
        }
    }

    /**
     * Guarda documentos de texto
     * @param {Object} $file - Documento a guardar
     * @param {int} $id - Codigo con el que se guardara el archivo
     * @return {boolean} - Caso exito o error
     */
    public function saveDocumentFile($file, $id)
    {
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileTmpName = $file["tmp_name"];
        if ($fileType == 'docx' || $fileType == 'doc' || $fileType == 'pdf' || $fileType == 'xlsx' || $fileType == 'xls') {
            $path = '../../../resource/docs/';
            if (file_exists($path)) {
                if (file_exists($path . $id . '.doc')) {
                    unlink($path . $id . '.doc');
                } else if (file_exists($path . $id . '.docx')) {
                    unlink($path . $id . '.docx');
                } else if (file_exists($path . $id . '.pdf')) {
                    unlink($path . $id . '.pdf');
                } else if (file_exists($path . $id . '.xlsx')) {
                    unlink($path . $id . '.xlsx');
                } else if (file_exists($path . $id . '.xls')) {
                    unlink($path . $id . '.xls');
                } else if (file_exists($path . $id . '.ppt')) {
                    unlink($path . $id . '.ppt');
                } else if (file_exists($path . $id . '.pptx')) {
                    unlink($path . $id . '.pptx');
                }

                if (move_uploaded_file($fileTmpName, $path . $id . '.' . $fileType)) {
                    switch ($fileType) {
                        case 'doc':
                            return '.doc';
                        case 'docx':
                            return '.docx';
                        case 'pdf':
                            return '.pdf';
                        case 'xlsx':
                            return '.xlsx';
                        case 'xls':
                            return '.xls';
                        case 'ppt':
                            return '.ppt';
                        case 'pptx':
                            return '.pptx';
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * Guarda imagenes 
     * @param {Object} $file - Imagen a guardar
     * @param {string} $path - Ruta donde se guardara la imagen
     * @param {string} $name - Nombre de la imagen a guardar
     * @return {boolean} - Caso exito o error
     */
    public function saveFile($file, $path, $name)
    {
        if (file_exists($path)) {
            if ($file) {
                if (move_uploaded_file($file['tmp_name'], $path . $name)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Borra archivos 
     * @param {string} $path - Ruta donde se encuentra el archivo
     * @param {string} $name - Nombre del archivo a borrar
     * @return {boolean} - Caso exito o error
     */
    public function deleteFile($path, $name)
    {
        if (file_exists($path)) {
            if (unlink($path . $name)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Valida longitud
     * @param {string} $value - Texto a evaluar
     * @param {int} $minLength - Longitud minima
     * @param {int} $maxLength - Longitud maxima
     * @return {boolean} - Caso de exito o error
     */
    public function validateLength($value, $minLength, $maxLength)
    {
        if (strlen($value) >= $minLength && strlen($value) <= $maxLength) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida formato de fecha
     * @param {string} $value - Fecha a evaluar
     * @return {boolean} - Caso de exito o error
     */
    public function validateDate($value)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Valida formato de hora
     * @param {string} $value - Hora a evaluar
     * @return {boolean} - Caso de exito o error
     */
    public function validateTime($value)
    {
        if (preg_match("/^([0-1][0-9]|[2][0-3])[\:]([0-5][0-9])[\:]([0-5][0-9])$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    public function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    public function getBearerToken($req)
    {
        $headers = $req->getHeader('HTTP_AUTHORIZATION')[0];
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
