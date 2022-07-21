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

				<p class="token">
					<input id="token"
						type="text"
						name="token"
						class="token__field"
						:placeholder="t('registration', 'Verification code')"
						required
						autofocus>
					<label for="token" class="infield">{{ t('registration', 'Verification code') }}</label>
					<img class="svg token__icon" :src="verifyIconPath" alt="">
				</p>

				<input type="hidden" name="requesttoken" :value="requesttoken">
				<Button id="submit"
					native-type="submit"
					type="primary"
					:wide="true">
					{{ t('registration', 'Verify') }}
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
	name: 'Verification',

	components: {
		Button,
	},

	data() {
		return {
			message: loadState('registration', 'message'),
			requesttoken: getRequestToken(),
			loginFormLink: loadState('registration', 'loginFormLink'),
		}
	},

	computed: {
		verifyIconPath() {
			return generateFilePath('registration', 'img', 'verify.svg')
		},
	},
}
</script>

<style lang="scss" scoped>
.token {
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
