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
					type="text"
					name="token"
					:label="t('registration', 'Verification code')"
					:labelVisible="true"
					required
					modelValue=""
					autofocus>
					<ShieldCheck :size="20" />
				</NcTextField>

				<input type="hidden" name="requesttoken" :value="requesttoken">
				<NcButton
					id="submit"
					type="submit"
					variant="primary"
					:wide="true">
					{{ t('registration', 'Verify') }}
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
import NcButton from '@nextcloud/vue/components/NcButton'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import ShieldCheck from 'vue-material-design-icons/ShieldCheck.vue'

const message = loadState<string>('registration', 'message')
const requesttoken = getRequestToken()
const loginFormLink = loadState<string>('registration', 'loginFormLink')

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
