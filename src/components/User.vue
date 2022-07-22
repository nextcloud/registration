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
			<input type="hidden" name="requesttoken" :value="requesttoken">
			<fieldset>
				<div v-if="message !== ''" class="notecard error">
					{{ message }}
				</div>
				<p v-else>
					{{ t('registration', 'Welcome, you can create your account below.') }}
				</p>

				<div v-if="additionalHint" class="notecard success">
					{{ additionalHint }}
				</div>

				<p v-if="!emailIsOptional || email.length > 0" class="input">
					<input id="email"
						v-model="email"
						type="email"
						class="input__field"
						name="email"
						disabled>
					<label for="email" class="infield">{{ t('registration', 'Email') }}></label>
					<Email :size="20" class="input__icon" fill-color="var(--color-placeholder-dark)" />
				</p>

				<p v-if="!emailIsLogin" class="input">
					<input id="loginname"
						v-model="loginname"
						type="text"
						name="loginname"
						class="input__field"
						:placeholder="t('registration', 'Login name')"
						required>
					<label for="loginname" class="infield">{{ t('registration', 'Login name') }}</label>
					<Key :size="20" class="input__icon" fill-color="var(--color-placeholder-dark)" />
				</p>
				<input v-else
					type="hidden"
					name="loginname"
					:value="email">

				<p v-if="showFullname" class="input">
					<input id="fullname"
						v-model="fullname"
						type="text"
						name="fullname"
						class="input__field"
						:placeholder="t('registration', 'Full name')"
						:required="enforceFullname">
					<label for="fullname" class="infield">{{ t('registration', 'Full name') }}</label>
					<Account :size="20" class="input__icon" fill-color="var(--color-placeholder-dark)" />
				</p>
				<input v-else
					type="hidden"
					name="fullname"
					value="">

				<p v-if="showPhone" class="groupmiddle input">
					<input id="phone"
						v-model="phone"
						type="text"
						name="phone"
						class="input__field"
						:placeholder="t('registration', 'Phone number')"
						:required="enforcePhone">
					<label for="phone" class="infield">{{ t('registration', 'Phone number') }}</label>
					<Phone :size="20" class="input__icon" fill-color="var(--color-placeholder-dark)" />
				</p>
				<input v-else
					type="hidden"
					name="phone"
					value="">

				<p class="groupbottom input">
					<input id="password"
						v-model="password"
						:type="passwordInputType"
						class="input__field"
						name="password"
						:placeholder="t('registration', 'Password')"
						required>
					<label for="password" class="infield">{{ t('registration', 'Password') }}</label>
					<Lock :size="20" class="input__icon" fill-color="var(--color-placeholder-dark)" />
					<Button class="toggle-password"
						type="tertiary-no-background"
						:aria-label="isPasswordHidden ? t('registration', 'Show password') : t('registration', 'Hide password')"
						@click.stop.prevent="togglePassword"
						@keydown.enter="togglePassword">
						<template #icon>
							<Eye v-if="isPasswordHidden" :size="20" />
							<EyeOff v-else :size="20" />
						</template>
					</Button>
				</p>
				<Button id="submit"
					native-type="submit"
					type="primary"
					:wide="true"
					:disabled="submitting || password.length === 0"
					@click="submit">
					{{ submitting ? t('registration', 'Loading') : t('registration', 'Create account') }}
				</Button>
			</fieldset>
		</form>
	</div>
</template>

<script>
import { getRequestToken } from '@nextcloud/auth'
import Button from '@nextcloud/vue/dist/Components/Button'
import { loadState } from '@nextcloud/initial-state'
import Eye from 'vue-material-design-icons/Eye'
import EyeOff from 'vue-material-design-icons/EyeOff'
import Email from 'vue-material-design-icons/Email'
import Lock from 'vue-material-design-icons/Lock'
import Phone from 'vue-material-design-icons/Phone'
import Account from 'vue-material-design-icons/Account'
import Key from 'vue-material-design-icons/Key'

export default {
	name: 'User',

	components: {
		Button,
		Eye,
		EyeOff,
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
		submit() {
			// prevent sending the request twice
			this.submitting = true
			setTimeout(() => {
				this.submitting = false
			}, 1000)
		},
	},
}
</script>

<style lang="scss" scoped>
.input {
	position: relative;

	&__field {
		margin-bottom: 12px;
		width: calc(100% - 56px);
		padding-left: 36px;
	}

	&__icon {
		position: absolute;
		left: 16px;
		top: 20px;
	}
}

.toggle-password {
	position: absolute;
	top: 6px;
	right: 10px;
	color: var(--color-text-lighter);
}
</style>
