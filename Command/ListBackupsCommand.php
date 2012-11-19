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


class ListBackupsCommand extends ContainerAwareCommand
{
    
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('frnk:backup:list')
            ->setDescription('lists backups.')
            ->addArgument('task', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'filter backups by task')
         //   ->addOption('message-limit', 0, InputOption::VALUE_OPTIONAL, 'The maximum number of messages to send.')
            ->setHelp(<<<EOF
The <info>frnk:backup:list</info> lists all backups stored in the backend.

<info>php app/console frnk:backup:list [task]</info>

EOF
            )
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $task = $input->getArgument('task');
        $manager = $this->getContainer()->get('frnk_backup.manager');
        $manager->registerOutputWriter($output);
        if(!isset($task)){
            $manager->listBackups();
        }else{
            $manager->listBackupsByTask($task);
        }
    }
}

?>
