<!--
  - @copyright Copyright (c) 2022 Carl Schwan <carl@carlschwan.eu>
  -
  - @author Carl Schwan <carl@carlschwan.eu>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->
<template>
	<div class="guest-box">
		<form action="" method="post">
			<fieldset>
				<NcNoteCard v-if="message !== ''" type="error">
					{{ message }}
				</NcNoteCard>

				<NcTextField type="text"
					name="token"
					:label="t('registration', 'Verification code')"
					:label-visible="true"
					required
					autofocus>
					<ShieldCheck :size="20" />
				</NcTextField>

				<input type="hidden" name="requesttoken" :value="requesttoken">
				<NcButton id="submit"
					native-type="submit"
					type="primary"
					:wide="true">
					{{ t('registration', 'Verify') }}
				</NcButton>

				<NcButton type="tertiary"
					:href="loginFormLink"
					:wide="true">
					{{ t('registration', 'Back to login') }}
				</NcButton>
			</fieldset>
		</form>
	</div>
</template>

<script>
import { getRequestToken } from '@nextcloud/auth'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import { loadState } from '@nextcloud/initial-state'
import ShieldCheck from 'vue-material-design-icons/ShieldCheck.vue'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'

export default {
	name: 'Verification',

	components: {
		NcButton,
		NcTextField,
		NcNoteCard,
		ShieldCheck,
	},

	data() {
		return {
			message: loadState('registration', 'message'),
			requesttoken: getRequestToken(),
			loginFormLink: loadState('registration', 'loginFormLink'),
		}
	},
}
</script>

<style lang="scss" scoped>
.guest-box {
	text-align: left;
}

fieldset {
	display: flex;
	flex-direction: column;
	gap: .5rem;
}

.button-vue--vue-tertiary {
	box-sizing: border-box;
}
</style>
