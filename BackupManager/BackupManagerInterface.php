<?php 
 /*
 * @Copyright Copyright(c) 2012, franco fallica <franco.fallica@gmail.com>
 *
 * This file is part of the FRNKBackupBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace FRNK\BackupBundle\BackupManager;

use FRNK\BackupBundle\BackupTask\BackupTaskInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface BackupManagerInterface {
    
    /**
     * adds a BackupTask. (To be called through CompilerPass)
     * @param \FRNK\BackupBundle\BackupTask\BackupTaskInterface $backupTask
     * @param string $name
     * @param string $namespace
     * @throws \InvalidArgumentException
     */
    public function addBackupTask(BackupTaskInterface $backupTask, $name, $namespace = null);
    
    /**
     * registers a OutputInterface for logging to the console.
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function registerOutputWriter(OutputInterface $output);
    
    /**
     * runs all BackupTasks that have been registered through addBackupTask()
     */
    public function run();
    
    
    /**
     * runs one of the BackupTasks by it's name.
     * @param string $taskName
     * @throws \InvalidArgumentException
     */
    public function runBackupsByTask($taskName);
 
    /**
     * lists all backups stored in the backend filtered by $taskName
     * @param type $taskName
     */
    public function listBackupsByTask($taskName);
    
    /**
     * restores a backup from the backend. 
     * @param string $backupName
     * @param boolean $forece
     */
    public function restoreBackup($backupName, $forece = false);
   
    /**
     * retieves a single backup from the backend as string. 
     * @param string $backupName a name of a backup
     * @return string the content of the backup. 
     */
    public function retrieveBackup($backupName);
}

?>
