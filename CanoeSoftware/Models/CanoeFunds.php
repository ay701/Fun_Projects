<?php namespace Emp\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CanoeFunds extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
    protected $connection = 'canoe';
	protected $table = 'funds';
	public $timestamps = false;

} 

