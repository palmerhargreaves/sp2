<?php

class Addvariablewhenupdated extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('variable', 'updated_at', 'timestamp', 25);
    }

    public function down()
    {
        $this->removeColumn('variable', 'updated_at');
    }
}
