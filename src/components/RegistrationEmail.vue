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

				<NcTextField name="email"
					type="email"
					:label="emailLabel"
					:label-visible="true"
					required
					value=""
					autofocus>
					<Email :size="20" />
				</NcTextField>

				<div id="terms_of_service" />

				<input type="hidden" name="requesttoken" :value="requesttoken">
				<NcButton id="submit"
					native-type="submit"
					type="primary"
					:wide="true">
					{{ submitValue }}
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
import Email from 'vue-material-design-icons/Email.vue'

export default {
	name: 'RegistrationEmail',

	components: {
		NcButton,
		NcTextField,
		NcNoteCard,
		Email,
	},

	data() {
		return {
			emailIsOptional: loadState('registration', 'emailIsOptional'),
			message: loadState('registration', 'message'),
			requesttoken: getRequestToken(),
			disableEmailVerification: loadState('registration', 'disableEmailVerification'),
			isLoginFlow: loadState('registration', 'isLoginFlow'),
			loginFormLink: loadState('registration', 'loginFormLink'),
		}
	},

	computed: {
		emailLabel() {
			return this.emailIsOptional
				? t('registration', 'Email (optional)')
				: t('registration', 'Email')
		},
		submitValue() {
			if (this.emailIsOptional || this.disableEmailVerification) {
				return t('registration', 'Continue')
			} else if (this.isLoginFlow) {
				return t('registration', 'Request verification code')
			} else {
				return t('registration', 'Request verification link')
			}
		},
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
