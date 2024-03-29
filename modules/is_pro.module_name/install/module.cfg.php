<?
$arModuleCfg = [
	'MODULE_ID' => mb_strtolower('is_pro.module_name'),

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
		],

		'FILE_SETTING' => [
			'type' => 'file',
			'ext'	=> 'csv'
		]

	]
];