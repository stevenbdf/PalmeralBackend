<?php

namespace Palmeral;

use Illuminate\Database\Eloquent\Model;

require_once('./helpers/validator.php');
require_once('./config/database.php');

class Transaction extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id_transaction';
    protected $fillable = array(
        'id_product', 'description', 'sale_price', 'type', 'date', 'purchase_price',
        'profit', 'quantity', 'stock'
    );
}
