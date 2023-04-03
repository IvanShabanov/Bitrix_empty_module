<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

global $USER;


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

// получить массив сайтов [lid => name, ...]
$res = \Bitrix\Main\SiteTable::getList();
$siteIds = [];
while ($site = $res->fetch()) {
	$siteIds[$site["LID"]] = $site["NAME"];
}

$currentUrl = $APPLICATION->GetCurPage() . '?mid=' . urlencode($mid) . '&amp;lang=' . LANGUAGE_ID;
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$doc_root = \Bitrix\Main\Application::getDocumentRoot();
$url_module = str_replace($doc_root, '', __DIR__);

$options_list = $arModuleCfg['options_list'];

$ok_message = '';
$eeror_message = '';

function checkOption(string $option_name, $option)
{
	/* Тут проверяем значение настроек, если есть ошибка, то возвращаем ее текст, иначе вернем true */
	return true;
}


$saveOption = false;

if (check_bitrix_sessid()) {
	$save = $request->getpost('save');
	if ($save == 'save') {
		$saveOption = true;
	}
}


foreach ($siteIds as $sId => $sName) {


	$setDefault = false;

	$isConfigurated = \Bitrix\Main\Config\Option::get($arModuleCfg['MODULE_ID'], 'IS_CONFIGURATED_' . $sId);
	if ($isConfigurated != 'Y') {

		\Bitrix\Main\Config\Option::set($arModuleCfg['MODULE_ID'], 'IS_CONFIGURATED_' . $sId, 'Y');
		$setDefault = true;
	}

	if (check_bitrix_sessid()) {
		if ($save == 'reset_' . $sId) {
			$setDefault = true;
		}
	}

	foreach ($options_list as $option_name => $arOption) {
		$option_name_def = $option_name;
		$option_name = $option_name . '_' . $sId;
		$optionIsValid = false;
		if ($saveOption) {

			if ($arOption['type'] == 'file') {
				$files = $request->getFile($option_name);
				if (!empty($files)) {
					$arr_file = [
						"name" => $files['name'],
						"size" => $files['size'],
						"tmp_name" => $files['tmp_name'],
						"type" => "",
						"old_file" => \Bitrix\Main\Config\Option::get($arModuleCfg['MODULE_ID'], $option_name),
						"del" => "Y",
						"MODULE_ID" => $arModuleCfg['MODULE_ID']
					];

					$fid = CFile::SaveFile($arr_file, $arModuleCfg['MODULE_ID']);
					if ($fid > 0) {
						$option[$option_name] = $fid;
						$optionIsValid = checkOption($option_name_def, $option[$option_name]);
					} else {
						$optionIsValid = 'File not loaded';
					}
					if ($optionIsValid !== true) {
						$eeror_message .= 'ERROR: ' . Loc::getMessage('ISPRO_module_name_' . $option_name_def) . ' ' . $optionIsValid . PHP_EOL;
					}
				}
				if (!empty($files)) {
					$tmp_name = $files["tmp_name"];
					if ($tmp_name != '') {
						$extension = explode(".", basename($files["name"]));
						$extension = end($extension);
						@mkdir($doc_root . '/upload/' . $arModuleCfg['MODULE_ID']);
						$filename = $doc_root . '/upload/' . $arModuleCfg['MODULE_ID'] . '/' . uniqid() . '.' . $extension;
						$isloaded = true;
						if (!move_uploaded_file($tmp_name, $filename)) {
							if (!copy($tmp_name, $filename)) {
								$isloaded = false;
							}
						}
						if ($isloaded) {
							$option[$option_name] = $filename;
							$optionIsValid = checkOption($option_name_def, $option[$option_name]);
						} else {
							$optionIsValid = 'File not loaded';
						};
						if ($optionIsValid !== true) {
							$eeror_message .= 'ERROR: ' . Loc::getMessage('ISPRO_module_name_' . $option_name_def) . ' ' . $optionIsValid . PHP_EOL;
						}
					}
				}
			} else {
				$option[$option_name] = $request->getpost('option_' . $option_name);
				$ok_message .= 'Пришло Значение: option_' . $option_name . ':' . $option[$option_name] . PHP_EOL;
				$optionIsValid = checkOption($option_name_def, $option[$option_name]);
				if ($optionIsValid !== true) {
					$eeror_message .= 'ERROR: ' . Loc::getMessage('ISPRO_module_name_' . $option_name_def) . ' ' . $optionIsValid . PHP_EOL;
				}
				if (is_array($option[$option_name])) {
					$option[$option_name] = json_encode($option[$option_name]);
				};
			}
		} elseif ($setDefault) {
			$option[$option_name] = $arOption['default'];
			$optionIsValid = true;
		};
		if (($saveOption || $setDefault) && ($optionIsValid === true)) {
			\Bitrix\Main\Config\Option::set($arModuleCfg['MODULE_ID'], $option_name, $option[$option_name]);
			$ok_message .= 'SAVED: ' . Loc::getMessage('ISPRO_module_name_' . $option_name_def) . PHP_EOL;
		};

		$option[$option_name] = \Bitrix\Main\Config\Option::get($arModuleCfg['MODULE_ID'], $option_name);
		if ($option_type == 'json') {
			$option[$option_name . '_VALUE'] = @json_decode($option[$option_name], true);
		};
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

$tabList = [];
$tabList[] = [
	'DIV' => 'description',
	'TAB' => Loc::getMessage('ISPRO_module_name_TAB_SET_DESC'),
	'ICON' => 'ib_settings',
	'TITLE' => Loc::getMessage('ISPRO_module_name_TAB_TITLE_DESC')
];

foreach ($siteIds as $sId => $sName) {
	$tabList[] = [
		'DIV' => 'setting' . $sId,
		'TAB' => Loc::getMessage('ISPRO_module_name_TAB_SET_OPTION') . ' (' . $sName . ')',
		'ICON' => 'ib_settings',
		'TITLE' => Loc::getMessage('ISPRO_module_name_TAB_TITLE_OPTION') . ' (' . $sName . ')'
	];
}

$tabControl = new CAdminTabControl(str_replace('.', '_', $arModuleCfg['MODULE_ID']) . '_options', $tabList);
?>
<style>
	#module_name_form textarea {
		width: 100%;
		min-height: 150px;
	}
</style>
<form method="POST" action="<?= $currentUrl; ?>" enctype="multipart/form-data" id="module_name_form">
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
			<?= Loc::getMessage('ISPRO_module_name_DESCRIPTION'); ?>
			<?= EndNote(); ?>
		</td>
	</tr>


	<? foreach ($siteIds as $sId => $sName) : ?>
		<?
		$tabControl->BeginNextTab();
		?>

		<? foreach ($options_list as $option_name => $arOption) : ?>
			<? $option_name_def = $option_name; ?>
			<? $option_name = $option_name . '_' . $sId; ?>
			<tr>
				<td width="20%" valign="top">
					<?= Loc::getMessage('ISPRO_module_name_' . $option_name_def) ?>
				</td>

				<td width="80%">
					<? if ($arOption['type'] == 'textarea') : ?>
						<textarea name="option_<?= $option_name ?>"><?= HtmlFilter::encode($option[$option_name]) ?></textarea>
					<? elseif ($arOption['type'] == 'checkbox') : ?>
						<input type="hidden" name="option_<?= $option_name ?>" value="N" />
						<input type="checkbox" name="option_<?= $option_name ?>" value="Y" <?= ($option[$option_name] == "Y") ? 'checked="checked"' : '' ?> />
					<? elseif ($arOption['type'] == 'select') : ?>
						<select name="option_<?= $option_name ?>">
							<? foreach ($arOption['values'] as $value) : ?>
								<option value="<?= $value ?>" <?= ($option[$option_name] == $value) ? 'selected' : '' ?>>
									<?= Loc::getMessage('ISPRO_module_name_' . $option_name_def . '_' . $value) ?>
								</option>
							<? endforeach ?>
						</select>
					<? elseif ($arOption['type'] == 'file') : ?>
						<?
						$fid = \Bitrix\Main\Config\Option::get($arModuleCfg['MODULE_ID'], $option_name);
						echo CFile::InputFile(
							$option_name,
							20,
							$fid,
							'/upload/'.$arModuleCfg['MODULE_ID'], '/',
							0,
							$arOption['ext'],
							"",
							0,
							"class=typeinput",
							"",
							false,
							true
						)
						?>
					<? else : ?>
						<input type="<?= $arOption['type'] ?>" name="option_<?= $option_name ?>" value="<?= HtmlFilter::encode($option[$option_name]) ?>" />
					<? endif ?>
				</td>
			</tr>
		<? endforeach ?>
		<tr>
			<td colspan="2">
				<button type="submit" class="adm-btn adm-btn-save" name="save" value="reset_<?= $sId ?>"><? echo Loc::getMessage('ISPRO_module_name_RESET'); ?> (<?=$sName?>)</button>
			</td>
		</tr>

	<? endforeach ?>

	<? $tabControl->Buttons(); ?>

		<button type="submit" class="adm-btn adm-btn-save" name="save" value="save"><? echo Loc::getMessage('ISPRO_module_name_SAVE'); ?></button>


	<? $tabControl->End(); ?>
</form>