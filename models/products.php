<?php

namespace Palmeral;

use Illuminate\Database\Eloquent\Model;

require_once('./helpers/validator.php');
require_once('./config/database.php');

class Product extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id_product';
    protected $fillable = array('id_supplier', 'id_category', 'description', 'image');
}
