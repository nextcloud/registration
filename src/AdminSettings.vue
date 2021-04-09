<!--
  - @copyright Copyright (c) 2018 Roeland Jago Douma <roeland@famdouma.nl>
  -
  - @author Roeland Jago Douma <roeland@famdouma.nl>
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
	<div id="registration_settings_form">
		<SettingsSection
			:title="t('registration', 'Registration settings')">
			<p>
				<input id="admin_approval"
					v-model="adminApproval"
					type="checkbox"
					name="admin_approval"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="admin_approval">{{ t('registration', 'Require admin approval') }}</label>
			</p>
			<em>{{ t('registration', 'Enabling "admin approval" will prevent registrations from mobile and desktop clients to complete as the credentials can not be verified by the client until the user was enabled.') }}</em>

			<p>
				<label for="registered_user_group">
					{{ t('registration', 'Registered users default group') }}
				</label>
				<Multiselect
					id="registered_user_group"
					v-model="registeredUserGroup"
					:placeholder="t('registration', 'Select group')"
					:options="groups"
					:disabled="loading"
					:searchable="true"
					:tag-width="60"
					:loading="loadingGroups"
					:allow-empty="true"
					:close-on-select="false"
					track-by="id"
					label="displayname"
					@search-change="searchGroup"
					@change="saveData" />
			</p>
		</SettingsSection>

		<SettingsSection
			:title="t('registration', 'Email settings')">
			<p>
				<label for="allowed_domains">{{ domainListLabel }}</label>
				<input
					id="allowed_domains"
					v-model="allowedDomains"
					type="text"
					name="allowed_domains"
					:disabled="loading"
					placeholder="nextcloud.com;*.example.com"
					:aria-label="t('registration', 'Allowed email domain')"
					@input="debounceSavingSlow">
			</p>

			<p>
				<input id="domains_is_blocklist"
					v-model="domainsIsBlocklist"
					type="checkbox"
					name="domains_is_blocklist"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="domains_is_blocklist">{{ t('registration', 'Block listed email domains instead of allowing them') }}</label>
			</p>

			<p>
				<input id="show_domains"
					v-model="showDomains"
					type="checkbox"
					name="show_domains"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="show_domains">{{ showDomainListLabel }}</label>
			</p>

			<p>
				<input id="disable_email_verification"
					v-model="disableEmailVerification"
					type="checkbox"
					name="disable_email_verification"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="disable_email_verification">{{ t('registration', 'Disable email verification') }}</label>
			</p>
		</SettingsSection>

		<SettingsSection
			:title="t('registration', 'User settings')">
			<p>
				<input id="email_is_login"
					v-model="emailIsLogin"
					type="checkbox"
					name="email_is_login"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="email_is_login">{{ t('registration', 'Force email as login name') }}</label>
			</p>
			<template
				v-if="!emailIsLogin">
				<p>
					<label for="username_policy_regex">{{ t('registration', 'Login name policy') }}</label>
					<input
						id="username_policy_regex"
						v-model="usernamePolicyRegex"
						type="text"
						name="username_policy_regex"
						:disabled="loading"
						placeholder="E.g.: /^[a-z-]+\.[a-z-]+$/"
						:aria-label="t('registration', 'Regular expression to validate login names')"
						@input="debounceSavingSlow">
				</p>
				<em>{{ t('registration', 'If configured, login names will be validated through the regular expression. If the validation fails the user is prompted with a generic error. Make sure your regex is working correctly.') }}</em>
			</template>

			<p>
				<input id="show_fullname"
					v-model="showFullname"
					type="checkbox"
					name="show_fullname"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="show_fullname">{{ t('registration', 'Show full name field') }}</label>
			</p>

			<p
				v-if="showFullname"
				class="indent">
				<input id="enforce_fullname"
					v-model="enforceFullname"
					type="checkbox"
					name="enforce_fullname"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="enforce_fullname">{{ t('registration', 'Enforce full name field') }}</label>
			</p>

			<p
				v-if="canShowPhone">
				<input id="show_phone"
					v-model="showPhone"
					type="checkbox"
					name="show_phone"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="show_phone">{{ t('registration', 'Show phone field') }}</label>
			</p>

			<p
				v-if="canShowPhone && showPhone"
				class="indent">
				<input id="enforce_phone"
					v-model="enforcePhone"
					type="checkbox"
					name="enforce_phone"
					class="checkbox"
					:disabled="loading"
					@change="saveData">
				<label for="enforce_phone">{{ t('registration', 'Enforce phone field') }}</label>
			</p>
		</SettingsSection>

		<SettingsSection
			:title="t('registration', 'User instructions')"
			:description="t('registration', 'Caution: The user instructions will not be translated and will therefore be displayed as configured below for all users regardless of their actual language.')">
			<h3>{{ t('registration', 'Registration form instructions') }}</h3>
			<p>
				<input v-model="additionalHint"
					type="text"
					name="additional_hint"
					:disabled="loading"
					placeholder="Please create your username following the scheme 'firstname.lastname'."
					:aria-label="t('registration', 'A short message that is shown to the user in the registration process.')"
					@input="debounceSavingSlow">
			</p>
			<em>{{ t('registration', 'Add additional user instructions (e.g. for choosing their login name). If configured the text is displayed in the account creation step of the registration process.') }}</em>

			<h3>{{ t('registration', 'Verification email instructions') }}</h3>
			<p>
				<input v-model="emailVerificationHint"
					type="text"
					name="email_verification_hint"
					:disabled="loading"
					placeholder="Welcome to Nextcloud. Please verify your email address to continue with the registration."
					:aria-label="t('registration', 'A short message that is shown to the user in the verification email.')"
					@input="debounceSavingSlow">
			</p>
			<em>{{ t('registration', 'Add additional user instructions (e.g. a welcome message or some hint on how to complete registration). If configured the text is embedded in the verification-email.') }}</em>
		</SettingsSection>
	</div>
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import '@nextcloud/dialogs/styles/toast.scss'
import { loadState } from '@nextcloud/initial-state'
import { generateOcsUrl, generateUrl } from '@nextcloud/router'
import debounce from 'debounce'

