<?php

namespace Ccovey\SymfonyRabbitMQBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();

        $root = $tree->root('ccovey_rabbitmq');

        $root
            ->children()
            ->booleanNode('debug')->defaultValue('%kernel.debug%')->end();

        $this->addConnection($root);
        $this->addQueues($root);
    }

    private function addConnection(ArrayNodeDefinition $root)
    {
        $root->fixXmlConfig('connection')
            ->children()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultValue(5672)->end()
                ->scalarNode('user')->defaultValue('guest')->end()
                ->scalarNode('password')->defaultValue('guest')->end()
                ->scalarNode('vhost')->defaultValue('/')->end()
                ->booleanNode('insist')->defaultFalse()->end()
                ->scalarNode('loginMethod')->defaultValue('AMQPlain')->end()
                ->scalarNode('connection_timeout')->defaultValue(3)->end()
                ->scalarNode('read_write_timeout')->defaultValue(3)->end()
                ->booleanNode('keepalive')->defaultFalse()->end()
                ->scalarNode('heartbeat')->defaultValue(0)->end()
            ->end()
        ->end();
    }

    private function addQueues(ArrayNodeDefinition $root)
    {
        $root->fixXmlConfig('queue')
            ->children()
            ->arrayNode('queues')
                ->useAttributeAsKey('key')
                ->canBeUnset()
                ->prototype('array')
                    ->children()
                        ->scalarNode('queue_name')->isRequired()->end()
                        ->scalarNode('exchange')->default('')->end()
                        // add arguments somehow
                        ->booleanNode('passive')->default(false)->end()
                        ->booleanNode('durable')->default(true)->end()
                        ->booleanNode('exclusive')->default(false)->end()
                        ->booleanNode('auto_delete')->default(false)->end()
                        ->scalarNode('ticket')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function addExchanges()
    {

    }
}
