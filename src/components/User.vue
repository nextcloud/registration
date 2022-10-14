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

				<NcTextField v-if="!emailIsOptional || email.length > 0"
					:value.sync="email"
					type="email"
					:label="t('registration', 'Email')"
					:label-visible="true"
					name="email"
					disabled>
					<Email :size="20" class="input__icon" />
				</NcTextField>

				<NcTextField v-if="!emailIsLogin"
					:value.sync="loginname"
					type="text"
					name="loginname"
					:label="t('registration', 'Login name')"
					:label-visible="true"
					required>
					<Key :size="20" class="input__icon" />
				</NcTextField>
				<input v-else
					type="hidden"
					name="loginname"
					:value="email">

				<NcTextField v-if="showFullname"
					:value.sync="fullname"
					type="text"
					name="fullname"
					:label="t('registration', 'Full name')"
					:label-visible="true"
					:required="enforceFullname">
					<Account :size="20" class="input__icon" />
				</NcTextField>
				<input v-else
					type="hidden"
					name="fullname"
					value="">

				<NcTextField v-if="showPhone"
					:value.sync="phone"
					type="text"
					name="phone"
					:label="t('registration', 'Phone number')"
					:label-visible="true"
					:required="enforcePhone">
					<Phone :size="20" class="input__icon" />
				</NcTextField>
				<input v-else
					type="hidden"
					name="phone"
					value="">

				<NcPasswordField :value.sync="password"
					:label="t('registration', 'Password')"
					:label-visible="true"
					name="password"
					required>
					<Lock :size="20" class="input__icon" />
				</NcPasswordField>

				<NcButton id="submit"
					native-type="submit"
					type="primary"
					:wide="true"
					:disabled="submitting || password.length === 0">
					{{ submitting ? t('registration', 'Loading') : t('registration', 'Create account') }}
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
import NcPasswordField from '@nextcloud/vue/dist/Components/NcPasswordField.js'
import { loadState } from '@nextcloud/initial-state'
import Email from 'vue-material-design-icons/Email.vue'
import Lock from 'vue-material-design-icons/Lock.vue'
import Phone from 'vue-material-design-icons/Phone.vue'
import Account from 'vue-material-design-icons/Account.vue'
import Key from 'vue-material-design-icons/Key.vue'

export default {
	name: 'User',

	components: {
		NcButton,
		NcNoteCard,
		NcTextField,
		NcPasswordField,
		Email,
		Lock,
		Phone,
		Account,
		Key,
	},

	data() {
		return {
			email: loadState('registration', 'email'),
			emailIsLogin: loadState('registration', 'emailIsLogin'),
			emailIsOptional: loadState('registration', 'emailIsOptional'),
			loginname: loadState('registration', 'loginname'),
			fullname: loadState('registration', 'fullname'),
			showFullname: loadState('registration', 'showFullname'),
			enforceFullname: loadState('registration', 'enforceFullname'),
			phone: loadState('registration', 'phone'),
			showPhone: loadState('registration', 'showPhone'),
			enforcePhone: loadState('registration', 'enforcePhone'),
			message: loadState('registration', 'message'),
			password: loadState('registration', 'password'),
			additionalHint: loadState('registration', 'additionalHint'),
			requesttoken: getRequestToken(),
			loginFormLink: loadState('registration', 'loginFormLink'),
			isPasswordHidden: true,
			passwordInputType: 'password',
			submitting: false,
		}
	},

	methods: {
		togglePassword() {
			if (this.passwordInputType === 'password') {
				this.passwordInputType = 'text'
			} else {
				this.passwordInputType = 'password'
			}
		},
		onSubmit() {
			// prevent sending the request twice
			this.submitting = true
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
