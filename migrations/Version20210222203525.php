<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210222203525 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file ADD vocabulary_track_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36103B5D9B FOREIGN KEY (vocabulary_track_id) REFERENCES track (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C9F36103B5D9B ON file (vocabulary_track_id)');
        $this->addSql('ALTER TABLE track ADD vocabulary_file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6A453AF14 FOREIGN KEY (vocabulary_file_id) REFERENCES file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D6E3F8A6A453AF14 ON track (vocabulary_file_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36103B5D9B');
        $this->addSql('DROP INDEX UNIQ_8C9F36103B5D9B ON file');
        $this->addSql('ALTER TABLE file DROP vocabulary_track_id');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6A453AF14');
        $this->addSql('DROP INDEX UNIQ_D6E3F8A6A453AF14 ON track');
        $this->addSql('ALTER TABLE track DROP vocabulary_file_id');
    }
}
