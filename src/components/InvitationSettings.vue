<!--
  - SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcSettingsSection
		:name="t('registration', 'Invitations')"
		:description="t('registration', 'Create invitation links to allow specific people to register. Each invitation can be limited in the number of uses, the quota and the allowed email address or domain.')">
		<div class="invitation-form">
			<div class="row">
				<NcTextField
					v-model="code"
					:label="t('registration', 'Invitation code')"
					labelVisible
					:disabled="loading"
					:placeholder="t('registration', 'Leave empty to generate one')" />
				<NcButton
					variant="secondary"
					:disabled="loading"
					@click="generateCode">
					{{ t('registration', 'Generate') }}
				</NcButton>
			</div>

			<div class="row">
				<NcTextField
					v-model="email"
					type="email"
					:label="t('registration', 'Restrict to email address')"
					labelVisible
					:disabled="loading"
					:placeholder="t('registration', 'Anyone (optional)')" />
			</div>

			<div class="row">
				<NcTextField
					v-model="domain"
					type="text"
					:label="t('registration', 'Restrict to email domain')"
					labelVisible
					:disabled="loading"
					:placeholder="t('registration', 'example.com (optional)')" />
			</div>

			<div class="row three">
				<NcTextField
					v-model="quota"
					type="text"
					:label="t('registration', 'Storage quota')"
					labelVisible
					:disabled="loading"
					:placeholder="t('registration', 'e.g. 5 GB (optional)')" />
				<NcTextField
					v-model="maxUses"
					type="number"
					:label="t('registration', 'Maximum number of uses')"
					labelVisible
					:disabled="loading"
					:placeholder="t('registration', 'Unlimited (optional)')" />
				<NcTextField
					v-model="expires"
					type="datetime-local"
					:label="t('registration', 'Expiry date')"
					labelVisible
					:disabled="loading"
					:placeholder="t('registration', 'No expiry (optional)')" />
			</div>

			<NcCheckboxRadioSwitch
				v-model="skipEmailVerification"
				type="switch"
				:disabled="loading">
				{{ t('registration', 'Skip email verification') }}
			</NcCheckboxRadioSwitch>
			<p><em>{{ t('registration', 'If enabled, the email address does not need to be verified and the user can create the account right after entering their email address.') }}</em></p>

			<NcCheckboxRadioSwitch
				v-model="skipAdminApproval"
				type="switch"
				:disabled="loading">
				{{ t('registration', 'Skip administrator approval') }}
			</NcCheckboxRadioSwitch>
			<p><em>{{ t('registration', 'If enabled, the account is enabled immediately even when administrator approval is required.') }}</em></p>

			<div class="actions">
				<NcButton
					variant="primary"
					:disabled="loading"
					@click="createInvitation">
					{{ t('registration', 'Create invitation') }}
				</NcButton>
			</div>
		</div>

		<NcNoteCard v-if="listMessage" :type="listMessageType">
			{{ listMessage }}
		</NcNoteCard>

		<div v-if="invitations.length > 0" class="invitation-list">
			<table class="grid-table">
				<thead>
					<tr>
						<th>{{ t('registration', 'Code') }}</th>
						<th>{{ t('registration', 'Restriction') }}</th>
						<th>{{ t('registration', 'Quota') }}</th>
						<th>{{ t('registration', 'Uses') }}</th>
						<th>{{ t('registration', 'Expires') }}</th>
						<th>{{ t('registration', 'Link') }}</th>
						<th />
					</tr>
				</thead>
				<tbody>
					<tr v-for="invitation in invitations" :key="invitation.id">
						<td><code>{{ invitation.code }}</code></td>
						<td>{{ restrictionLabel(invitation) }}</td>
						<td>{{ invitation.quota || '—' }}</td>
						<td>{{ invitation.uses }}{{ invitation.max_uses ? ` / ${invitation.max_uses}` : '' }}</td>
						<td>{{ invitation.expires ? formatDate(invitation.expires) : '—' }}</td>
						<td>
							<NcButton
								variant="tertiary"
								type="button"
								@click="copyLink(invitation)">
								{{ t('registration', 'Copy link') }}
							</NcButton>
						</td>
						<td>
							<NcButton
								variant="tertiary"
								type="button"
								@click="showDetails(invitation)">
								{{ t('registration', 'Details') }}
							</NcButton>
							<NcButton
								variant="tertiary"
								type="button"
								@click="deleteInvitation(invitation)">
								{{ t('registration', 'Delete') }}
							</NcButton>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<NcDialog
			:open="showDialog"
			:name="t('registration', 'Invitation created')"
			@update:open="showDialog = $event">
			<p class="invitation-dialog-text">
				{{ t('registration', 'Share the link or the code below with the person you want to invite.') }}
			</p>
			<div class="invitation-dialog-row">
				<NcTextField
					:modelValue="selectedInvitation?.code"
					:label="t('registration', 'Code')"
					labelVisible
					readonly />
				<NcButton
					variant="secondary"
					@click="copyText(selectedInvitation?.code ?? '')">
					{{ t('registration', 'Copy code') }}
				</NcButton>
			</div>
			<div class="invitation-dialog-row">
				<NcTextField
					:modelValue="selectedInvitation?.link"
					:label="t('registration', 'Invitation link')"
					labelVisible
					readonly />
				<NcButton
					variant="secondary"
					@click="copyLink(selectedInvitation)">
					{{ t('registration', 'Copy link') }}
				</NcButton>
			</div>
		</NcDialog>
	</NcSettingsSection>
</template>

