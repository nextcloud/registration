<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Registration\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0005Date20200710135953 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('registration')) {
			$table = $schema->createTable('registration');
			$table->addColumn('id', Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('email', Types::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('username', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('password', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('displayname', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('email_confirmed', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('token', Types::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('client_secret', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('requested', Types::DATETIME_MUTABLE, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
		}
		return $schema;
	}
}
