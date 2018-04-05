<?php
namespace USD\API\Model;

use Carbon\Carbon;

/**
 * USD handler, with Paris Model structure.
 * @author Aldarien
 *
 * @property int id;
 * @property Carbon fecha;
 * @property double valor;
 */
class USD extends \Model
{
	/**
	 * Diferent table name from 'usd' for Paris
	 * @var string
	 */
	public static $_table = 'usds';

	/**
	 * Transform USD to $CLP
	 * @param double $usd
	 * @return double
	 */
	public function pesos(double $usd): double
	{
		return $usd * $this->valor;
	}
	/**
	 * Transform $CLP to USD
	 * @param int $pesos
	 * @return double
	 */
	public function usd(int $pesos): double
	{
		return $pesos / $this->valor;
	}
}
?>
