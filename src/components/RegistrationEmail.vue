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

				<NcTextField
					name="email"
					type="email"
					:label="emailLabel"
					:labelVisible="true"
					required
					modelValue=""
					autofocus>
					<Email :size="20" />
				</NcTextField>

				<div id="terms_of_service" />

				<input type="hidden" name="requesttoken" :value="requesttoken">
				<NcButton
					id="submit"
					type="submit"
					variant="primary"
					:wide="true">
					{{ submitValue }}
				</NcButton>

				<NcButton
					variant="tertiary"
					:href="loginFormLink"
					:wide="true">
					{{ t('registration', 'Back to login') }}
				</NcButton>
			</fieldset>
		</form>
	</div>
</template>

<script lang="ts" setup>
import { getRequestToken } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { computed } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import Email from 'vue-material-design-icons/Email.vue'

const emailIsOptional = loadState<boolean>('registration', 'emailIsOptional')
const message = loadState<string>('registration', 'message')
const requesttoken = getRequestToken()
const disableEmailVerification = loadState<boolean>('registration', 'disableEmailVerification')
const isLoginFlow = loadState<boolean>('registration', 'isLoginFlow')
const loginFormLink = loadState<string>('registration', 'loginFormLink')

const emailLabel = computed(() => {
	return emailIsOptional
		? t('registration', 'Email (optional)')
		: t('registration', 'Email')
})
const submitValue = computed(() => {
	if (emailIsOptional || disableEmailVerification) {
		return t('registration', 'Continue')
	} else if (isLoginFlow) {
		return t('registration', 'Request verification code')
	} else {
		return t('registration', 'Request verification link')
	}
})
</script>

<style lang="scss" scoped>
.guest-box {
	text-align: start;
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
