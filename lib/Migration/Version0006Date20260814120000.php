<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0006Date20260814120000 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	#[\Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('registration_invitation')) {
			$table = $schema->createTable('registration_invitation');
			$table->addColumn('id', Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('code', Types::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('email', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('domain', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('quota', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('max_uses', Types::INTEGER, [
				'notnull' => false,
			]);
			$table->addColumn('uses', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('expires', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('created_by', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['code'], 'registration_invitation_code_idx');
		}

		$registrationTable = $schema->getTable('registration');
		if (!$registrationTable->hasColumn('invitation_id')) {
			$registrationTable->addColumn('invitation_id', Types::INTEGER, [
				'notnull' => false,
				'unsigned' => true,
			]);
		}

		return $schema;
	}
}