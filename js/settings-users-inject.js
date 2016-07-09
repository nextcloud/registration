(function() {
	var GROUPLIST_TEMPLATE = 
	'<li data-gid="registration" data-usercount="{{count}}" class="">' +
	'<a id="pending-reg" href="#" class="">' +
	'<span class="groupname" style="font-weight: bold;">{{label}}</span>' +
	'</a>' +
	'<span class="utils">' +
	'<span class="usercount">{{count}}</span>' +
	'</span>' +
	'</li>';

	var PENDING_USER = '';
	var REG_CONTENT = 
	'<div id="reg-content" style="display: none; overflow-y: auto;">' +
	'	<table id="reglist" class="grid">' +
	'		<thead>' +
	'			<tr>' +
	'				<th>{{label-username}}</th>' +
	'				<th>{{label-email}}</th>' +
	'				<th>{{label-displayname}}</th>' +
	'				<th>{{label-emailverified}}</th>' +
	'				<th>{{label-actions}}</th>' +
	'			</tr>' +
	'		</thead>' +
	'		<tbody>' +
	'			{{#each users}}' +
	'			<tr>' +
	'				<td>{{this.username}}</td>' +
	'				<td>{{this.email}}</td>' +
	'				<td>{{this.displayname}}</td>' +
	'				<td>{{#if this.emailverified}}' +
	'					<i class="reg-approve icon-checkmark"></i>'+
	'					{{else}}'+
	'					<i class="reg-deny icon-close"></i>'+
	'					{{/if}}</td>' +
	'				<td><a class="reg-deny icon-close">{{../label-deny}}</a> <a class="reg-approve icon-checkmark">{{../label-approve}}</a> '+
	'				<a class="reg-delete icon-delete">{{../label-delete}}</a></td>' +
	'			</tr>' +
	'			{{/each}}' +
	'		</tbody>' +
	'	</table>' +
	'</div>';

	$(document).ready(function() {
		var grouplist_template = Handlebars.compile(GROUPLIST_TEMPLATE);
		var reg_content = Handlebars.compile(REG_CONTENT);
		$('#app-content').after(reg_content({
			'label-username': t('registration', 'Username'),
			'label-email': t('registration', 'Email'),
			'label-displayname': t('registration', 'Display Name'),
			'label-emailverified': t('registration', 'Email Verified'),
			'label-actions': t('registration', 'Actions'),
			'label-approve': t('registration', 'Approve'),
			'label-deny': t('registration', 'Deny'),
			'label-delete': t('registration', 'Delete'),
			'users': [
				{'username': 'clubmate', 'email': 'clib@mate.me', 'displayname': 'John Doe', 'email_validated': false},
			],
		}));
		// TODO only append when count>0
		$('#newgroup-init').after(grouplist_template({'count': '?', 'label': t('registration', 'Pending registration request')}));
		$('#pending-reg').click(function() {
			$('#app-content').hide();
			$('#reg-content').show();
		});
		$('#usergrouplist').on('click', '.isgroup', function () {
			$('#app-content').show();
			$('#reg-content').hide();
		});
	});
})();
