<?php

namespace Palmeral;

use Illuminate\Database\Eloquent\Model;

require_once('../../helpers/validator.php');
require_once('../../config/database.php');

class User extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id_user';
    protected $fillable = array('email', 'password');
}
