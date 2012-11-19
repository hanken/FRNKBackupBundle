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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('frnk_backup');

        $rootNode->children()
                ->arrayNode('filesystem_backend')
                    ->children()
                        ->scalarNode('path')->defaultValue('')->end()
                    ->end()
                ->end()
                ->scalarNode("manager")->defaultValue('frnk_backup.default_manager')->end()
                ->scalarNode("storage")->defaultValue('frnk_backup.filesystem_storage')->end()
                ->arrayNode('backup_tasks')
                    ->children()
                        ->arrayNode('folder_backup')
                            ->children()
                                ->arrayNode('paths')->requiresAtLeastOneElement()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('tmp_dir')->defaultValue('/tmp')->end()
                                ->booleanNode('include_hidden_files')->defaultFalse()->end()
                                ->booleanNode('include_vcs_folders')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
        ->end();
               
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
