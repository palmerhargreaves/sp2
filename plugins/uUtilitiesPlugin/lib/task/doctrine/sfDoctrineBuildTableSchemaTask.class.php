<?php

/**
 * @package     uUtilitiesPlugin
 * @subpackage  SchemaBuilder
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class sfDoctrineBuildTableSchemaTask extends sfDoctrineBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'console'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
            new sfCommandOption('table', null, sfCommandOption::PARAMETER_REQUIRED, 'The name of the database table'),
        ));

        $this->namespace = 'doctrine';
        $this->name = 'build-table-schema';
        $this->briefDescription = 'Generate the schema for a specific table in a database.';
        $this->detailedDescription = <<<EOF
The [build-table-schema|INFO] task adds the schema for a single table to the
main schema.yml file. This can be very useful when dealing with a large number
of databases in a project and not all tables are managed with Doctrine Models.

Call it with:

  [symfony doctrine:build-table-schema|INFO] --connection=conn_name --table=tbl_name --application=appname
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        if ($options['application'] == false) {
            throw new Exception('You must set the application name in order for the database connectoins to be properly initialized (They are on the application level)');
        }

        if ($options['connection'] == false) {
            throw new Exception('You must set the connection name!');
        }

        if ($options['table'] == false) {
            throw new Exception('You must set a table name!');
        }

        $databaseManager = new sfDatabaseManager($this->configuration);

        $this->logSection('doctrine', 'generating yaml schema from database');

        $config = $this->getCliConfig();
        $schemaPath = $config['yaml_schema_path'] . uFs::DS . 'schema.yml';
        $connections = array($options['connection'] => array($options['table']));
        $builderOptions = array();

        return SchemaBuilder::getInstance()->update($schemaPath, $connections, $builderOptions);
    }
}
