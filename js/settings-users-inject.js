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
	'<div id="reg-content" style="display none;">' +
	'	<table id="reglist" class="grid">' +
	'		<thead>' +
	'			<tr>' +
	'				<th>{{label-username}}</th>' +
	'				<th>{{label-email}}</th>' +
	'				<th></th>' +
	'			</tr>' +
	'		</thead>' +
	'		<tbody>' +
	'			{{#each users}}' +
	'			<tr>' +
	'				<td>{{this.username}}</td>' +
	'				<td>{{this.email}}</td>' +
	'				<td><a class="reg-deny icon-close">Deny</a> <a class="reg-approve icon-checkmark">Approve</a></td>' +
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
			'users': [
				{'username': 'clubmate', 'email': 'clib@mate.me'},
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
