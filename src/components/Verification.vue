<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
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
					value=""
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
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import { loadState } from '@nextcloud/initial-state'
import ShieldCheck from 'vue-material-design-icons/ShieldCheck.vue'

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
