<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BackupTaskPass
 *
 * @author franco
 */

namespace FRNK\BackupBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class BackupTaskPass implements CompilerPassInterface {

    
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container) {

        if (false === $container->hasAlias('frnk_backup.manager')) {
            return;
        }

        $definition = $container->findDefinition('frnk_backup.manager');

        foreach ($container->findTaggedServiceIds('frnk_backup.backup_task') as $id => $attributes) {
            $name = false;
            $namespace = false;
            foreach ($attributes as $attribute) {
                if (isset($attribute["task_name"])) {
                    $name = $attribute["task_name"];
                }

                if (isset($attribute["namespace"])) {
                    $namespace = $attribute["namespace"];
                }
            }
            if (!($name)) {
                throw new \InvalidArgumentException(sprintf('Backup Taks service "%s" must have an task_name attribute.', $id));
            } else {
                $definition->addMethodCall('addBackupTask', array(new Reference($id), $name, $namespace));
            }
        }
    }

}

?>
