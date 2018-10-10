<?php namespace Emp\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CanoeClients extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
    protected $connection = 'canoe';
	protected $table = 'clients';
	public $timestamps = false;

} 

