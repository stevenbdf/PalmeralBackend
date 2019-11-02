<?php

namespace Palmeral;

use Illuminate\Database\Eloquent\Model;

require_once('./helpers/validator.php');
require_once('./config/database.php');

class Supplier extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id_supplier';
    protected $fillable = array('name', 'address', 'phone', 'email');
}
