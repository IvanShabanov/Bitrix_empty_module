<?

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if (!$USER->IsAdmin()) {
	return;
}

if (file_exists(__DIR__ . "/install/module.cfg.php")) {
	include(__DIR__ . "/install/module.cfg.php");
};

if (!Loader::includeModule($arModuleCfg['MODULE_ID'])) {
	return;
}

Loc::loadMessages(__FILE__);


$currentUrl = $APPLICATION->GetCurPage() . '?mid=' . urlencode($mid) . '&amp;lang=' . LANGUAGE_ID;
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$doc_root = \Bitrix\Main\Application::getDocumentRoot();
$url_module = str_replace($doc_root, '', __DIR__);

$options_list = $arModuleCfg['options_list'];

$ok_message = '';
$eeror_message = '';

$saveOption = false;
$setDefault = false;

if ($request->getpost('saveoptions') != '') {
	$saveOption = true;
}

if ($request->getpost('saveoptionsdefault') != '') {
	$setDefault = true;
}


$isConfigurated = \Bitrix\Main\Config\Option::get($arModuleCfg['MODULE_ID'], 'IS_CONFIGURATED');
if ($isConfigurated != 'Y') {
	$setDefault = true;
}

function checkOption(string $option_name, $option)
{
	/* Тут проверяем значение настроек, есои есть ошибка, то возвращаем ее текст, иначе вернем true */
	return true;
}

foreach ($options_list as $option_name => $arOption) {
	$optionIsValid = false;
	if ($saveOption) {
		$option[$option_name] = $request->getpost('option_' . $option_name);
		$optionIsValid = checkOption($option_name, $option[$option_name]);
		if ($optionIsValid !== true) {
			$eeror_message .= 'ERROR: ' . Loc::getMessage('ISPRO___MODULENAME___' . $option_name) . ' ' . $optionIsValid . PHP_EOL;
		}
		if (is_array($option[$option_name])) {
			$option[$option_name] = json_encode($option[$option_name]);
		};
	};
	if ($setDefault) {
		$option[$option_name] = $arOption['default'];
		$optionIsValid = true;
	};
	if (($saveOption || $setDefault) && ($optionIsValid === true)) {
		\Bitrix\Main\Config\Option::set($arModuleCfg['MODULE_ID'], $option_name, $option[$option_name]);
		$ok_message .= 'SAVED: ' . Loc::getMessage('ISPRO___MODULENAME___' . $option_name) . PHP_EOL;
	};

	$option[$option_name] = \Bitrix\Main\Config\Option::get($arModuleCfg['MODULE_ID'], $option_name);

	if ($option_type == 'json') {
		$option[$option_name . '_VALUE'] = @json_decode($option[$option_name], true);
	};
};

if ($ok_message != '') {
	$message = new \CAdminMessage(array(
		'MESSAGE' => $ok_message,
		'TYPE' => 'OK'
	));
	echo $message->Show();
}
if ($eeror_message != '') {
	$message = new \CAdminMessage(array(
		'MESSAGE' => $eeror_message,
		'TYPE' => 'ERROR'
	));
	echo $message->Show();
}

$tabList = [
	[
		'DIV' => 'description',
		'TAB' => Loc::getMessage('ISPRO___MODULENAME___TAB_SET_DESC'),
		'ICON' => 'ib_settings',
		'TITLE' => Loc::getMessage('ISPRO___MODULENAME___TAB_TITLE_DESC')
	],
	[
		'DIV' => 'setting',
		'TAB' => Loc::getMessage('ISPRO___MODULENAME___TAB_SET_OPTION'),
		'ICON' => 'ib_settings',
		'TITLE' => Loc::getMessage('ISPRO___MODULENAME___TAB_TITLE_OPTION')
	],
];


$tabControl = new CAdminTabControl(str_replace('.', '_', $arModuleCfg['MODULE_ID']) . '_options', $tabList);
?>
<style>
	#__MODULENAME___form textarea {
		width: 100%;
		min-height: 150px;
	}
</style>
<form method="POST" action="<?= $currentUrl; ?>" enctype="multipart/form-data" id="__MODULENAME___form">
	<?= bitrix_sessid_post(); ?>
	<?
	$tabControl->Begin();
	?>

	<?
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td colspan="2">
			<?= BeginNote(); ?>
			<?= Loc::getMessage('ISPRO___MODULENAME___DESCRIPTION'); ?>
			<?= EndNote(); ?>
		</td>
	</tr>

	<?
	$tabControl->BeginNextTab();
	?>

	<?foreach ($options_list as $option_name => $arOption) :?>
		<tr>
			<td width="20%" valign="top">
				<?= Loc::getMessage('ISPRO___MODULENAME___'.$option_name) ?>
			</td>
			<td width="80%">
				<?if ($arOption['type'] == 'text') :?>
					<input type="text" name="option_<?=$option_name?>" value="<?=$option[$option_name]?>" />
				<?endif?>
				<?if ($arOption['type'] == 'textarea') :?>
					<textarea name="option_<?=$option_name?>"><?= $option['TEMPLATE'] ?></textarea>
				<?endif?>
				<?if ($arOption['type'] == 'checkbox') :?>
					<input type="hidden"  name="option_<?=$option_name?>" value="N" />
					<input type="checkbox" name="option_<?=$option_name?>" value="Y" <?= ($option[$option_name] == "Y") ? 'checked="checked"' : '' ?> />
				<?endif?>
				<?if ($arOption['type'] == 'select') :?>
					<select name="option_<?=$option_name?>">
					<? foreach ($arOption['values'] as $value) : ?>
						<option value="<?= $value ?>" <?= ($option[$option_name] == $value) ? 'selected' : '' ?>>
							<?= Loc::getMessage('ISPRO___MODULENAME___'.$option_name.'_'.$value) ?>
						</option>
					<? endforeach ?>
					</select>
				<?endif?>

			</td>
		</tr>
	<?endforeach?>

	<tr>
		<td colspan="2">
			<input type="submit" class="adm-btn-save" name="saveoptions" value="<? echo Loc::getMessage('ISPRO___MODULENAME___SAVE'); ?>">
			<input type="submit" class="adm-btn-save" name="saveoptionsdefault" value="<? echo Loc::getMessage('ISPRO___MODULENAME___DEFAULT'); ?>">
		</td>
	</tr>

	<? $tabControl->Buttons(); ?>
	<? $tabControl->End(); ?>
</form>