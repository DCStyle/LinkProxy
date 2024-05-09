<?php

namespace DC\LinkProxy;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	// ################################ INSTALLATION ####################

	public function installStep1()
	{
		$sm = $this->schemaManager();

		foreach ($this->getTables() AS $tableName => $closure)
		{
			$sm->createTable($tableName, $closure);
		}
	}

	public function installStep2()
    {
        $sm = $this->schemaManager();

        foreach ($this->getAlterTables() AS $tableName => $closure)
        {
            $sm->alterTable($tableName, $closure[0]);
        }
    }

    // ################################ UPGRADATION #######################

    public function upgrade2000031Step1()
    {
        $this->installStep1();
    }

	// ################################ UNINSTALL #########################

	public function uninstallStep1()
	{
		$sm = $this->schemaManager();

		foreach (array_keys($this->getTables()) AS $tableName)
		{
			$sm->dropTable($tableName);
		}
	}

	public function uninstallStep2()
    {
        $sm = $this->schemaManager();

        foreach ($this->getAlterTables() AS $tableName => $closure)
        {
            $sm->alterTable($tableName, $closure[1]);
        }
    }

	// ############################# TABLE / DATA DEFINITIONS ##############################

	protected function getTables()
	{
		$tables = [];

        return $tables;
    }

	protected function getAlterTables()
    {
        $tables = [];

		return $tables;
	}
}