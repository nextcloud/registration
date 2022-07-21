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
				<div v-if="message !== ''" class="notecard error">
					{{ message }}
				</div>

				<p class="email">
					<input id="email"
						type="email"
						name="email"
						class="email__field"
						:placeholder="emailPlaceholder"
						required
						autofocus>
					<label v-if="emailIsOptional" for="email" class="infield">{{ t('registration', 'Email (optional)') }}</label>
					<label v-else for="email" class="infield">{{ t('registration', 'Email') }}</label>
					<img class="svg email__icon" :src="emailIconPath" alt="">
				</p>

				<div id="terms_of_service" />

				<input type="hidden" name="requesttoken" :value="requesttoken">
				<Button id="submit"
					native-type="submit"
					type="primary"
					:wide="true">
					{{ submitValue }}
				</Button>

				<a id="lost-password-back" :href="loginFormLink">
					{{ t('registration', 'Back to login') }}
				</a>
			</fieldset>
		</form>
	</div>
</template>

<script>
import { getRequestToken } from '@nextcloud/auth'
import Button from '@nextcloud/vue/dist/Components/Button'
import { generateFilePath } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'

export default {
	name: 'RegistrationEmail',

	components: {
		Button,
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
		emailPlaceholder() {
			return this.emailIsOptional
				? t('registration', 'Email (optional)')
				: t('registration', 'Email')
		},
		emailIconPath() {
			return generateFilePath('core', 'img', 'actions/mail.svg')
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
.email {
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
</style>
