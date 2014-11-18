<?

namespace UniPlug\CLI\Command;

use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class clearCache extends \Psy\Command\Command {

	protected function configure() {
		$this
			->setName('clear_cache')
			->setAliases(array('cc'))
			->setDescription('Clear all bitrix caches.')
			->setHelp('');
	}

	public static function FormatSize($size, $precision = 2)
	{
		static $a = array("b", "Kb", "Mb", "Gb", "Tb");
		$pos = 0;
		while($size >= 1024 && $pos < 4)
		{
			$size /= 1024;
			$pos++;
		}
		return round($size, $precision)." ".$a[$pos];
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/cache_files_cleaner.php");

		$_SESSION["CACHE_STAT"] = array(
			"errors" => 0,
		);

		$obCacheCleaner = new \CFileCacheCleaner("all");
		$obCacheCleaner->InitPath("");
		if(\Bitrix\Main\Data\Cache::getCacheEngineType() == "cacheenginefiles") {
			$obCacheCleaner->Start();
			while ($file = $obCacheCleaner->GetNextFile()) {
				if ( is_string($file) ) {
					$date_expire = $obCacheCleaner->GetFileExpiration($file);
					if ( $date_expire ) {
						$file_size = filesize($file);

						$_SESSION["CACHE_STAT"]["scanned"]++;
						$_SESSION["CACHE_STAT"]["space_total"]+=$file_size;

						if(@unlink($file))
						{
							$_SESSION["CACHE_STAT"]["deleted"]++;
							$_SESSION["CACHE_STAT"]["space_freed"]+=$file_size;
						}
						else
						{
							$_SESSION["CACHE_STAT"]["errors"]++;
						}
					}
				}
			}
		}

		BXClearCache(true);
		$GLOBALS["CACHE_MANAGER"]->CleanAll();
		$GLOBALS["stackCacheManager"]->CleanAll();
		$staticHtmlCache = \Bitrix\Main\Data\StaticHtmlCache::getInstance();
		$staticHtmlCache->deleteAll();

		$table = $this->getApplication()->getHelperSet()->get('table')
			->setRows(array());

		$table->addRow(array(
				sprintf('<info>%s</info>', "files scanned"),
				sprintf('<number>%s</number>', $_SESSION["CACHE_STAT"]["scanned"]),
			));

		$table->addRow(array(
				sprintf('<info>%s</info>', "files deleted"),
				sprintf('<number>%s</number>', $_SESSION["CACHE_STAT"]["deleted"]),
			));

		$table->addRow(array(
				sprintf('<info>%s</info>', "space freed"),
				sprintf('<number>%s</number>', $this->FormatSize($_SESSION["CACHE_STAT"]["space_freed"])),
			));


		$table->addRow(array(
				sprintf('<info>%s</info>', "errors"),
				sprintf('<number>%s</number>', $_SESSION["CACHE_STAT"]["errors"]),
			));

		$output->page(
			function ($output) use ($table) {
				$table->render($output);
			}
		);
	}

}
