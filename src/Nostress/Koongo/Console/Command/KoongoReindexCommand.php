<?php

namespace Nostress\Koongo\Console\Command;

use Magento\Catalog\Model\Indexer\Category\Flat as FlatCategory;
use Magento\Catalog\Model\Indexer\Product\Flat;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KoongoReindexCommand extends Command
{
    /** @var Flat  */
    private $flat;
    /** @var FlatCategory  */
    private $flatCategory;

    public function __construct(Flat $flat, FlatCategory $flatCategory)
    {
        parent::__construct();
        $this->flat = $flat;
        $this->flatCategory = $flatCategory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('koongo:flat:reindex');
        $this->setDescription('Reindex products for Koongo plugin');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start recomputing products!</info>');
        $this->flat->executeFull();
        $output->writeln('<info>Finished recomputing products!</info>');

        $output->writeln('<info>Start recomputing categories!</info>');
        $this->flatCategory->executeFull();
        $output->writeln('<info>Finished recomputing categories!</info>');
    }
}