<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260311093911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed initial categories and tags';
    }

    public function up(Schema $schema): void
    {
        $categories = [
            ['Entrée',          'entree'],
            ['Plat principal',  'plat-principal'],
            ['Dessert',         'dessert'],
            ['Soupe',           'soupe'],
            ['Salade',          'salade'],
            ['Petit-déjeuner',  'petit-dejeuner'],
            ['Goûter',          'gouter'],
            ['Apéritif',        'aperitif'],
            ['Boisson',         'boisson'],
            ['Sauce',           'sauce'],
        ];

        foreach ($categories as [$name, $slug]) {
            $this->addSql(
                "INSERT INTO category (name, slug) VALUES (?, ?)",
                [$name, $slug]
            );
        }

        $tags = [
            ['Végétarien',   'vegetarien'],
            ['Vegan',        'vegan'],
            ['Sans gluten',  'sans-gluten'],
            ['Rapide',       'rapide'],
            ['Économique',   'economique'],
            ['Festif',       'festif'],
            ['Healthy',      'healthy'],
            ['Épicé',        'epice'],
            ['Fait maison',  'fait-maison'],
            ['Traditionnel', 'traditionnel'],
        ];

        foreach ($tags as [$name, $slug]) {
            $this->addSql(
                "INSERT INTO tag (name, slug) VALUES (?, ?)",
                [$name, $slug]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $slugs = ['entree', 'plat-principal', 'dessert', 'soupe', 'salade',
                  'petit-dejeuner', 'gouter', 'aperitif', 'boisson', 'sauce'];
        foreach ($slugs as $slug) {
            $this->addSql("DELETE FROM category WHERE slug = ?", [$slug]);
        }

        $slugs = ['vegetarien', 'vegan', 'sans-gluten', 'rapide', 'economique',
                  'festif', 'healthy', 'epice', 'fait-maison', 'traditionnel'];
        foreach ($slugs as $slug) {
            $this->addSql("DELETE FROM tag WHERE slug = ?", [$slug]);
        }
    }
}
