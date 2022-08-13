<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {

	return;
}

echo (CAdminMessage::ShowNote(Loc::getMessage("ISPRO___MODULENAME___UNSTEP_BEFORE") . " " . Loc::getMessage("ISPRO___MODULENAME___UNSTEP_AFTER")));
?>

<form action="<? echo ($APPLICATION->GetCurPage()); ?>">
	<input type="hidden" name="lang" value="<? echo (LANG); ?>" />
	<input type="submit" value="<? echo (Loc::getMessage("ISPRO___MODULENAME___STEP_SUBMIT_BACK")); ?>">
</form>