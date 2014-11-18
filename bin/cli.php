<?
if ( php_sapi_name() !== 'cli' ) {
	echo "cli use only";
	exit(1);
}

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);
set_time_limit(0);

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . '/../../../../');
require( $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/uniplug.cli/vendor/autoload.php');

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_after.php");

chdir( $_SERVER["DOCUMENT_ROOT"] . '/bitrix/tmp');

CModule::IncludeModule("uniplug.cli");

$arCommands = array();

foreach(GetModuleEvents("uniplug.cli", "OnCommandListBuild", true) as $arEvent) {
	$arCommand = array();
	if ( ExecuteModuleEventEx($arEvent, array(&$arCommand)) !== false ) {
		$arCommands = array_merge($arCommands, $arCommand);
	}
}

$config = array(
	"commands" => $arCommands,
);

$shell = new \Psy\Shell(new \Psy\Configuration($config));

$shell->run();
