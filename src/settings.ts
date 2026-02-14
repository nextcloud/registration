/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { translate, translatePlural } from '@nextcloud/l10n'
import { createApp } from 'vue'
import AdminSettings from './AdminSettings.vue'

const app = createApp(AdminSettings)

app.config.globalProperties.t = translate
app.config.globalProperties.n = translatePlural

app.mount('#registration_settings_form')
