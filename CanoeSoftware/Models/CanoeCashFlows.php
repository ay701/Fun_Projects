<?php namespace Emp\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CanoeCashFlows extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
    protected $connection = 'canoe';
	protected $table = 'cash_flows';
	public $timestamps = false;

} 

