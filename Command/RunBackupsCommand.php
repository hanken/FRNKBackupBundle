<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RunBackupCommand
 *
 * @author franco
 */

namespace FRNK\BackupBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class RunBackupsCommand extends ContainerAwareCommand
{
    
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('frnk:backup:run')
            ->setDescription('Collects backups and stores them to the backup store')
            ->addArgument('task', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'define which task to run')
         //   ->addOption('message-limit', 0, InputOption::VALUE_OPTIONAL, 'The maximum number of messages to send.')
            ->setHelp(<<<EOF
The <info>frnk:backup:run</info> command collects backups from all backup services and stores them to the backup store.

<info>php app/console frnk:backup:run [task]</info>

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
            $manager->run();
        }else{
            $manager->runBackupsByTask($task);
        }
        
        var_dump(memory_get_peak_usage());
    }
}

?>
