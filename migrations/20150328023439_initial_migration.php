<?php

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function change()
    {
        $tr_table = $this->table('organization');
        $tr_table
            ->addColumn('name', 'text')
            ->addColumn('address1', 'text')
            ->addColumn('address2', 'text')
            ->addColumn('city', 'text')
            ->addColumn('state', 'text')
            ->addColumn('zip', 'text')
            ->addColumn('phone', 'text')
            ->addColumn('description', 'text')
            ->addColumn('img_url', 'text')
            ->addColumn('twitter', 'text')
            ->addColumn('facebook_url', 'text')
            ->addColumn('website_url', 'text')
            ->addColumn('date_created', 'datetime')
            ->addColumn('approved', 'boolean')
            ->create();
    }
}
