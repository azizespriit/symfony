<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250412164924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX speciality ON admin
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin CHANGE id_admin id_admin INT NOT NULL, CHANGE experience experience LONGTEXT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin ADD CONSTRAINT FK_880E0D76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_prestataire_user ON admin
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_880E0D76A76ED395 ON admin (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD created_at DATETIME NOT NULL, DROP CreatedAt, CHANGE ID id INT NOT NULL, CHANGE Content content VARCHAR(400) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF030F6DDC3 FOREIGN KEY (ServiceID) REFERENCES service (ID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF07BA1C7D1 FOREIGN KEY (OrganisateurID) REFERENCES client (id_client) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8F91ABF030F6DDC3 ON avis (ServiceID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8F91ABF07BA1C7D1 ON avis (OrganisateurID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD years_of_experience INT NOT NULL, ADD number_of_events_organized INT NOT NULL, DROP yearsOfExperience, DROP numberOfEventsOrganized, CHANGE id_client id_client INT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_organisateur_user ON client
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7440455A76ED395 ON client (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY commande_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE id id INT NOT NULL, CHANGE id_panier id_panier INT DEFAULT NULL, CHANGE date_commande date_commande DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id_panier ON commande
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67D2FBB81F ON commande (id_panier)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT commande_ibfk_1 FOREIGN KEY (id_panier) REFERENCES panier (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire CHANGE id id INT NOT NULL, CHANGE Id_pub Id_pub INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCC34CD1E9 FOREIGN KEY (Id_pub) REFERENCES publication (Id_pub) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67F068BCC34CD1E9 ON commentaire (Id_pub)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions MODIFY IdC INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON competitions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions ADD nb_part INT NOT NULL, DROP IdC, CHANGE NbPart id_c INT NOT NULL, CHANGE DateC date_c DATE NOT NULL, CHANGE LieuC lieu_c VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions ADD PRIMARY KEY (id_c)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP createdAt, DROP updatedAt, CHANGE id_prestataire id_prestataire INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F378B0A977 FOREIGN KEY (id_prestataire) REFERENCES admin (id_admin) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_equipement_prestataire ON equipement
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B8B4C6F378B0A977 ON equipement (id_prestataire)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE generatedcodes CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE jour CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objectif CHANGE id id INT NOT NULL, CHANGE lien lien VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier CHANGE id id INT NOT NULL, CHANGE prix_total prix_total DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier_produit DROP FOREIGN KEY panier_produit_ibfk_2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id_produit ON panier_produit
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D31F28A6F7384557 ON panier_produit (id_produit)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier_produit ADD CONSTRAINT panier_produit_ibfk_2 FOREIGN KEY (id_produit) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan DROP FOREIGN KEY ga
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan DROP FOREIGN KEY ga
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE id_plan id_plan INT NOT NULL, CHANGE id_obj id_obj INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DD8C23C36 FOREIGN KEY (id_obj) REFERENCES objectif (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX ga ON plan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DD5A5B7DD8C23C36 ON plan (id_obj)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan ADD CONSTRAINT ga FOREIGN KEY (id_obj) REFERENCES objectif (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE id id INT NOT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL, CHANGE description description LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publication CHANGE Id_pub id_pub INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE date_pub date_pub DATETIME NOT NULL, CHANGE imageUrl image_url VARCHAR(130) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX Id_pub ON reactions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions CHANGE id id INT NOT NULL, CHANGE type type VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3C34CD1E9 FOREIGN KEY (Id_pub) REFERENCES publication (Id_pub) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3C90EF3D7 FOREIGN KEY (Id_user) REFERENCES utilisateur (Id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_38737FB3C34CD1E9 ON reactions (Id_pub)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id_user ON reactions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_38737FB3C90EF3D7 ON reactions (Id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD created_at DATETIME NOT NULL, ADD update_at DATETIME NOT NULL, DROP createdAT, DROP updateAT, CHANGE id_reclamation id_reclamation INT NOT NULL, CHANGE id_organisateur id_organisateur INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064043F0033A2 FOREIGN KEY (id_service) REFERENCES service (ID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640468161836 FOREIGN KEY (id_organisateur) REFERENCES client (id_client) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064041D3E4624 FOREIGN KEY (id_equipement) REFERENCES equipement (id_equipement) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CE6064043F0033A2 ON reclamation (id_service)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CE60640468161836 ON reclamation (id_organisateur)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CE6064041D3E4624 ON reclamation (id_equipement)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CE606404F7384557 ON reclamation (id_produit)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse ADD created_at DATETIME NOT NULL, ADD update_at DATETIME NOT NULL, DROP createdAt, DROP updateAT, CHANGE id_reponse id_reponse INT NOT NULL, CHANGE id_reclamation id_reclamation INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7D672A9F3 FOREIGN KEY (id_reclamation) REFERENCES reclamation (id_reclamation) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5FB6DEC7D672A9F3 ON reponse (id_reclamation)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations MODIFY IdR INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX Etrangère ON reservations
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON reservations
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD id_r INT NOT NULL, ADD nom_p VARCHAR(255) NOT NULL, ADD prenom_p VARCHAR(255) NOT NULL, ADD mode_p VARCHAR(255) NOT NULL, ADD id_c INT NOT NULL, DROP IdR, DROP NomP, DROP PrenomP, DROP ModeP, DROP IdC, CHANGE DateR date_r DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD PRIMARY KEY (id_r)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD rate_count INT NOT NULL, ADD rate_sum DOUBLE PRECISION NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP RateCount, DROP RateSum, DROP CreatedAt, DROP UpdatedAt, CHANGE ID id INT NOT NULL, CHANGE Title title VARCHAR(20) NOT NULL, CHANGE Description description VARCHAR(255) NOT NULL, CHANGE Price price DOUBLE PRECISION NOT NULL, CHANGE Disponibility disponibility TINYINT(1) NOT NULL, CHANGE Type type VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2D3F17567 FOREIGN KEY (PrestataireID) REFERENCES admin (id_admin) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E19D9AD2D3F17567 ON service (PrestataireID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE typeevenement MODIFY id_typeEvenement INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON typeevenement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE typeevenement ADD id_type_evenement INT NOT NULL, ADD created_at DATETIME NOT NULL, ADD update_at DATETIME NOT NULL, DROP id_typeEvenement, DROP createdAt, DROP updateAt
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE typeevenement ADD PRIMARY KEY (id_type_evenement)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD last_name VARCHAR(255) NOT NULL, ADD first_name VARCHAR(255) NOT NULL, ADD is_locked TINYINT(1) NOT NULL, ADD failed_attempts INT NOT NULL, ADD lockout_time DATETIME NOT NULL, ADD created_at DATETIME NOT NULL, ADD update_at DATETIME NOT NULL, DROP lastName, DROP firstName, DROP isLocked, DROP failedAttempts, DROP lockoutTime, DROP createdAt, DROP updateAt, CHANGE id_user id_user INT NOT NULL, CHANGE roles roles VARCHAR(255) NOT NULL, CHANGE reset_token reset_token VARCHAR(255) NOT NULL, CHANGE token_expiry token_expiry DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur CHANGE Id_user id_user INT NOT NULL, CHANGE role role VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin DROP FOREIGN KEY FK_880E0D76A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin DROP FOREIGN KEY FK_880E0D76A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin CHANGE id_admin id_admin INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE experience experience TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX speciality ON admin (speciality)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_880e0d76a76ed395 ON admin
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_prestataire_user ON admin (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin ADD CONSTRAINT FK_880E0D76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF030F6DDC3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF07BA1C7D1
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8F91ABF030F6DDC3 ON avis
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8F91ABF07BA1C7D1 ON avis
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP created_at, CHANGE id ID INT AUTO_INCREMENT NOT NULL, CHANGE content Content VARCHAR(400) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP FOREIGN KEY FK_C7440455A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP FOREIGN KEY FK_C7440455A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD yearsOfExperience INT NOT NULL, ADD numberOfEventsOrganized INT NOT NULL, DROP years_of_experience, DROP number_of_events_organized, CHANGE id_client id_client INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_c7440455a76ed395 ON client
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_organisateur_user ON client (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D2FBB81F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_panier id_panier INT NOT NULL, CHANGE date_commande date_commande DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_6eeaa67d2fbb81f ON commande
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX id_panier ON commande (id_panier)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D2FBB81F FOREIGN KEY (id_panier) REFERENCES panier (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCC34CD1E9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67F068BCC34CD1E9 ON commentaire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE Id_pub Id_pub INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON competitions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions ADD IdC INT AUTO_INCREMENT NOT NULL, ADD NbPart INT NOT NULL, DROP id_c, DROP nb_part, CHANGE date_c DateC DATE NOT NULL, CHANGE lieu_c LieuC VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions ADD PRIMARY KEY (IdC)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F378B0A977
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F378B0A977
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD createdAt DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updatedAt DATETIME DEFAULT NULL, DROP created_at, DROP updated_at, CHANGE id_prestataire id_prestataire INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_b8b4c6f378b0a977 ON equipement
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_equipement_prestataire ON equipement (id_prestataire)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F378B0A977 FOREIGN KEY (id_prestataire) REFERENCES admin (id_admin) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE generatedcodes CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE jour CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objectif CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE lien lien VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE prix_total prix_total NUMERIC(10, 2) DEFAULT '0.00' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier_produit DROP FOREIGN KEY FK_D31F28A6F7384557
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d31f28a6f7384557 ON panier_produit
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX id_produit ON panier_produit (id_produit)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE panier_produit ADD CONSTRAINT FK_D31F28A6F7384557 FOREIGN KEY (id_produit) REFERENCES produit (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DD8C23C36
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DD8C23C36
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE id_plan id_plan INT AUTO_INCREMENT NOT NULL, CHANGE id_obj id_obj INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan ADD CONSTRAINT ga FOREIGN KEY (id_obj) REFERENCES objectif (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_dd5a5b7dd8c23c36 ON plan
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ga ON plan (id_obj)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DD8C23C36 FOREIGN KEY (id_obj) REFERENCES objectif (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE prix prix NUMERIC(10, 2) NOT NULL, CHANGE description description TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publication CHANGE id_pub Id_pub INT AUTO_INCREMENT NOT NULL, CHANGE id_user id_user INT DEFAULT NULL, CHANGE contenu contenu TEXT DEFAULT NULL, CHANGE date_pub date_pub DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE image_url imageUrl VARCHAR(130) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3C34CD1E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3C90EF3D7
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_38737FB3C34CD1E9 ON reactions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3C90EF3D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX Id_pub ON reactions (Id_pub, Id_user)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_38737fb3c90ef3d7 ON reactions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX Id_user ON reactions (Id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3C90EF3D7 FOREIGN KEY (Id_user) REFERENCES utilisateur (Id_user) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064043F0033A2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640468161836
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064041D3E4624
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404F7384557
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CE6064043F0033A2 ON reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CE60640468161836 ON reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CE6064041D3E4624 ON reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CE606404F7384557 ON reclamation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD createdAT DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updateAT DATETIME DEFAULT NULL, DROP created_at, DROP update_at, CHANGE id_reclamation id_reclamation INT AUTO_INCREMENT NOT NULL, CHANGE id_organisateur id_organisateur INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7D672A9F3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5FB6DEC7D672A9F3 ON reponse
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse ADD createdAt DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updateAT DATETIME DEFAULT NULL, DROP created_at, DROP update_at, CHANGE id_reponse id_reponse INT AUTO_INCREMENT NOT NULL, CHANGE id_reclamation id_reclamation INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON reservations
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD IdR INT AUTO_INCREMENT NOT NULL, ADD NomP VARCHAR(255) NOT NULL, ADD PrenomP VARCHAR(255) NOT NULL, ADD ModeP VARCHAR(255) NOT NULL, ADD IdC INT DEFAULT NULL, DROP id_r, DROP nom_p, DROP prenom_p, DROP mode_p, DROP id_c, CHANGE date_r DateR DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX Etrangère ON reservations (IdC)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD PRIMARY KEY (IdR)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2D3F17567
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E19D9AD2D3F17567 ON service
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD RateCount INT DEFAULT 0, ADD RateSum DOUBLE PRECISION DEFAULT '0', ADD CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP, ADD UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP, DROP rate_count, DROP rate_sum, DROP created_at, DROP updated_at, CHANGE id ID INT AUTO_INCREMENT NOT NULL, CHANGE title Title VARCHAR(20) DEFAULT NULL, CHANGE description Description VARCHAR(255) DEFAULT NULL, CHANGE price Price DOUBLE PRECISION DEFAULT NULL, CHANGE disponibility Disponibility TINYINT(1) DEFAULT NULL, CHANGE type Type VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON typeevenement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE typeevenement ADD id_typeEvenement INT AUTO_INCREMENT NOT NULL, ADD createdAt DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updateAt DATETIME DEFAULT NULL, DROP id_type_evenement, DROP created_at, DROP update_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE typeevenement ADD PRIMARY KEY (id_typeEvenement)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD lastName VARCHAR(255) NOT NULL, ADD firstName VARCHAR(255) NOT NULL, ADD isLocked TINYINT(1) DEFAULT NULL, ADD failedAttempts INT DEFAULT NULL, ADD lockoutTime DATETIME DEFAULT NULL, ADD createdAt DATETIME DEFAULT CURRENT_TIMESTAMP, ADD updateAt DATETIME DEFAULT NULL, DROP last_name, DROP first_name, DROP is_locked, DROP failed_attempts, DROP lockout_time, DROP created_at, DROP update_at, CHANGE id_user id_user INT AUTO_INCREMENT NOT NULL, CHANGE roles roles VARCHAR(255) NOT NULL COLLATE `utf8mb4_bin`, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE token_expiry token_expiry DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur CHANGE id_user Id_user INT AUTO_INCREMENT NOT NULL, CHANGE role role VARCHAR(255) DEFAULT 'utilisateur'
        SQL);
    }
}
