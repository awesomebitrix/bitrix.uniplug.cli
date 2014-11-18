<?

namespace UniPlug\CLI\Command;

use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class siteLock extends \Psy\Command\Command {

	protected function configure() {
		$this
			->setName('site_lock')
			->setAliases(array('lock'))
			->setDefinition(
				array(
					new InputArgument('action', InputArgument::OPTIONAL, 'status (default) / lock / unlock / switch', NULL),
				)
			)
			->setDescription('Lock or unlock public part.')
			->setHelp('');
	}

	protected function showStatus(OutputInterface $output) {
		$output->writeln(sprintf('status: <info>%s</info>', \COption::GetOptionString("main", "site_stopped", "N") == "N" ? "unlocked" : "locked"));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$action = $input->getArgument('action');

		switch ( $action ) {
			case "switch":
				$locked = \COption::GetOptionString("main", "site_stopped", "N") == "Y";
				if ( $locked ) {
					\COption::SetOptionString("main", "site_stopped", "N");
				} else {
					\COption::SetOptionString("main", "site_stopped", "Y");
				}
				break;
			case "unlock":
				\COption::SetOptionString("main", "site_stopped", "N");
				break;
			case "lock":
				\COption::SetOptionString("main", "site_stopped", "Y");
				break;
			case "status":
				break;
			default:
				$output->writeln(sprintf('<error>%s</error>', "Undefined argument"));
		}
		$this->showStatus($output);
	}
}
