<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DrChronoMessenger extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('users', ['id' => false, 'primary_key' => ['id']]);
        $table
            ->addColumn('id', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['null' => true, 'limit' => 50])
            ->addColumn('patient_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('patient_name', 'string', ['null' => true, 'limit' => 50])
            ->addColumn('state', 'text');
        $table->create();
    }
}