export default {
	name: 'AdminSettings',

	components: {
		Multiselect,
		SettingsSection,
	},

	data() {
		return {
			loading: false,
			loadingGroups: false,
			groups: [],
			saveNotification: null,

			adminApproval: false,
			registeredUserGroup: '',
			allowedDomains: '',
			domainsIsBlocklist: false,
			showDomains: false,
			disableEmailVerification: false,
			emailIsLogin: false,
			usernamePolicyRegex: '',
			showFullname: false,
			enforceFullname: false,
			canShowPhone: false,
			showPhone: false,
			enforcePhone: false,
			additionalHint: '',
			emailVerificationHint: '',
		}
	},

	computed: {
		domainListLabel() {
			if (this.domainsIsBlocklist) {
				return t('registration', 'Blocked email domains')
			}

			return t('registration', 'Allowed email domains')
		},
		showDomainListLabel() {
			if (this.domainsIsBlocklist) {
				return t('registration', 'Show the blocked email domains to users')
			}

			return t('registration', 'Show the allowed email domains to users')
		},
	},

	mounted() {
		this.adminApproval = loadState('registration', 'admin_approval_required')
		this.registeredUserGroup = loadState('registration', 'registered_user_group')
		this.allowedDomains = loadState('registration', 'allowed_domains')
		this.domainsIsBlocklist = loadState('registration', 'domains_is_blocklist')
		this.showDomains = loadState('registration', 'show_domains')
		this.disableEmailVerification = loadState('registration', 'disable_email_verification')
		this.emailIsLogin = loadState('registration', 'email_is_login')
		this.usernamePolicyRegex = loadState('registration', 'username_policy_regex')
		this.showFullname = loadState('registration', 'show_fullname')
		this.enforceFullname = loadState('registration', 'enforce_fullname')
		this.canShowPhone = loadState('registration', 'can_show_phone')
		this.showPhone = loadState('registration', 'show_phone')
		this.enforcePhone = loadState('registration', 'enforce_phone')
		this.additionalHint = loadState('registration', 'additional_hint')
		this.emailVerificationHint = loadState('registration', 'email_verification_hint')

		this.searchGroup('')
	},
	methods: {
		debounceSavingSlow: debounce(function() {
			this.saveData()
		}, 2000),

		async saveData() {
			this.loading = true
			if (this.saveNotification) {
				await this.saveNotification.hideToast()
			}

			try {
				const response = await axios.post(generateUrl('/apps/registration/settings'), {
					admin_approval_required: this.adminApproval,
					registered_user_group: this.registeredUserGroup?.id,
					allowed_domains: this.allowedDomains,
					domains_is_blocklist: this.domainsIsBlocklist,
					show_domains: this.showDomains,
					disable_email_verification: this.disableEmailVerification,
					email_is_login: this.emailIsLogin,
					username_policy_regex: this.usernamePolicyRegex,
					show_fullname: this.showFullname,
					enforce_fullname: this.enforceFullname,
					show_phone: this.showPhone,
					enforce_phone: this.enforcePhone,
					additional_hint: this.additionalHint,
					email_verification_hint: this.emailVerificationHint,
				})

				if (response?.data?.status === 'success' && response?.data?.data?.message) {
					this.saveNotification = showSuccess(response.data.data.message)
				} else if (response?.data?.data?.message) {
					this.saveNotification = showError(response.data.data.message)
				} else {
					this.saveNotification = showError(t('registration', 'An error occurred while saving the settings'))
				}
			} catch (e) {
				if (e.response?.data?.data?.message) {
					this.saveNotification = showError(e.response.data.data.message)
				} else {
					this.saveNotification = showError(t('registration', 'An error occurred while saving the settings'))
					console.error(e)
				}
			}

			this.loading = false
		},

		searchGroup: debounce(async function(query) {
			this.loadingGroups = true
			try {
				const response = await axios.get(generateOcsUrl('cloud', 2) + 'groups/details', {
					search: query,
					limit: 20,
					offset: 0,
				})
				this.groups = response.data.ocs.data.groups.sort(function(a, b) {
					return a.displayname.localeCompare(b.displayname)
				})
			} catch (err) {
				console.error('Could not fetch groups', err)
			} finally {
				this.loadingGroups = false
			}
		}, 500),
	},
}
</script>

<style scoped lang="scss">

p {
	label {
		display: block;
	}

	&.indent {
		padding-left: 28px;
	}
}

</style>
