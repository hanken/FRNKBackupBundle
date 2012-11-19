<?php 
 /*
 * @Copyright Copyright(c) 2012, franco fallica <franco.fallica@gmail.com>
 *
 * This file is part of the FRNKBackupBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace FRNK\BackupBundle\Tests;

//use FRNK\BackupBundle\BackupManager\BackupManager;
//use FRNK\BackupBundle\BackupBackend\BackupBackendInterface;


class BackupManagerTest extends \PHPUnit_Framework_TestCase {

    public function testAddBackupTask() {
        list ($manager, $backend, $logger) = $this->getBackupManager();

        $testTaskName = 'test_task';
        $task = $this->getMock('FRNK\BackupBundle\BackupTask\BackupTaskInterface');


//        $permissionMap
//            ->expects($this->once())
//            ->method('getMasks')
//            ->with($this->equalTo('VIEW'))
//            ->will($this->returnValue($masks = array(1, 2, 3)))
//        ;

        $logger->
                expects($this->once())
                ->method('debug')
                ->with($this->equalTo(sprintf('added task %s', $testTaskName)))
        ;


        $manager->addBackupTask($task, 'test_task', 'test_namespace');
    }

    public function testRetrieveBackup() {
        list ($manager, $backend, $logger) = $this->getBackupManager();



        $backupName = 'test_backup';
        $testContent = 'test_content';

        $backend->expects($this->once())
                ->method('retrieveBackup')
                ->with($this->equalTo($backupName))
                ->will($this->returnValue($testContent));

        $content = $manager->retrieveBackup($backupName);

        $this->assertEquals($content, $testContent);
    }

    public function testRestoreBackup() {
        list ($manager, $backend, $logger) = $this->getBackupManager();

        $testTaskName = 'test_task';
        $testTestNamespace = 'test_namespace';
        $task = $this->getMock('FRNK\BackupBundle\BackupTask\BackupTaskInterface');

        $backupName = 'test_backup';
        $testContent = 'test_content';

        $testInfo = array('namespace' => $testTestNamespace, 'name' => $backupName, 'date' => new \DateTime('now'));
        $backend->expects($this->once())
                ->method('getBackupInfo')
                ->with($this->equalTo($backupName))
                ->will($this->returnValue($testInfo));


        $backend->expects($this->once())
                ->method('retrieveBackup')
                ->with($this->equalTo($backupName))
                ->will($this->returnValue($testContent));

        $task->expects($this->once())
                ->method('restore')
                ->with($this->equalTo($testInfo), $this->equalTo($testContent), $this->equalTo(false))
                ->will($this->returnValue(true));

        $manager->addBackupTask($task, $testTaskName, $testTestNamespace);

        $manager->restoreBackup($backupName);
    }

    public function testRunBackup() {
        list ($manager, $backend, $logger) = $this->getBackupManager();

        $testTaskName = 'test_task';
        $testTestNamespace = 'test_namespace';
        $task = $this->getMock('FRNK\BackupBundle\BackupTask\BackupTaskInterface');

        $backupName = 'test_backup';
        $testContent = 'test_content';

        $task->expects($this->once())
                ->method('collect')
                ->with()
                ->will($this->returnValue(array(array('name' => $backupName, 'data' => $testContent))));

        $backend->expects($this->once())
                ->method('store')
                ->with($this->equalTo($testTestNamespace), $this->equalTo($backupName), $this->equalTo($testContent));

        $manager->addBackupTask($task, $testTaskName, $testTestNamespace);

        $manager->run();
    }

    private function getBackupManager() {
        $backend = $this->getMock('FRNK\BackupBundle\BackupBackend\FilesystemBackupBackend', array(), array('/tmp'), '', false);



        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface', array(), array(), '', false);
        $manager = new \FRNK\BackupBundle\BackupManager\BackupManager($backend, $logger);

        return array($manager, $backend, $logger);
    }

}

?>
