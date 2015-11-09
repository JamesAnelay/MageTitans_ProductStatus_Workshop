<?php

namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductStatusCommand extends Command
{

    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter){
        parent::__construct();
        $this->productStatusAdapter = $productStatusAdapter;
    }

    protected function configure()
    {
        $this->setName('catalog:product:status');
        $this->setDescription('Show status for products matching SKU');
        $this->addArgument('sku', InputArgument::REQUIRED, 'SKU to display the product status of');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $sku = $input->getArgument('sku');
            $matches = $this->productStatusAdapter->getProductStatusMatchingSku($sku);
            if(empty($matches)) {
                $output->writeln(
                    sprintf('<comment>No SKUs Matching "%s" found</comment>', $sku)
                );
            } else {
                foreach($matches as $matchedSku => $status){
                    $output->writeln(sprintf('<info>%s: %s</info>', $matchedSku, $status));
                }
            }
        } catch (ProductStatusAdapterException $e){
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}