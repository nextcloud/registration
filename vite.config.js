/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig({
	// entry points: {name: script}
	form: 'src/form.ts',
	settings: 'src/settings.ts',
}, {
	extractLicenseInformation: {
		includeSourceMaps: true,
	},
	thirdPartyLicense: false,
	emptyOutputDirectory: {
		additionalDirectories: ['css'],
	},
})
