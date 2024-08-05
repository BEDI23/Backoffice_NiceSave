<?php

namespace App\Command;

use App\Entity\SuperAdmin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-SuperAdmin',
    description: 'Creates a super admin with predefined credentials',
)]
class CreateSuperAdminCommand extends Command
{
    protected static $defaultName = 'app:create-superAdmin';

    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-superAdmin')
            ->setDescription('Creates a superAdmin user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $superAdmin = new SuperAdmin();
        $superAdmin->setEmail('user@gmail.com');
        $superAdmin->setRoles(['SUPER_ADMINISTRATEUR']);
        $superAdmin->setFirstname('Super');
        $superAdmin->setLastname('Administrateur');
        $superAdmin->setPassword(
            $this->passwordHasher->hashPassword($superAdmin, 'user')
        );
        $superAdmin->setDateCreate();
        $this->entityManager->persist($superAdmin);
        $this->entityManager->flush();

        $io->success('SuperAdmin user has been created successfully.');

        return Command::SUCCESS;
    }
}
