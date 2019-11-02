<?php

namespace Palmeral;

use Illuminate\Database\Eloquent\Model;

require_once('./helpers/validator.php');
require_once('./config/database.php');

class Category extends Model
{
    public $timestamps = false;
    protected $table = 'categories';
    protected $primaryKey = 'id_category';
    protected $fillable = array('name', 'description');
}
