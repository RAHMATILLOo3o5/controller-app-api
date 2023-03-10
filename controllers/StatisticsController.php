<?php

namespace app\controllers;

use app\models\Statistics;

class StatisticsController extends BaseController
{
	public $modelClass = Statistics::class;

	public function actions ()
	{
		$actions = parent::actions();
		unset($actions[ 'view' ], $actions[ 'delete' ], $actions[ 'update' ]);
		
		return $actions;
	}

	public function actionCreate ()
	{
		
	}
	
}