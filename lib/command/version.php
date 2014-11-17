<?

namespace UniPlug\CLI\Command;

use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Version extends \Psy\Command\Command {
	private $arModules;

	protected function getModules() {
		$arModules = array(
			"main"     => array(),
			"partners" => array(),
			"system"   => array()
		);
		$folders = array(
			"/local/modules",
			"/bitrix/modules",
		);
		foreach ($folders as $folder) {
			$handle = @opendir($_SERVER["DOCUMENT_ROOT"] . $folder);
			if ( $handle ) {
				while (false !== ($dir = readdir($handle))) {
					if ( is_dir($_SERVER["DOCUMENT_ROOT"] . $folder . "/" . $dir) && $dir != "." && $dir != ".." ) {
						if ( $dir === "main" ) {
							$arModule = &$arModules["main"];
						} elseif ( strpos($dir, ".") ) {
							$arModule = &$arModules["partners"];
						} else {
							$arModule = &$arModules["system"];
						}
						$module_dir = $_SERVER["DOCUMENT_ROOT"] . $folder . "/" . $dir;
						if ( $info = \CModule::CreateModuleObject($dir) ) {
							$arModule[$dir]["MODULE_ID"] = $info->MODULE_ID;
							$arModule[$dir]["MODULE_NAME"] = $info->MODULE_NAME;
							$arModule[$dir]["MODULE_DESCRIPTION"] = $info->MODULE_DESCRIPTION;
							$arModule[$dir]["MODULE_VERSION"] = $info->MODULE_VERSION;
							$arModule[$dir]["MODULE_VERSION_DATE"] = $info->MODULE_VERSION_DATE;
							$arModule[$dir]["MODULE_SORT"] = $info->MODULE_SORT;
							$arModule[$dir]["MODULE_PARTNER"] = (strpos($dir, ".") !== false) ? $info->PARTNER_NAME : "";
							$arModule[$dir]["MODULE_PARTNER_URI"] = (strpos($dir, ".") !== false) ? $info->PARTNER_URI : "";
							$arModule[$dir]["IsInstalled"] = $info->IsInstalled();
						}
					}
				}
				closedir($handle);
			}
		}
		uasort($arModules["system"], create_function('$a, $b', 'if($a["MODULE_SORT"] == $b["MODULE_SORT"]) return strcasecmp($a["MODULE_NAME"], $b["MODULE_NAME"]); return ($a["MODULE_SORT"] < $b["MODULE_SORT"])? -1 : 1;'));
		uasort($arModules["partners"], create_function('$a, $b', 'if($a["MODULE_SORT"] == $b["MODULE_SORT"]) return strcasecmp($a["MODULE_NAME"], $b["MODULE_NAME"]); return ($a["MODULE_SORT"] < $b["MODULE_SORT"])? -1 : 1;'));

		$this->arModules = $arModules;
	}

	protected function configure() {
		$this
			->setName('version')
			->setAliases(array('v'))
			->setDefinition(
				array(
					new InputArgument('name', InputArgument::OPTIONAL, 'The module type or module name', NULL),
				)
			)
			->setDescription('Show a list of modules version. Type `version [module]` for information about [module].')
			->setHelp('');
	}

	protected function getSystem($name = false) {
		if ( !empty($name) ) {
			$arModule = $this->arModules["system"][$name];
			return array(
				sprintf('<info>%s</info>', $arModule["MODULE_ID"]),
				$arModule["MODULE_VERSION"],
			);
		}

		$arModules = array();

		foreach ($this->arModules["system"] as $name => $arModule) {
			$arModules[] = array(
					sprintf('<info>%s</info>', $arModule["MODULE_ID"]),
					$arModule["MODULE_VERSION"],
				);
		}

		return $arModules;
	}

	protected function getPartners($name = false) {
		if ( !empty($name) ) {
			$arModule = $this->arModules["partners"][$name];
			return array(
				sprintf('<info>%s</info>', $arModule["MODULE_ID"]),
				$arModule["MODULE_VERSION"],
			);
		}

		$arModules = array();

		foreach ($this->arModules["partners"] as $name => $arModule) {
			$arModules[] = array(
				sprintf('<info>%s</info>', $arModule["MODULE_ID"]),
				$arModule["MODULE_VERSION"],
			);
		}

		return $arModules;
	}

	protected function getMain() {
		return array(array(
			sprintf('<info>%s</info>', $this->arModules["main"]["main"]["MODULE_ID"]),
			$this->arModules["main"]["main"]["MODULE_VERSION"],
		));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->getModules();

		$name = $input->getArgument('name');

		$table = $this->getApplication()->getHelperSet()->get('table')
			->setRows(array())
			->setHeaders(array("module", "varsion"));

		if ( empty($name) ) {
			foreach ($this->getMain() as $arModule) {
				$table->addRow($arModule);
			}
		} else
		if ( $name == "all"  ) {

			foreach ($this->getMain() as $arModule) {
				$table->addRow($arModule);
			}

			foreach ($this->getSystem() as $arModule) {
				$table->addRow($arModule);
			}

			foreach ($this->getPartners() as $arModule) {
				$table->addRow($arModule);
			}
		} else
		if ( $name == "system" ) {
			foreach ($this->getSystem() as $arModule) {
				$table->addRow($arModule);
			}
		} else
		if ( $name == "partners" ) {
			foreach ($this->getPartners() as $arModule) {
				$table->addRow($arModule);
			}
		} else {
			if ( array_key_exists($name, $this->arModules["main"]) ) {
				foreach ($this->getMain() as $arModule) {
					$table->addRow($arModule);
				}
			} elseif ( array_key_exists($name, $this->arModules["system"]) ) {
				$table->addRow($this->getSystem($name));
			} elseif ( array_key_exists($name, $this->arModules["partners"]) ) {
				$table->addRow($this->getPartners($name));
			} else {
				$table->addRow(array("not found"));
			}
		}

		$output->page(
			function ($output) use ($table) {
				$table->render($output);
			}
		);

	}
}
