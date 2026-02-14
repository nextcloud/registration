<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div class="guest-box">
		<form action="" method="post" @submit="onSubmit">
			<input type="hidden" name="requesttoken" :value="requesttoken">
			<fieldset>
				<NcNoteCard v-if="message !== ''" type="error">
					{{ message }}
				</NcNoteCard>
				<p v-else>
					{{ t('registration', 'Welcome, you can create your account below.') }}
				</p>

				<NcNoteCard v-if="additionalHint" type="success">
					{{ additionalHint }}
				</NcNoteCard>

				<NcTextField
					v-if="!emailIsOptional || email.length > 0"
					v-model="email"
					type="email"
					:label="t('registration', 'Email')"
					:labelVisible="true"
					name="email"
					disabled>
					<Email :size="20" class="input__icon" />
				</NcTextField>

				<NcTextField
					v-if="!emailIsLogin"
					v-model="loginname"
					type="text"
					name="loginname"
					:label="t('registration', 'Login name')"
					:labelVisible="true"
					required>
					<Key v-if="showFullname" :size="20" class="input__icon" />
					<Account v-else :size="20" class="input__icon" />
				</NcTextField>
				<input
					v-else
					type="hidden"
					name="loginname"
					:value="email">

				<NcTextField
					v-if="showFullname"
					v-model="fullname"
					type="text"
					name="fullname"
					:label="t('registration', 'Full name')"
					:labelVisible="true"
					:required="enforceFullname">
					<Account :size="20" class="input__icon" />
				</NcTextField>
				<input
					v-else
					type="hidden"
					name="fullname"
					value="">

				<NcTextField
					v-if="showPhone"
					v-model="phone"
					type="text"
					name="phone"
					:label="t('registration', 'Phone number')"
					:labelVisible="true"
					:required="enforcePhone">
					<Phone :size="20" class="input__icon" />
				</NcTextField>
				<input
					v-else
					type="hidden"
					name="phone"
					value="">

				<NcPasswordField
					v-model="password"
					:label="t('registration', 'Password')"
					:labelVisible="true"
					name="password"
					required>
					<Lock :size="20" class="input__icon" />
				</NcPasswordField>

				<NcButton
					id="submit"
					type="submit"
					variant="primary"
					:wide="true"
					:disabled="submitting || password.length === 0">
					{{ submitting ? t('registration', 'Loading') : t('registration', 'Create account') }}
				</NcButton>
			</fieldset>
		</form>
	</div>
</template>

<script lang="ts" setup>
import { getRequestToken } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'
import { ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcPasswordField from '@nextcloud/vue/components/NcPasswordField'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import Account from 'vue-material-design-icons/Account.vue'
import Email from 'vue-material-design-icons/Email.vue'
import Key from 'vue-material-design-icons/Key.vue'
import Lock from 'vue-material-design-icons/Lock.vue'
import Phone from 'vue-material-design-icons/Phone.vue'

const email = ref(loadState<string>('registration', 'email'))
const emailIsLogin = loadState<boolean>('registration', 'emailIsLogin')
const emailIsOptional = loadState<boolean>('registration', 'emailIsOptional')
const loginname = ref(loadState<string>('registration', 'loginname'))
const fullname = ref(loadState<string>('registration', 'fullname'))
const showFullname = loadState<boolean>('registration', 'showFullname')
const enforceFullname = loadState<boolean>('registration', 'enforceFullname')
const phone = ref(loadState<string>('registration', 'phone'))
const showPhone = loadState<string>('registration', 'showPhone')
const enforcePhone = loadState<boolean>('registration', 'enforcePhone')
const message = loadState<string>('registration', 'message')
const password = ref(loadState<string>('registration', 'password'))
const additionalHint = loadState<string>('registration', 'additionalHint')
const requesttoken = getRequestToken()
const submitting = ref(false)

/**
 * prevent sending the request twice
 */
function onSubmit() {
	submitting.value = true
}
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
