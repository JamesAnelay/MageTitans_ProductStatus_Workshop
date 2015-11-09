<?php

namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableProductCommand extends Command
{
    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter){
        parent::__construct();
        $this->productStatusAdapter = $productStatusAdapter;
    }

    protected function configure()
    {
        $this->setName('catalog:product:disable');
        $this->setDescription('Disable a product by SKU');
        $this->addArgument('sku', InputArgument::REQUIRED, 'SKU to disable');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $sku = $input->getArgument('sku');
            $this->productStatusAdapter->disableProductBySku($sku);
            $output->writeln(sprintf('<info>"%s" was successfully disabled</info>',$sku));
        } catch (ProductStatusAdapterException $e){
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}