<script lang="ts" setup>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { onMounted, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import NcTextField from '@nextcloud/vue/components/NcTextField'

type Invitation = {
	id: number
	code: string
	email: string | null
	domain: string | null
	quota: string | null
	max_uses: number | null
	uses: number
	expires: string | null
	created_at: string
	skip_email_verification: boolean
	skip_admin_approval: boolean
	link: string
}

const loading = ref(false)
const invitations = ref<Invitation[]>([])
const code = ref('')
const email = ref('')
const domain = ref('')
const quota = ref('')
const maxUses = ref('')
const expires = ref('')
const skipEmailVerification = ref(false)
const skipAdminApproval = ref(false)
const listMessage = ref('')
const listMessageType = ref<'error' | 'info'>('error')
const showDialog = ref(false)
const selectedInvitation = ref<Invitation | null>(null)

/**
 * Human readable label for the restriction of an invitation
 *
 * @param invitation invitation to describe
 */
function restrictionLabel(invitation: Invitation): string {
	if (invitation.email) {
		return invitation.email
	}
	if (invitation.domain) {
		return `@${invitation.domain}`
	}
	return t('registration', 'Anyone')
}

/**
 * Make a datetime string human friendly
 *
 * @param value datetime value
 */
function formatDate(value: string): string {
	return (value ?? '').replace('T', ' ')
}

/**
 * Load all invitations into the list
 */
async function loadInvitations() {
	try {
		const response = await axios.get(generateUrl('/apps/registration/admin/invitations'))
		invitations.value = response.data
	} catch (e) {
		showError(t('registration', 'Could not load invitations'))
		console.error(e)
	}
}

/**
 * Generate a fresh random invitation code
 */
function generateCode() {
	const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
	const values = new Uint32Array(8)
	crypto.getRandomValues(values)
	code.value = Array.from(values, (n) => characters[n % characters.length]).join('')
}

/**
 * Create a new invitation from the form values
 */
async function createInvitation() {
	loading.value = true
	listMessage.value = ''
	try {
		const response = await axios.post(generateUrl('/apps/registration/admin/invitations'), {
			code: code.value,
			email: email.value,
			domain: domain.value,
			quota: quota.value,
			max_uses: maxUses.value,
			expires: expires.value ? expires.value.replace('T', ' ') : '',
			skip_email_verification: skipEmailVerification.value ? 'true' : '',
			skip_admin_approval: skipAdminApproval.value ? 'true' : '',
		})

		if (response.data?.status === 'error') {
			listMessage.value = response.data.message ?? t('registration', 'Could not create the invitation')
			listMessageType.value = 'error'
		} else {
			showSuccess(t('registration', 'Invitation created'))
			invitations.value.unshift(response.data)
			code.value = ''
			email.value = ''
			domain.value = ''
			quota.value = ''
			maxUses.value = ''
			expires.value = ''
			skipEmailVerification.value = false
			skipAdminApproval.value = false
			selectedInvitation.value = response.data
			showDialog.value = true
		}
	} catch (e) {
		const msg = e.response?.data?.message
		listMessage.value = msg ?? t('registration', 'Could not create the invitation')
		listMessageType.value = 'error'
	} finally {
		loading.value = false
	}
}

/**
 * Delete an invitation
 *
 * @param invitation invitation to delete
 */
async function deleteInvitation(invitation: Invitation) {
	try {
		await axios.delete(generateUrl(`/apps/registration/admin/invitations/${invitation.id}`))
		showSuccess(t('registration', 'Invitation deleted'))
		invitations.value = invitations.value.filter((i) => i.id !== invitation.id)
	} catch (e) {
		showError(t('registration', 'Could not delete the invitation'))
		console.error(e)
	}
}

/**
 * Copy an arbitrary text to the clipboard
 *
 * @param text text to copy
 */
async function copyText(text: string) {
	try {
		await navigator.clipboard.writeText(text)
		showSuccess(t('registration', 'Copied to clipboard'))
	} catch (e) {
		showError(t('registration', 'Could not copy to clipboard'))
		console.error(e)
	}
}

/**
 * Copy the invitation link to the clipboard
 *
 * @param invitation invitation whose link is copied
 */
async function copyLink(invitation: Invitation) {
	await copyText(invitation.link)
}

/**
 * Open the details dialog for an invitation
 *
 * @param invitation invitation to show
 */
function showDetails(invitation: Invitation) {
	selectedInvitation.value = invitation
	showDialog.value = true
}

onMounted(() => {
	loadInvitations()
})
</script>

<style scoped lang="scss">
.invitation-form {
	display: flex;
	flex-direction: column;
	gap: .75rem;
	max-width: 700px;

	.row {
		display: flex;
		align-items: flex-end;
		gap: .5rem;

		> :deep(*) {
			flex: 1;
		}
	}

	.three {
		flex-wrap: wrap;
	}

	.actions {
		margin-top: .25rem;
	}
}

.invitation-list {
	margin-top: 1.5rem;

	.grid-table {
		width: 100%;
		border-collapse: collapse;

		th,
		td {
			padding: .5rem;
			text-align: start;
			vertical-align: middle;
		}

		th {
			font-weight: 600;
			border-bottom: 1px solid var(--color-border);
		}

		td {
			border-bottom: 1px solid var(--color-border);
		}
	}
}

.invitation-dialog-text {
	margin-top: 0;
}

.invitation-dialog-row {
	display: flex;
	align-items: flex-end;
	gap: .5rem;
	margin-bottom: .75rem;

	> :deep(*) {
		flex: 1;
	}
}
</style>
