<?php 
 /*
 * @Copyright Copyright(c) 2012, franco fallica <franco.fallica@gmail.com>
 *
 * This file is part of the FRNKBackupBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace FRNK\BackupBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FRNKBackupExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        
        $container->setAlias('frnk_backup.manager', $config['manager']);
        $container->setAlias('frnk_backup.storage', $config['storage']);


        if (isset($config['backup_tasks'])){
           if (isset($config['backup_tasks']['folder_backup'])){
               $container->setParameter('frnk_backup.folder_backup.paths', $config['backup_tasks']['folder_backup']['paths']);
               $container->setParameter('frnk_backup.folder_backup.tmp_dir', $config['backup_tasks']['folder_backup']['tmp_dir']);
               $container->setParameter('frnk_backup.folder_backup.include_hidden_files', $config['backup_tasks']['folder_backup']['include_hidden_files']);
               $container->setParameter('frnk_backup.folder_backup.include_vcs_folders', $config['backup_tasks']['folder_backup']['include_vcs_folders']);
               $loader->load('tasks/folderbackup.xml');
           } 
        }
            
        if (isset($config['filesystem_backend'])){
            $container->setParameter('frnk_backup.filesystem_storage.path', $config['filesystem_backend']['path']);
            $loader->load('backend/filesystem.xml');
        }
    }
}
