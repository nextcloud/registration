/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import AdminSettings from './AdminSettings.vue'

Vue.prototype.t = t
Vue.prototype.OC = OC

export const app = new Vue({
	el: '#registration_settings_form',
	render: h => h(AdminSettings),
})
