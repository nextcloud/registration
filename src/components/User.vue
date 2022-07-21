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
						type="email"
						class="input__field"
						name="email"
						:value="email"
						disabled>
					<label for="email" class="infield">{{ t('registration', 'Email') }}></label>
					<img id="email-icon"
						class="input__icon"
						:src="emailIconPath"
						alt="">
				</p>

				<p v-if="!emailIsLogin" class="input">
					<input id="loginname"
						type="text"
						name="loginname"
						class="input__field"
						:value="loginname"
						:placeholder="t('registration', 'Login name')"
						required>
					<label for="loginname" class="infield">{{ t('registration', 'Login name') }}</label>
					<img id="loginname-icon"
						class="input__icon"
						:src="authIconPath"
						alt="">
				</p>
				<input v-else
					type="hidden"
					name="loginname"
					:value="email">

				<p v-if="showFullname" class="input">
					<input id="fullname"
						type="text"
						name="fullname"
						class="input__field"
						:value="fullname"
						:placeholder="t('registration', 'Full name')"
						:required="enforceFullname">
					<label for="fullname" class="infield">{{ t('registration', 'Full name') }}</label>
					<img id="fullname-icon"
						class="input__icon"
						:src="userIconPath"
						alt="">
				</p>
				<input v-else
					type="hidden"
					name="fullname"
					value="">

				<p v-if="showPhone" class="groupmiddle input">
					<input id="phone"
						type="text"
						name="phone"
						class="input__field"
						:value="phone"
						:placeholder="t('registration', 'Phone number')"
						:required="enforcePhone">
					<label for="phone" class="infield">{{ t('registration', 'Phone number') }}</label>
					<img id="phone-icon"
						class="input__icon"
						:src="phoneIconPath"
						alt="">
				</p>
				<input v-else
					type="hidden"
					name="phone"
					value="">

				<p class="groupbottom input">
					<input id="password"
						type="password"
						class="input__field"
						name="password"
						:value="password"
						:placeholder="t('registration', 'Password')"
						required>
					<label for="password" class="infield">{{ t('registration', 'Password') }}</label>
					<img id="password-icon"
						class="svg input__icon"
						:src="passwordIconPath"
						alt="">
					<Button class="toggle-password"
						type="tertiary-no-background"
						:aria-label="isPasswordHidden ? t('registration', 'Show password') : t('registration', 'Hide password')"
						@click.stop.prevent="togglePassword">
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
					:disabled="submitting"
					@click="submit">
					{{ t('registration', 'Create account') }}
				</Button>
			</fieldset>
		</form>
	</div>
</template>

<script>
import { getRequestToken } from '@nextcloud/auth'
import Button from '@nextcloud/vue/dist/Components/Button'
import { generateFilePath } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import Eye from 'vue-material-design-icons/Eye'
import EyeOff from 'vue-material-design-icons/EyeOff'

export default {
	name: 'User',

	components: {
		Button,
		Eye,
		EyeOff,
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

	computed: {
		emailIconPath() {
			return generateFilePath('core', 'img', 'actions/mail.svg')
		},
		phoneIconPath() {
			return generateFilePath('core', 'img', 'clients/phone.svg')
		},
		userIconPath() {
			return generateFilePath('core', 'img', 'actions/user.svg')
		},
		authIconPath() {
			return generateFilePath('core', 'img', 'categories/auth.svg')
		},
		passwordIconPath() {
			return generateFilePath('core', 'img', 'actions/password.svg')
		},
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
			this.submitting = true
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
		top: 22px;
		filter: alpha(opacity=30);
		opacity: .3;
	}
}

.toggle-password {
	position: absolute;
	top: 6px;
	right: 10px;
	color: var(--color-text-lighter);
}
</style>
