<?

namespace UniPlug\CLI;

class Command {

	function OnCommandListBuildHandler(&$arCommands) {
		$arCommands[] = new \UniPlug\CLI\Command\Version();
		$arCommands[] = new \UniPlug\CLI\Command\siteLock();

		return true;
	}

}
