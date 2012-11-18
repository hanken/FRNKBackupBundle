<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * BackupManager
 *
 * @author franco
 */

namespace FRNK\BackupBundle\BackupManager;

use FRNK\BackupBundle\BackupManager\BackupManagerInterface;
use FRNK\BackupBundle\BackupBackend\BackupBackendInterface;
use FRNK\BackupBundle\BackupTask\BackupTaskInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupManager implements BackupManagerInterface {

    protected $backend;
    protected $backupTasks = array();
    protected $totalStoredBackups = 0;
    private $logger;
    private $outputWriter = false;

    /**
     * Constructor for BackupManager
     * @param \FRNK\BackupBundle\BackupBackend\BackupBackendInterface $backend
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    function __construct(BackupBackendInterface $backend, LoggerInterface $logger = null) {
        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = false;
        }

        $this->backend = $backend;
    }

    /**
     * {@inheritDoc}
     */
    public function addBackupTask(BackupTaskInterface $backupTask, $name, $namespace = false) {
        if (!$namespace) {
            $namespace = strtolower($name);
        }
        if (!isset($this->backupTasks[strtolower($name)])) {
            $this->backupTasks [strtolower($name)] = array(
                'task' => $backupTask,
                'options' => array(
                    'namespace' => $namespace,
                    'name' => $name)
            );
            $this->log('debug', sprintf('added task %s', $name));
        } else {
            throw new \InvalidArgumentException(sprintf('Duplicated taskname %s.', $name));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveBackup($backupName) {
        return $this->backend->retrieveBackup($backupName);
    }


    /**
     * {@inheritDoc}
     */
    public function restoreBackup($backupName, $force = false) {
        $info = $this->backend->getBackupInfo($backupName);
        if (is_array($info)) {
            if (!isset($info['namespace'])) {
                throw new \RuntimeException('BackupBackend returned invalid backup info');
            }

            $task = $this->findBackupTaskByNamespace($info['namespace']);
            if (!$task) {
                throw new \RuntimeException(sprintf('No task with namespace %s registered.', $info['namespace']));
            }

            $content = $this->retrieveBackup($backupName);

            if (!$task->restore($info, $content, $force)) {
                throw new \RuntimeException(sprintf('Failed to restore %s.', $backupName));
            }

            $this->writeAndLog('info', sprintf('Successfuly restored %s', $backupName));
            return;
        }

        throw new \InvalidArgumentException(sprintf('Invalid backup name %s.', $backupName));
    }

    /**
     * {@inheritDoc}
     */
    public function registerOutputWriter(OutputInterface $output) {
        $this->outputWriter = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function run() {
        $this->totalStoredBackups = 0;
        foreach ($this->backupTasks as $task) {
            $this->runTask($task['task'], $task['options']);
        }
        $this->writeAndLog('info', sprintf('Finished (%s backups written).', $this->totalStoredBackups));
    }

    /**
     * {@inheritDoc}
     */
    public function runBackupsByTask($taskName) {
        if (!isset($this->backupTasks[strtolower($taskName)])) {
            throw new \InvalidArgumentException(sprintf('No such task %s.', $taskName));
        }

        $this->totalStoredBackups = 0;
        $this->writeAndLog('debug', sprintf('Running backup task: %s', $taskName));
        $this->runTask($this->backupTasks[strtolower($taskName)]['task'], $this->backupTasks[strtolower($taskName)]['options']);

        $this->writeAndLog('info', sprintf('Finished (%s backups written)', $this->totalStoredBackups));
    }

    /**
     * {@inheritDoc}
     */
    public function listBackupsByTask($taskName) {
        if (!isset($this->backupTasks[strtolower($taskName)])) {
            throw new \InvalidArgumentException(sprintf('No such task %s.', $taskName));
        }
        $this->printBackups($this->backend->listBackups($this->backupTasks[strtolower($taskName)]['options']['namespace']));
    }

    /**
     * {@inheritDoc}
     */
    public function listBackups() {
        $this->printBackups($this->backend->listBackups());
    }

    /**
     * finds a task by a namespace. 
     * 
     * @param string $namespace
     * @return the found task or false if not found. 
     */
    protected function findBackupTaskByNamespace($namespace) {
        foreach ($this->backupTasks as $task) {
            if ($task['options']['namespace'] === $namespace) {
                return $task['task'];
            }
        }
        return false;
    }

    protected function printBackups(array $backups) {
        foreach ($backups as $backup) {
            $this->printBackup($backup);
        }
    }

    protected function printBackup($backup) {
        $this->write($backup);
    }

    /**
     * collects backups from one task and stores them to the backend. 
     * @param \FRNK\BackupBundle\BackupTask\BackupTaskInterface $task
     * @param array $options
     */
    protected function runTask(BackupTaskInterface $task, array $options) {
        $this->writeAndLog('debug', sprintf('Collecting backups from %s.', $options['name']));

        $backups = $task->collect();
        if (!is_array($backups)) {
            $backups = array($backups);
        }

        $backupCount = count($backups);
        $this->writeAndLog('debug', sprintf('   Got %s backups from %s.', $backupCount, $options['name']));

        for ($i = 0; $i < $backupCount; $i++) {
            $backup = $backups[$i];
            if (!isset($backup['data'])) {
                $backup['data'] = $backup;
            }
            if (!isset($backup['name'])) {
                $backup['name'] = $name . "." . $i;
            }


            $this->writeAndLog('debug', sprintf('   Storing backup %s of %s from %s.', $i + 1, $backupCount, $options['name']));
            $this->backend->store($options['namespace'], $backup['name'], $backup['data']);
            $this->totalStoredBackups++;
        }
    }

    /**
     * logs a message if a logger is present
     * @param string $level
     * @param string $msg
     */
    private function log($level, $msg) {
        if ($this->logger) {
            $this->logger->{$level}(trim($msg));
        }
    }

    /**
     * writes to the OutputInterfce set via registerOutputWriter()
     * @param mixed $msg
     */
    private function write($msg) {
        if ($this->outputWriter) {
            $this->outputWriter->writeln($msg);
        }
    }

    /**
     * helper for $this->write and $this->log that simply does both. 
     * @param type $level
     * @param type $msg
     */
    private function writeAndLog($level, $msg) {
        $this->log($level, $msg);
        $this->write($msg);
    }

}

?>
