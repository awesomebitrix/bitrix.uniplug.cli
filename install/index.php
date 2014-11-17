<?
IncludeModuleLangFile(__FILE__);

if (class_exists('uniplug_cli')) {
	return;
}

Class uniplug_cli extends CModule {

	const MODULE_ID = "uniplug.cli";
	var $MODULE_ID = "uniplug.cli";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "N";

	function __construct() {
		$arModuleVersion = array();

		include(dirname(__FILE__) . "/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("UNIPLUG_CLI_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("UNIPLUG_CLI_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("UNIPLUG_CLI_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("UNIPLUG_CLI_PARTNER_URI");

	}

	public function InstallFiles() {
		CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		chmod($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/cli.php', 0750);

		return true;
	}

	public function UnInstallFiles() {
		DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');

		return true;
	}

	public function DoInstall() {
		if ( !$this->InstallFiles() ) {

			return false;
		}

		RegisterModule(self::MODULE_ID);

		return true;
	}

	public function DoUninstall() {
		if ( !$this->UnInstallFiles() ) {

			return false;
		}

		UnRegisterModule(self::MODULE_ID);

		return true;
	}

}
