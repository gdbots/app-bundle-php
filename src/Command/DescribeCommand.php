<?php

namespace Gdbots\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;

class DescribeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:describe')
            ->setDescription('Returns the details of the application as json (name, version, build, etc.)')
            ->addOption('pretty', null, InputOption::VALUE_NONE, 'Prints the json response with JSON_PRETTY_PRINT.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $keys = [
            'app_vendor',
            'app_name',
            'app_version',
            'app_build',
            'app_dev_branch',
            'system_mac_address',
            'cloud_provider',
            'cloud_region',
            'cloud_zone',
            'cloud_instance_id',
            'cloud_instance_type',
            'is_production',
            'is_not_production',
            'kernel.environment',
            'kernel.debug',
            'kernel.bundles',
        ];

        $data = [];
        $data['symfony_version'] = Kernel::VERSION;

        foreach ($keys as $k) {
            $data[str_replace('.', '_', $k)] = $container->getParameter($k);
        }

        $output->writeln(json_encode($data, $input->getOption('pretty') ? JSON_PRETTY_PRINT : 0));
    }
}
