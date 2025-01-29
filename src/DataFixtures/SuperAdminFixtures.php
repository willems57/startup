<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un super administrateur
        $superAdmin = new User();
        $superAdmin->setFirstName('Admin');
        $superAdmin->setLastName('Jose');
        $superAdmin->setEmail('Jose@ecoride.com');
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'Ecoride@2025'));
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setCredits(1000); // Exemple de crédits initiaux

        // Persister l'utilisateur
        $manager->persist($superAdmin);

        // Ajouter des utilisateurs normaux pour tester
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setFirstName("User$i");
            $user->setLastName("LastName$i");
            $user->setEmail("user$i@example.com");
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password@123'));
            $user->setRoles(['ROLE_USER']);
            $user->setCredits(20); // Donne des crédits aléatoires

            $manager->persist($user);
        }

        // Enregistrer dans la base de données
        $manager->flush();
    }
}
