<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\FinanceApiClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:refresh-stock-profile',
    description: 'Add a short description for your command',
)]
class RefreshStockProfileCommand extends Command
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var FinanceApiClientInterface */
    private FinanceApiClientInterface $financeApiClient;

    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager, 
        FinanceApiClientInterface $financeApiClient,
        SerializerInterface $serializer,
        LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->financeApiClient = $financeApiClient;
        $this->serializer = $serializer;
        parent::__construct();
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol')
            ->addArgument('region', InputArgument::REQUIRED, 'The region of the company')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // 1. Ping Yahoo API and grab the response
            $stockProfile = $this->financeApiClient->fetchStockProfile(
                $input->getArgument('symbol'),
                $input->getArgument('region')
            );

            // Handle non 200 status code responses
            if ($stockProfile->getStatusCode() !== 200) {
                $output->writeln($stockProfile->getContent());

                return Command::FAILURE;
            }

            // attempt to find a record in the DB using the $stockProfile symbol
            $symbol = json_decode($stockProfile->getContent())->symbol ?? null;

            $stock = $this->entityManager->getRepository(Stock::class)->findOneBy(['symbol' => $symbol]);

            if ($stock) {
                /** @var Stock $stock */
                $stock = $this->serializer->deserialize(
                    $stockProfile->getContent(),
                    Stock::class,
                    'json',
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $stock]
                );
            } else {
                /** @var Stock $stock */
                $stock = $this->serializer->deserialize(
                    $stockProfile->getContent(),
                    Stock::class,
                    'json'
                );
            }

            $this->entityManager->persist($stock);

            $this->entityManager->flush();

            $output->writeln($stock->getShortName() . ' has been saved / updated');

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $this->logger->warning(
                get_class($exception) . ': '
                . $exception->getMessage() . ' in '
                . $exception->getFile() . ' on line '
                . $exception->getLine() . ' using [symbol/region]'
                . '[' . $input->getArgument('symbol') . '/'
                . $input->getArgument('region') . ']'
            );

            return Command::FAILURE;
        }
    }
}
