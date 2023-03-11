<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "statistics".
 *
 * @property int $id
 * @property float|null $total_spent
 * @property float|null $total_benefit
 * @property float|null $pure_benefit
 * @property int|null $created_at
 */
class Statistics extends \yii\db\ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName ()
	{
		return 'statistics';
	}

	public function behaviors ()
	{
		return [
			[
				'class' => TimestampBehavior::class,
				'updatedAtAttribute' => false
			]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules ()
	{
		return [
			[ [ 'total_spent', 'total_benefit', 'pure_benefit' ], 'number' ],
			[ [ 'created_at' ], 'integer' ],
		];
	}

	public function saved ()
	{
		$this->total_spent = $this->calculateSpent();
		$this->total_benefit = $this->calculateBenefit();
		$this->pure_benefit = $this->calculatePureBenefit();
		return $this->save() ? $this : $this->errors;
	}

	private function calculateSpent ()
	{
		$product_spent = Product::find()->sum('purchase_price') ?? 0;
		$other_spent = OtherSpent::find()->sum('sum') ?? 0;
		return $product_spent + $other_spent;
	}

	private function calculateBenefit ()
	{
		return Selling::find()->sum('sell_price') ?? 0;
	}

	private function calculatePureBenefit ()
	{
		$sell_online = Selling::find()->where([ 'type_sell' => Selling::PAY_ONLINE ])->sum('sell_price') ?? 0;
		$plastic_card_tax = PlasticCardTax::find()->orderBy([ 'id' => SORT_DESC ])->one();
		$plastic_spent = $sell_online * ( $plastic_card_tax / 100 );
		$sell_debt = Selling::find()->where([ 'type_sell' => Selling::PAY_DEBT ])->sum('sell_price');
		$pay_instant = DebtHistory::find()->sum('pay_amount') ?? 0;
		$pay_history_amount = PaymentHistoryList::find()->sum('pay_amount') ?? 0;
		$debt = $sell_debt - $pay_instant + $pay_history_amount;
		return $this->calculateBenefit() - ( $this->calculateSpent() + $plastic_spent + $debt );
	}

}
