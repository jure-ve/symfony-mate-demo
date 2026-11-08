<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Crea 20 categorías con 10 productos cada una')]
class SeedCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        for ($c = 1; $c <= 20; ++$c) {
            $category = new Category("Categoría $c");
            $this->em->persist($category);
            for ($p = 1; $p <= 10; ++$p) {
                $this->em->persist(new Product("Producto $c-$p", $p * 9.99, $category));
            }
        }
        $this->em->flush();
        $output->writeln('Listo: 20 categorías y 200 productos.');

        return Command::SUCCESS;
    }
}
