<?php

namespace YAMM\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('yamm:export')
            ->setDescription('Exporter for YAMM Configs')
            ->addArgument(
                'shop',
                InputArgument::OPTIONAL,
                'The shop ID of the shop you want to export your YAMM Config from'
            )->addArgument(
                'inheritFromParent',
                InputArgument::OPTIONAL,
                'Decides weather the generated config should be inherited from the parent shop id',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yamm = new \yamm_exporter((bool) $input->getArgument('inheritFromParent'));

        if ($input->getArgument('shop')) {
            $output->write($yamm->export($input->getArgument('shop')));
        } else {
            $output->writeln('Generating YAMM Configs for all Shops.');
            $shopIds = \oxRegistry::getConfig()->getShopIds();
            sort($shopIds);
            foreach ($shopIds as $shopId) {
                $output->writeln(sprintf('Generating YAMM Configs for Shop with ID %d', $shopId));
                $output->write($yamm->export($shopId));
            }
        }
    }
} 