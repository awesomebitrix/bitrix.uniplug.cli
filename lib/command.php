<?

namespace UniPlug\CLI;

class Command {

	function OnCommandListBuildHandler(&$arCommands) {
		$arCommands[] = new \UniPlug\CLI\Command\Version();

		return true;
	}

}
