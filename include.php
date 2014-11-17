<?

AddEventHandler('uniplug.cli', "OnCommandListBuild", "OnCommandListBuildHH");

function OnCommandListBuildHH(&$arCommands) {
	$arCommands[] = new UniPlug\CLI\Command\Version();
}
