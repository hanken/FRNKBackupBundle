<?php

namespace FRNK\BackupBundle\Tests;

//use FRNK\BackupBundle\BackupManager\BackupManager;
//use FRNK\BackupBundle\BackupBackend\BackupBackendInterface;


class BackupManagerTest extends \PHPUnit_Framework_TestCase{
    
    
    
    
    
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
                ->with($this->equalTo( sprintf('added task %s', $testTaskName)))
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
    
    public function testRestoreBackup(){
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
   
    
    private function getBackupManager(){
        $backend = $this->getMock('FRNK\BackupBundle\BackupBackend\FilesystemBackupBackend', array(), array('/tmp'), '', false);
        
        
                
        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface', array(), array(), '' , false);
        $manager = new \FRNK\BackupBundle\BackupManager\BackupManager($backend, $logger);
        
        return array($manager, $backend, $logger);
        
    }
    
}

?>
