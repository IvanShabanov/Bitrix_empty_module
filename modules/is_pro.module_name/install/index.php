<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
Loc::loadMessages(__FILE__);

Class is_pro___MODULENAME__ extends CModule
{
	public function __construct()
	{
		if(file_exists(__DIR__."/module.cfg.php")){
			include(__DIR__."/module.cfg.php");
		}
		if(file_exists(__DIR__."/version.php")){
			$arModuleVersion = array();
			include(__DIR__."/version.php");
			$this->MODULE_ID 		   = $arModuleCfg['MODULE_ID'];
			$this->MODULE_VERSION  	   = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
			$this->MODULE_NAME 		   = Loc::getMessage("ISPRO___MODULENAME___NAME");
			$this->MODULE_DESCRIPTION  = Loc::getMessage("ISPRO___MODULENAME___DESC");
			$this->PARTNER_NAME 	   = Loc::getMessage("ISPRO___MODULENAME___PARTNER_NAME");
			$this->PARTNER_URI  	   = Loc::getMessage("ISPRO___MODULENAME___PARTNER_URI");
		}
		return false;
	}


	public function DoInstall()
	{
		global $DB, $APPLICATION, $step;
		$this->InstallEvents();
		ModuleManager::registerModule($this->MODULE_ID);
		$this->SetDefaultOptions();
		return true;
	}

	public function DoUninstall()
	{
		global $DB, $APPLICATION, $step;
		$this->UnInstallEvents();
		$this->RemoveOptions();
		ModuleManager::unRegisterModule($this->MODULE_ID);
		return true;
	}


	public function InstallEvents()
	{
		/*
		RegisterModuleDependences("main", "OnProlog", $this->MODULE_ID,"IS_PRO\__MODULENAME__\Main", "OnProlog");
		RegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "IS_PRO\__MODULENAME__\Main", "OnEpilog");
		RegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "IS_PRO\__MODULENAME__\Main", "OnEndBufferContent");
		*/
		return false;
	}

	public function SetDefaultOptions()
	{
		include(__DIR__."/module.cfg.php");
		include(__DIR__ . "/../default_option.php");
		foreach ($options_list as $option_name => $option_type) {
			$option[$option_name] = $is_pro___MODULENAME___default_options[$option_name];
			\Bitrix\Main\Config\Option::set($arModuleCfg['MODULE_ID'], $option_name, $option[$option_name]);
		}
	}

	public function RemoveOptions()
	{
		include(__DIR__."/module.cfg.php");
		COption::RemoveOption($arModuleCfg['MODULE_ID'], "");
	}

	public function UnInstallEvents()
	{
		/*
		UnRegisterModuleDependences("main", "OnProlog", $this->MODULE_ID, "IS_PRO\__MODULENAME__\Main", "OnProlog");
		UnRegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "IS_PRO\__MODULENAME__\Main", "OnEpilog");
		UnRegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "IS_PRO\__MODULENAME__\Main", "OnEndBufferContent");
		*/
		return false;
	}

}
