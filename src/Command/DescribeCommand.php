<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

final class DescribeCommand extends Command
{
    protected static $defaultName = 'app:describe';
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Returns the details of the application as json (name, version, build, etc.)')
            ->addOption(
                'pretty',
                null,
                InputOption::VALUE_NONE,
                'Prints the json response with JSON_PRETTY_PRINT.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keys = [
            'app_env',
            'app_vendor',
            'app_name',
            'app_version',
            'app_build',
            'app_deployment_id',
            'app_dev_branch',
            'system_mac_address',
            'cloud_provider',
            'cloud_region',
            'cloud_zone',
            'cloud_instance_id',
            'cloud_instance_type',
            'kernel.environment',
            'kernel.debug',
            'kernel.project_dir',
            'kernel.cache_dir',
            'kernel.config_dir',
            'kernel.logs_dir',
            'kernel.tmp_dir',
            'kernel.bundles',
        ];

        $data = [];
        $data['symfony_version'] = Kernel::VERSION;

        foreach ($keys as $k) {
            $data[str_replace('.', '_', $k)] = $this->container->getParameter($k);
        }

        $output->writeln(json_encode($data, $input->getOption('pretty') ? JSON_PRETTY_PRINT : 0));
        return 0;
    }
}
