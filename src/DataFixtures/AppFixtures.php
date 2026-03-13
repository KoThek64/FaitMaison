<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Favorite;
use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private SluggerInterface $slugger,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        // Catégories
        $categoryNames = ['Entrée', 'Plat principal', 'Dessert', 'Soupe', 'Salade', 'Petit-déjeuner', 'Goûter', 'Apéritif', 'Boisson', 'Sauce'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $category->setSlug(strtolower($this->slugger->slug($name)));
            $category->setDescription($faker->sentence());
            $manager->persist($category);
            $categories[] = $category;
        }

        // Tags
        $tagNames = ['Végétarien', 'Vegan', 'Sans gluten', 'Rapide', 'Économique', 'Festif', 'Healthy', 'Épicé'];
        $tags = [];
        foreach ($tagNames as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $tag->setSlug(strtolower($this->slugger->slug($name)));
            $manager->persist($tag);
            $tags[] = $tag;
        }

        // Compte admin
        $admin = new User();
        $admin->setEmail('admin@faitmaison.fr');
        $admin->setUsername('admin');
        $admin->setPassword($this->hasher->hashPassword($admin, '123456789'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setBio("L'admin hehe");
        $admin->setIsVerified(true);
        $manager->persist($admin);

        $testUser = new User();
        $testUser->setEmail('test@mail.com');
        $testUser->setUsername('test');
        $testUser->setPassword($this->hasher->hashPassword($testUser, '123456789'));
        $testUser->setCreatedAt(new \DateTimeImmutable());
        $testUser->setIsVerified(true);
        $manager->persist($testUser);

        // Comptes non vérifiés
        foreach (['unverified1@mail.com', 'unverified2@mail.com', 'unverified3@mail.com'] as $i => $email) {
            $u = new User();
            $u->setEmail($email);
            $u->setUsername('unverified' . ($i + 1));
            $u->setPassword($this->hasher->hashPassword($u, 'password'));
            $u->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($u);
        }

        // Utilisateurs
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email());
            $user->setUsername($faker->unique()->userName());
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $user->setIsVerified(true);
            $manager->persist($user);
            $users[] = $user;
        }

        // Recettes
        $difficulties = ['facile', 'moyen', 'difficile'];
        $recipes = [];

        foreach ($users as $user) {
            $count = rand(3, 7);
            for ($i = 0; $i < $count; $i++) {
                $recipe = new Recipe();
                $recipe->setTitle(ucfirst($faker->words(rand(2, 4), true)));
                $recipe->setDescription($faker->paragraph(2));
                $recipe->setSteps($faker->paragraph(5));
                $recipe->setDuration(rand(10, 120));
                $recipe->setServings(rand(1, 8));
                $recipe->setDifficulty($difficulties[array_rand($difficulties)]);
                $recipe->setIsPublished((bool) rand(0, 1));
                $recipe->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
                $recipe->setCategory($categories[array_rand($categories)]);
                $recipe->setAuthor($user);

                // Tags aléatoires (0 à 3)
                $shuffledTags = $tags;
                shuffle($shuffledTags);
                foreach (array_slice($shuffledTags, 0, rand(0, 3)) as $tag) {
                    $recipe->addTag($tag);
                }

                $manager->persist($recipe);
                $recipes[] = $recipe;
            }
        }

        // Favoris et notes (sur les recettes publiées uniquement)
        $publishedRecipes = array_values(array_filter($recipes, fn($r) => $r->isPublished()));

        foreach ($users as $user) {
            $favoritedRecipes = [];
            $ratedRecipes = [];

            // Favoris (3 à 8)
            $shuffled = $publishedRecipes;
            shuffle($shuffled);
            foreach (array_slice($shuffled, 0, rand(3, 8)) as $recipe) {
                if ($recipe->getAuthor() === $user) continue;
                if (in_array($recipe, $favoritedRecipes, true)) continue;
                $favorite = new Favorite();
                $favorite->setUser($user);
                $favorite->setRecipe($recipe);
                $favorite->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($favorite);
                $favoritedRecipes[] = $recipe;
            }

            // Notes (3 à 8)
            $shuffled2 = $publishedRecipes;
            shuffle($shuffled2);
            foreach (array_slice($shuffled2, 0, rand(3, 8)) as $recipe) {
                if ($recipe->getAuthor() === $user) continue;
                if (in_array($recipe, $ratedRecipes, true)) continue;
                $rating = new Rating();
                $rating->setUser($user);
                $rating->setRecipe($recipe);
                $rating->setScore(rand(1, 5));
                $rating->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($rating);
                $ratedRecipes[] = $recipe;
            }
        }

        $manager->flush();
    }
}
