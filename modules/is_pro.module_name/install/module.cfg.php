<?
$arModuleCfg = [
	'MODULE_ID' => 'is_pro.__MODULENAME__',

	/* Настройки модуля */
	'options_list' => [

		/* Пример настройки с выбором (select) */

		'MODULE_MODE' => [ 					/* Имя настройки */
			'type' => 'select', 			/* Тип поля настройки */
			'values' => [					/* Значения настройки */
				'off',
				'test',
				'on'
			],
			'default' => 'off'				/* Значение по умолчанию */
		],

		'CHECBOX_SETTING' => [
			'type' => 'checkbox',
			'default' => 'Y'
		],

		'STRING_SETTING' => [
			'type' => 'text',
			'default' => ''
		],

		'TEXTAREA_SETTING' => [
			'type' => 'textarea',
			'default' => ''
		]
	]
];