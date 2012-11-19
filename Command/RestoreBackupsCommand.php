<?php 
 /*
 * @Copyright Copyright(c) 2012, franco fallica <franco.fallica@gmail.com>
 *
 * This file is part of the FRNKBackupBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace FRNK\BackupBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class RestoreBackupsCommand extends ContainerAwareCommand {

    /**
     * @see Command
     */
    protected function configure() {
        $this
                ->setName('frnk:backup:restore')
                ->setDescription('restores backups.')
                ->addArgument('backup', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'restore only a single backup')
                ->addOption('force', 0, InputOption::VALUE_NONE, 'force overwriting on restore')
                ->setHelp(<<<EOF
The <info>frnk:backup:restore</info> restores backups stored in the backend.

<info>php app/console frnk:backup:restore [backup] [--force]</info>

EOF
                )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $backup = $input->getArgument('backup');
        $force = $input->getOption('force');
        
        $manager = $this->getContainer()->get('frnk_backup.manager');
        $manager->registerOutputWriter($output);

        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog->askConfirmation(
                        $output, '<question>Are you sure you want to restore form backup?</question>', false
        )) {
            return;
        }

        $manager->restoreBackup($backup, $force);
    }

}

?>
