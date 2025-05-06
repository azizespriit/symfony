<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427143303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE blog
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations CHANGE NomP NomP VARCHAR(20) NOT NULL, CHANGE PrenomP PrenomP VARCHAR(20) NOT NULL, CHANGE codeConfirmation code_confirmation INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE blog (id_blog INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, titre VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, auteur VARCHAR(100) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, image VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, contenu TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, PRIMARY KEY(id_blog)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations CHANGE NomP NomP VARCHAR(255) NOT NULL, CHANGE PrenomP PrenomP VARCHAR(255) NOT NULL, CHANGE code_confirmation codeConfirmation INT NOT NULL
        SQL);
    }
}
