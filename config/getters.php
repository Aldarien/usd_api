<?php
return [
		/*'investing' => [
				'ajax' => true,
				'url' => 'https://es.investing.com/instruments/HistoricalDataAjax',
				'params' => [
					'action' => 'historical_data',
					'curr_id' => 2110,
					'header' => 'Datos+históricos+USD/CLP',
					'interval_sec' => 'Daily',
					'smlID' => 107258,
					'sort_col' => 'date',
					'sort_ord' => 'DESC'
				],
				'variables' => [
					'st_date',
					'end_date'
				],
				'class' => \USD\API\Provider\InvestingGetter::class
		],
		'bcentral' => [
				'ajax' => true,
				'url' => 'https://si3.bcentral.cl/bdemovil/BDE/SeriesData/MOV_SC_TC1',
				'params' => [],
				'variables' => [],
				'class' => \USD\API\Provider\BCentralGetter::class
		],*/
		'sii' => [
				'url' => 'http://www.sii.cl/valores_y_fechas/dolar/',
				'part' => 'dolar<year>.htm',
				'class' => \USD\API\Provider\SIIGetter::class
		]/*,
		'cambio' => [
				'url' => 'https://cambio.today/',
				'part' => 'historico?currencyfrom=dolar-norteamericano&currencyto=peso-chileno&startdate=<year>-01-01&enddate=<year>-12-31&p=1',
				'class' => \USD\API\Provider\CambioGetter::class
		]*/
];
?>
