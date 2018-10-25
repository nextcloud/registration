<?php
\OCP\Util::addStyle('registration', 'style');
if ($_['showtimezone'] === "yes") {
	\OCP\Util::addScript('registration', 'moment-timezone-with-data');
}
\OCP\Util::addScript('registration', 'form');
if ( \OCP\Util::getVersion()[0] >= 12 )
	\OCP\Util::addStyle('core', 'guest');
?><form action="<?php print_unescaped(\OC::$server->getURLGenerator()->linkToRoute('registration.register.createAccount', array('token'=>$_['token']))) ?>" method="post">
	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" />
	<fieldset>
		<?php if ( !empty($_['errormsgs']) ) {?>
		<ul class="error">
			<?php foreach ( $_['errormsgs'] as $errormsg ) {
				echo "<li>$errormsg</li>";
			} ?>
		</ul>
		<?php } else { ?>
		<ul class="msg">
			<li><?php p($l->t('Welcome, you can create your account below.'));?></li>
		</ul>
		<?php } ?>
		<p class="grouptop">
			<input type="email" name="email" id="email" value="<?php echo $_['email']; ?>" disabled />
			<label for="email" class="infield"><?php echo $_['email']; ?></label>
			<img id="email-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
		</p>
			<?php if ($_['showfullname'] === "yes") {?>
			<p class="groupmiddle">
				<input type="text" id="fullname" name="fullname" value="<?php echo !empty($_['entered_data']['fullname']) ? $_['entered_data']['fullname'] : ''; ?>" placeholder="<?php p($l->t('Full name')); ?>" required />
				<img id="fullname-icon" class="svg" src="<?php print_unescaped(image_path('', 'categories/social.svg')); ?>" alt=""/>
				<label for="fullname" class="infield"><?php p($l->t('Full name'));?></label>
			</p>
			<?php }?>

			<?php if ($_['showcompany'] === "yes") {?>
			<p class="groupmiddle">
				<input type="text" id="company" name="company" value="<?php echo !empty($_['entered_data']['company']) ? $_['entered_data']['company'] : ''; ?>" placeholder="<?php p($l->t('Company')); ?>" />
				<label class="infield"><?php p($l->t('Company'));?></label>
				<img id="company-icon" class="svg" src="<?php print_unescaped(image_path('', 'categories/social.svg')); ?>" alt=""/>
			</p>
			<?php }?>
			<?php if ($_['showphoneno'] === "yes") {?>
			<p class="groupmiddle">
				<input type="text" id="phoneno" name="phoneno" value="<?php echo !empty($_['entered_data']['phoneno']) ? $_['entered_data']['phoneno'] : ''; ?>" placeholder="<?php p($l->t('Phone Number')); ?>" />
				<label class="infield"><?php p($l->t('Phone Number'));?></label>
				<img id="phoneno-icon" class="svg" src="<?php print_unescaped(image_path('', 'categories/social.svg')); ?>" alt=""/>
			</p>
			<?php }?>

		<p class="groupmiddle">
			<input type="text" name="username" id="username" value="<?php echo !empty($_['entered_data']['user']) ? $_['entered_data']['user'] : ''; ?>" placeholder="<?php p($l->t('Username')); ?>" />
			<label for="username" class="infield"><?php p($l->t('Username')); ?></label>
			<img id="username-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/user.svg')); ?>" alt=""/>
		</p>

		<p class="groupbottom">
			<input type="password" name="password" id="password" placeholder="<?php p($l->t('Password')); ?>"/>
			<label for="password" class="infield"><?php p($l->t( 'Password' )); ?></label>
			<img id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
			<input id="show" name="show" type="checkbox">
			<label id="show-password" style="display: inline;" for="show"></label>
		</p>
		<?php if ($_['showcountry'] === "yes") {?>
			<p class="groupofone">
				<img id="country-icon" class="svg" src="<?php print_unescaped(image_path('', 'places/link.svg')); ?>" alt=""/>
				<label for="country" class="msg"><?php p($l->t('Country'));?></label>
				<select id="country" name="country" class="selfield" data-value="<?php echo !empty($_['entered_data']['country']) ? $_['entered_data']['country'] : ''; ?>" placeholder="<?php p($l->t('Country')); ?>" >
					<option value="AF">Afghanistan</option>
					<option value="AX">Åland Islands</option>
					<option value="AL">Albania</option>
					<option value="DZ">Algeria</option>
					<option value="AS">American Samoa</option>
					<option value="AD">Andorra</option>
					<option value="AO">Angola</option>
					<option value="AI">Anguilla</option>
					<option value="AQ">Antarctica</option>
					<option value="AG">Antigua and Barbuda</option>
					<option value="AR">Argentina</option>
					<option value="AM">Armenia</option>
					<option value="AW">Aruba</option>
					<option value="AU">Australia</option>
					<option value="AT">Austria</option>
					<option value="AZ">Azerbaijan</option>
					<option value="BS">Bahamas</option>
					<option value="BH">Bahrain</option>
					<option value="BD">Bangladesh</option>
					<option value="BB">Barbados</option>
					<option value="BY">Belarus</option>
					<option value="BE">Belgium</option>
					<option value="BZ">Belize</option>
					<option value="BJ">Benin</option>
					<option value="BM">Bermuda</option>
					<option value="BT">Bhutan</option>
					<option value="BO">Bolivia, Plurinational State of</option>
					<option value="BQ">Bonaire, Sint Eustatius and Saba</option>
					<option value="BA">Bosnia and Herzegovina</option>
					<option value="BW">Botswana</option>
					<option value="BV">Bouvet Island</option>
					<option value="BR">Brazil</option>
					<option value="IO">British Indian Ocean Territory</option>
					<option value="BN">Brunei Darussalam</option>
					<option value="BG">Bulgaria</option>
					<option value="BF">Burkina Faso</option>
					<option value="BI">Burundi</option>
					<option value="KH">Cambodia</option>
					<option value="CM">Cameroon</option>
					<option value="CA">Canada</option>
					<option value="CV">Cape Verde</option>
					<option value="KY">Cayman Islands</option>
					<option value="CF">Central African Republic</option>
					<option value="TD">Chad</option>
					<option value="CL">Chile</option>
					<option value="CN">China</option>
					<option value="CX">Christmas Island</option>
					<option value="CC">Cocos (Keeling) Islands</option>
					<option value="CO">Colombia</option>
					<option value="KM">Comoros</option>
					<option value="CG">Congo</option>
					<option value="CD">Congo, the Democratic Republic of the</option>
					<option value="CK">Cook Islands</option>
					<option value="CR">Costa Rica</option>
					<option value="CI">Côte d'Ivoire</option>
					<option value="HR">Croatia</option>
					<option value="CU">Cuba</option>
					<option value="CW">Curaçao</option>
					<option value="CY">Cyprus</option>
					<option value="CZ">Czech Republic</option>
					<option value="DK">Denmark</option>
					<option value="DJ">Djibouti</option>
					<option value="DM">Dominica</option>
					<option value="DO">Dominican Republic</option>
					<option value="EC">Ecuador</option>
					<option value="EG">Egypt</option>
					<option value="SV">El Salvador</option>
					<option value="GQ">Equatorial Guinea</option>
					<option value="ER">Eritrea</option>
					<option value="EE">Estonia</option>
					<option value="ET">Ethiopia</option>
					<option value="FK">Falkland Islands (Malvinas)</option>
					<option value="FO">Faroe Islands</option>
					<option value="FJ">Fiji</option>
					<option value="FI">Finland</option>
					<option value="FR">France</option>
					<option value="GF">French Guiana</option>
					<option value="PF">French Polynesia</option>
					<option value="TF">French Southern Territories</option>
					<option value="GA">Gabon</option>
					<option value="GM">Gambia</option>
					<option value="GE">Georgia</option>
					<option value="DE">Germany</option>
					<option value="GH">Ghana</option>
					<option value="GI">Gibraltar</option>
					<option value="GR">Greece</option>
					<option value="GL">Greenland</option>
					<option value="GD">Grenada</option>
					<option value="GP">Guadeloupe</option>
					<option value="GU">Guam</option>
					<option value="GT">Guatemala</option>
					<option value="GG">Guernsey</option>
					<option value="GN">Guinea</option>
					<option value="GW">Guinea-Bissau</option>
					<option value="GY">Guyana</option>
					<option value="HT">Haiti</option>
					<option value="HM">Heard Island and McDonald Islands</option>
					<option value="VA">Holy See (Vatican City State)</option>
					<option value="HN">Honduras</option>
					<option value="HK">Hong Kong</option>
					<option value="HU">Hungary</option>
					<option value="IS">Iceland</option>
					<option value="IN">India</option>
					<option value="ID">Indonesia</option>
					<option value="IR">Iran, Islamic Republic of</option>
					<option value="IQ">Iraq</option>
					<option value="IE">Ireland</option>
					<option value="IM">Isle of Man</option>
					<option value="IL">Israel</option>
					<option value="IT">Italy</option>
					<option value="JM">Jamaica</option>
					<option value="JP">Japan</option>
					<option value="JE">Jersey</option>
					<option value="JO">Jordan</option>
					<option value="KZ">Kazakhstan</option>
					<option value="KE">Kenya</option>
					<option value="KI">Kiribati</option>
					<option value="KP">Korea, Democratic People's Republic of</option>
					<option value="KR">Korea, Republic of</option>
					<option value="KW">Kuwait</option>
					<option value="KG">Kyrgyzstan</option>
					<option value="LA">Lao People's Democratic Republic</option>
					<option value="LV">Latvia</option>
					<option value="LB">Lebanon</option>
					<option value="LS">Lesotho</option>
					<option value="LR">Liberia</option>
					<option value="LY">Libya</option>
					<option value="LI">Liechtenstein</option>
					<option value="LT">Lithuania</option>
					<option value="LU">Luxembourg</option>
					<option value="MO">Macao</option>
					<option value="MK">Macedonia, the former Yugoslav Republic of</option>
					<option value="MG">Madagascar</option>
					<option value="MW">Malawi</option>
					<option value="MY">Malaysia</option>
					<option value="MV">Maldives</option>
					<option value="ML">Mali</option>
					<option value="MT">Malta</option>
					<option value="MH">Marshall Islands</option>
					<option value="MQ">Martinique</option>
					<option value="MR">Mauritania</option>
					<option value="MU">Mauritius</option>
					<option value="YT">Mayotte</option>
					<option value="MX">Mexico</option>
					<option value="FM">Micronesia, Federated States of</option>
					<option value="MD">Moldova, Republic of</option>
					<option value="MC">Monaco</option>
					<option value="MN">Mongolia</option>
					<option value="ME">Montenegro</option>
					<option value="MS">Montserrat</option>
					<option value="MA">Morocco</option>
					<option value="MZ">Mozambique</option>
					<option value="MM">Myanmar</option>
					<option value="NA">Namibia</option>
					<option value="NR">Nauru</option>
					<option value="NP">Nepal</option>
					<option value="NL">Netherlands</option>
					<option value="NC">New Caledonia</option>
					<option value="NZ">New Zealand</option>
					<option value="NI">Nicaragua</option>
					<option value="NE">Niger</option>
					<option value="NG">Nigeria</option>
					<option value="NU">Niue</option>
					<option value="NF">Norfolk Island</option>
					<option value="MP">Northern Mariana Islands</option>
					<option value="NO">Norway</option>
					<option value="OM">Oman</option>
					<option value="PK">Pakistan</option>
					<option value="PW">Palau</option>
					<option value="PS">Palestinian Territory, Occupied</option>
					<option value="PA">Panama</option>
					<option value="PG">Papua New Guinea</option>
					<option value="PY">Paraguay</option>
					<option value="PE">Peru</option>
					<option value="PH">Philippines</option>
					<option value="PN">Pitcairn</option>
					<option value="PL">Poland</option>
					<option value="PT">Portugal</option>
					<option value="PR">Puerto Rico</option>
					<option value="QA">Qatar</option>
					<option value="RE">Réunion</option>
					<option value="RO">Romania</option>
					<option value="RU">Russian Federation</option>
					<option value="RW">Rwanda</option>
					<option value="BL">Saint Barthélemy</option>
					<option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
					<option value="KN">Saint Kitts and Nevis</option>
					<option value="LC">Saint Lucia</option>
					<option value="MF">Saint Martin (French part)</option>
					<option value="PM">Saint Pierre and Miquelon</option>
					<option value="VC">Saint Vincent and the Grenadines</option>
					<option value="WS">Samoa</option>
					<option value="SM">San Marino</option>
					<option value="ST">Sao Tome and Principe</option>
					<option value="SA">Saudi Arabia</option>
					<option value="SN">Senegal</option>
					<option value="RS">Serbia</option>
					<option value="SC">Seychelles</option>
					<option value="SL">Sierra Leone</option>
					<option value="SG">Singapore</option>
					<option value="SX">Sint Maarten (Dutch part)</option>
					<option value="SK">Slovakia</option>
					<option value="SI">Slovenia</option>
					<option value="SB">Solomon Islands</option>
					<option value="SO">Somalia</option>
					<option value="ZA">South Africa</option>
					<option value="GS">South Georgia and the South Sandwich Islands</option>
					<option value="SS">South Sudan</option>
					<option value="ES">Spain</option>
					<option value="LK">Sri Lanka</option>
					<option value="SD">Sudan</option>
					<option value="SR">Suriname</option>
					<option value="SJ">Svalbard and Jan Mayen</option>
					<option value="SZ">Swaziland</option>
					<option value="SE">Sweden</option>
					<option value="CH">Switzerland</option>
					<option value="SY">Syrian Arab Republic</option>
					<option value="TW">Taiwan</option>
					<option value="TJ">Tajikistan</option>
					<option value="TZ">Tanzania, United Republic of</option>
					<option value="TH">Thailand</option>
					<option value="TL">Timor-Leste</option>
					<option value="TG">Togo</option>
					<option value="TK">Tokelau</option>
					<option value="TO">Tonga</option>
					<option value="TT">Trinidad and Tobago</option>
					<option value="TN">Tunisia</option>
					<option value="TR">Turkey</option>
					<option value="TM">Turkmenistan</option>
					<option value="TC">Turks and Caicos Islands</option>
					<option value="TV">Tuvalu</option>
					<option value="UG">Uganda</option>
					<option value="UA">Ukraine</option>
					<option value="AE">United Arab Emirates</option>
					<option value="GB">United Kingdom</option>
					<option value="US">United States</option>
					<option value="UM">United States Minor Outlying Islands</option>
					<option value="UY">Uruguay</option>
					<option value="UZ">Uzbekistan</option>
					<option value="VU">Vanuatu</option>
					<option value="VE">Venezuela, Bolivarian Republic of</option>
					<option value="VN">Viet Nam</option>
					<option value="VG">Virgin Islands, British</option>
					<option value="VI">Virgin Islands, U.S.</option>
					<option value="WF">Wallis and Futuna</option>
					<option value="EH">Western Sahara</option>
					<option value="YE">Yemen</option>
					<option value="ZM">Zambia</option>
					<option value="ZW">Zimbabwe</option>
				</select>
			</p>
			<?php }?>
			<?php if ($_['showlanguage'] === "yes") {?>
			<p class="grouptop">
				<img id="language-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/info.svg')); ?>" alt=""/>
				<label for="language" class="msg"><?php p($l->t('Language'));?></label>
				<select id="language" name="language" data-value="<?php echo !empty($_['entered_data']['language']) ? $_['entered_data']['language'] : ''; ?>" placeholder="Language" class="selfield">
					<option value="en">
						English (US) </option>
					<option value="es">
						Castellano </option>
					<option value="fr">
						Français </option>
					<option value="de">
						Deutsch (Persönlich: Du) </option>
					<option value="de_DE">
						Deutsch (Förmlich: Sie) </option>
					<option value="ja">
						Japanese (日本語) </option>
					<option value="ar">
						اللغة العربية </option>\
					<option value="ru">
						Русский </option>
					<option value="nl">
						Nederlands </option>
					<option value="it">
						Italiano </option>
					<option value="pt_BR">
						Português Brasileiro </option>
					<option value="pt_PT">
						Português </option>
					<option value="da">
						Dansk </option>
					<option value="sv">
						Svenska </option>
					<option value="tr">
						Türkçe </option>
					<option value="zh_CN">
						简体中文 </option>
					<option value="ko">
						한국어 </option>
					<optgroup label="––––––––––"></optgroup>
					<option value="ast">
						Asturianu </option>
					<option value="id">
						Bahasa Indonesia </option>
					<option value="ca">
						Català </option>
					<option value="et_EE">
						Eesti </option>
					<option value="en_GB">
						English (British English) </option>
					<option value="es_AR">
						Español (Argentina) </option>
					<option value="es_CL">
						Español (Chile) </option>
					<option value="es_CO">
						Español (Colombia) </option>
					<option value="es_CR">
						Español (Costa Rica) </option>
					<option value="es_DO">
						Español (Dominican Republic) </option>
					<option value="es_EC">
						Español (Ecuador) </option>
					<option value="es_SV">
						Español (El Salvador) </option>
					<option value="es_GT">
						Español (Guatemala) </option>
					<option value="es_HN">
						Español (Honduras) </option>
					<option value="es_419">
						Español (Latin America) </option>
					<option value="es_MX">
						Español (México) </option>
					<option value="es_NI">
						Español (Nicaragua) </option>
					<option value="es_PA">
						Español (Panama) </option>
					<option value="es_PY">
						Español (Paraguay) </option>
					<option value="es_PE">
						Español (Peru) </option>
					<option value="es_PR">
						Español (Puerto Rico) </option>
					<option value="es_UY">
						Español (Uruguay) </option>
					<option value="eu">
						Euskara </option>
					<option value="lv">
						Latviešu </option>
					<option value="lt_LT">
						Lietuvių </option>
					<option value="hu">
						Magyar </option>
					<option value="nb">
						Norsk bokmål </option>
					<option value="ro">
						Română </option>
					<option value="sq">
						Shqip </option>
					<option value="sk">
						Slovenčina </option>
					<option value="sl">
						Slovenščina </option>
					<option value="vi">
						Tiếng Việt </option>
					<option value="pl">
						polski </option>
					<option value="fi">
						suomi </option>
					<option value="is">
						Íslenska </option>
					<option value="cs">
						Česky </option>
					<option value="el">
						Ελληνικά </option>
					<option value="bg">
						Български </option>
					<option value="sr">
						Српски </option>
					<option value="uk">
						Українська </option>
					<option value="he">
						עברית </option>\
					<option value="fa">
						فارسى </option>\
					<option value="th">
						ภาษาไทย - Thai languages </option>
					<option value="ka_GE">
						ქართული </option>
					<option value="zh_TW">
						正體中文（臺灣） </option>
					<option value="af">
						af </option>
					<option value="uz">
						uz </option>
				</select>
			</p>
			<?php }?>
			<?php if ($_['showtimezone'] === "yes") {?>
			<p class="grouptop">
				<img id="timezone-icon" class="svg" src="<?php print_unescaped(image_path('', 'places/calendar.svg')); ?>" alt=""/>
				<label for="timezone" class="msg"><?php p($l->t('Timezone'));?></label>
				<select id="timezone" name="timezone" class="selfield" data-value="<?php echo !empty($_['entered_data']['timezone']) ? $_['entered_data']['timezone'] : ''; ?>" placeholder="<?php p($l->t('Timezone')); ?>" >
					<option value="Asia/Kabul"><?php p($l->t('Afghanistan / Kabul')); ?></option>
					<option value="Europe/Mariehamn"><?php p($l->t('Åland Islands / Mariehamn')); ?></option>
					<option value="Europe/Tirane"><?php p($l->t('Albania / Tirane')); ?></option>
					<option value="Africa/Algiers"><?php p($l->t('Algeria / Algiers')); ?></option>
					<option value="Pacific/Pago_Pago"><?php p($l->t('American Samoa / Pago Pago')); ?></option>
					<option value="Europe/Andorra"><?php p($l->t('Andorra')); ?></option>
					<option value="Africa/Luanda"><?php p($l->t('Angola / Luanda')); ?></option>
					<option value="America/Anguilla"><?php p($l->t('Anguilla')); ?></option>
					<option value="America/Antigua"><?php p($l->t('Antigua and Barbuda / Antigua')); ?></option>
					<option value="America/Argentina/Buenos_Aires"><?php p($l->t('Argentina / Buenos Aires')); ?></option>
					<option value="America/Argentina/Catamarca"><?php p($l->t('Argentina / Catamarca')); ?></option>
					<option value="America/Argentina/Cordoba"><?php p($l->t('Argentina / Cordoba')); ?></option>
					<option value="America/Argentina/Jujuy"><?php p($l->t('Argentina / Jujuy')); ?></option>
					<option value="America/Argentina/La_Rioja"><?php p($l->t('Argentina / La Rioja')); ?></option>
					<option value="America/Argentina/Mendoza"><?php p($l->t('Argentina / Mendoza')); ?></option>
					<option value="America/Argentina/Rio_Gallegos"><?php p($l->t('Argentina / Rio Gallegos')); ?></option>
					<option value="America/Argentina/Salta"><?php p($l->t('Argentina / Salta')); ?></option>
					<option value="America/Argentina/San_Juan"><?php p($l->t('Argentina / San Juan')); ?></option>
					<option value="America/Argentina/San_Luis"><?php p($l->t('Argentina / San Luis')); ?></option>
					<option value="America/Argentina/Tucuman"><?php p($l->t('Argentina / Tucuman')); ?></option>
					<option value="America/Argentina/Ushuaia"><?php p($l->t('Argentina / Ushuaia')); ?></option>
					<option value="Asia/Yerevan"><?php p($l->t('Armenia / Yerevan')); ?></option>
					<option value="America/Aruba"><?php p($l->t('Aruba')); ?></option>
					<option value="Australia/Adelaide"><?php p($l->t('Australia / Adelaide')); ?></option>
					<option value="Australia/Brisbane"><?php p($l->t('Australia / Brisbane')); ?></option>
					<option value="Australia/Broken_Hill"><?php p($l->t('Australia / Broken Hill')); ?></option>
					<option value="Australia/Currie"><?php p($l->t('Australia / Currie')); ?></option>
					<option value="Australia/Darwin"><?php p($l->t('Australia / Darwin')); ?></option>
					<option value="Australia/Eucla"><?php p($l->t('Australia / Eucla')); ?></option>
					<option value="Australia/Hobart"><?php p($l->t('Australia / Hobart')); ?></option>
					<option value="Australia/Lindeman"><?php p($l->t('Australia / Lindeman')); ?></option>
					<option value="Australia/Lord_Howe"><?php p($l->t('Australia / Lord Howe')); ?></option>
					<option value="Australia/Melbourne"><?php p($l->t('Australia / Melbourne')); ?></option>
					<option value="Australia/Perth"><?php p($l->t('Australia / Perth')); ?></option>
					<option value="Australia/Sydney"><?php p($l->t('Australia / Sydney')); ?></option>
					<option value="Europe/Vienna"><?php p($l->t('Austria / Vienna')); ?></option>
					<option value="Asia/Baku"><?php p($l->t('Azerbaijan / Baku')); ?></option>
					<option value="America/Nassau"><?php p($l->t('Bahamas / Nassau')); ?></option>
					<option value="Asia/Bahrain"><?php p($l->t('Bahrain')); ?></option>
					<option value="Asia/Dhaka"><?php p($l->t('Bangladesh / Dhaka')); ?></option>
					<option value="America/Barbados"><?php p($l->t('Barbados')); ?></option>
					<option value="Europe/Minsk"><?php p($l->t('Belarus / Minsk')); ?></option>
					<option value="Europe/Brussels"><?php p($l->t('Belgium / Brussels')); ?></option>
					<option value="America/Belize"><?php p($l->t('Belize')); ?></option>
					<option value="Africa/Porto-Novo"><?php p($l->t('Benin / Porto-Novo')); ?></option>
					<option value="Atlantic/Bermuda"><?php p($l->t('Bermuda')); ?></option>
					<option value="Asia/Thimphu"><?php p($l->t('Bhutan / Thimphu')); ?></option>
					<option value="America/La_Paz"><?php p($l->t('Bolivia, Plurinational State of / La Paz')); ?></option>
					<option value="Europe/Sarajevo"><?php p($l->t('Bosnia and Herzegovina / Sarajevo')); ?></option>
					<option value="Africa/Gaborone"><?php p($l->t('Botswana / Gaborone')); ?></option>
					<option value="America/Araguaina"><?php p($l->t('Brazil / Araguaina')); ?></option>
					<option value="America/Bahia"><?php p($l->t('Brazil / Bahia')); ?></option>
					<option value="America/Belem"><?php p($l->t('Brazil / Belem')); ?></option>
					<option value="America/Boa_Vista"><?php p($l->t('Brazil / Boa Vista')); ?></option>
					<option value="America/Campo_Grande"><?php p($l->t('Brazil / Campo Grande')); ?></option>
					<option value="America/Cuiaba"><?php p($l->t('Brazil / Cuiaba')); ?></option>
					<option value="America/Eirunepe"><?php p($l->t('Brazil / Eirunepe')); ?></option>
					<option value="America/Fortaleza"><?php p($l->t('Brazil / Fortaleza')); ?></option>
					<option value="America/Maceio"><?php p($l->t('Brazil / Maceio')); ?></option>
					<option value="America/Manaus"><?php p($l->t('Brazil / Manaus')); ?></option>
					<option value="America/Noronha"><?php p($l->t('Brazil / Noronha')); ?></option>
					<option value="America/Porto_Velho"><?php p($l->t('Brazil / Porto Velho')); ?></option>
					<option value="America/Recife"><?php p($l->t('Brazil / Recife')); ?></option>
					<option value="America/Rio_Branco"><?php p($l->t('Brazil / Rio Branco')); ?></option>
					<option value="America/Santarem"><?php p($l->t('Brazil / Santarem')); ?></option>
					<option value="America/Sao_Paulo"><?php p($l->t('Brazil / Sao Paulo')); ?></option>
					<option value="Asia/Brunei"><?php p($l->t('Brunei Darussalam / Brunei')); ?></option>
					<option value="Europe/Sofia"><?php p($l->t('Bulgaria / Sofia')); ?></option>
					<option value="Africa/Ouagadougou"><?php p($l->t('Burkina Faso / Ouagadougou')); ?></option>
					<option value="Africa/Bujumbura"><?php p($l->t('Burundi / Bujumbura')); ?></option>
					<option value="Asia/Phnom_Penh"><?php p($l->t('Cambodia / Phnom Penh')); ?></option>
					<option value="Africa/Douala"><?php p($l->t('Cameroon / Douala')); ?></option>
					<option value="America/Atikokan"><?php p($l->t('Canada / Atikokan')); ?></option>
					<option value="America/Blanc-Sablon"><?php p($l->t('Canada / Blanc-Sablon')); ?></option>
					<option value="America/Cambridge_Bay"><?php p($l->t('Canada / Cambridge Bay')); ?></option>
					<option value="America/Dawson"><?php p($l->t('Canada / Dawson')); ?></option>
					<option value="America/Dawson_Creek"><?php p($l->t('Canada / Dawson Creek')); ?></option>
					<option value="America/Edmonton"><?php p($l->t('Canada / Edmonton')); ?></option>
					<option value="America/Glace_Bay"><?php p($l->t('Canada / Glace Bay')); ?></option>
					<option value="America/Goose_Bay"><?php p($l->t('Canada / Goose Bay')); ?></option>
					<option value="America/Halifax"><?php p($l->t('Canada / Halifax')); ?></option>
					<option value="America/Inuvik"><?php p($l->t('Canada / Inuvik')); ?></option>
					<option value="America/Iqaluit"><?php p($l->t('Canada / Iqaluit')); ?></option>
					<option value="America/Moncton"><?php p($l->t('Canada / Moncton')); ?></option>
					<option value="America/Nipigon"><?php p($l->t('Canada / Nipigon')); ?></option>
					<option value="America/Pangnirtung"><?php p($l->t('Canada / Pangnirtung')); ?></option>
					<option value="America/Rainy_River"><?php p($l->t('Canada / Rainy River')); ?></option>
					<option value="America/Rankin_Inlet"><?php p($l->t('Canada / Rankin Inlet')); ?></option>
					<option value="America/Regina"><?php p($l->t('Canada / Regina')); ?></option>
					<option value="America/Resolute"><?php p($l->t('Canada / Resolute')); ?></option>
					<option value="America/St_Johns"><?php p($l->t('Canada / St Johns')); ?></option>
					<option value="America/Swift_Current"><?php p($l->t('Canada / Swift Current')); ?></option>
					<option value="America/Thunder_Bay"><?php p($l->t('Canada / Thunder Bay')); ?></option>
					<option value="America/Toronto"><?php p($l->t('Canada / Toronto')); ?></option>
					<option value="America/Vancouver"><?php p($l->t('Canada / Vancouver')); ?></option>
					<option value="America/Whitehorse"><?php p($l->t('Canada / Whitehorse')); ?></option>
					<option value="America/Winnipeg"><?php p($l->t('Canada / Winnipeg')); ?></option>
					<option value="America/Yellowknife"><?php p($l->t('Canada / Yellowknife')); ?></option>
					<option value="Atlantic/Cape_Verde"><?php p($l->t('Cape Verde')); ?></option>
					<option value="America/Cayman"><?php p($l->t('Cayman Islands / Cayman')); ?></option>
					<option value="Africa/Bangui"><?php p($l->t('Central African Republic / Bangui')); ?></option>
					<option value="Africa/Ndjamena"><?php p($l->t('Chad / Ndjamena')); ?></option>
					<option value="Pacific/Easter"><?php p($l->t('Chile / Easter')); ?></option>
					<option value="America/Santiago"><?php p($l->t('Chile / Santiago')); ?></option>
					<option value="Asia/Shanghai"><?php p($l->t('China / Shanghai')); ?></option>
					<option value="Asia/Urumqi"><?php p($l->t('China / Urumqi')); ?></option>
					<option value="Indian/Christmas"><?php p($l->t('Christmas Island / Christmas')); ?></option>
					<option value="Indian/Cocos"><?php p($l->t('Cocos (Keeling) Islands / Cocos')); ?></option>
					<option value="America/Bogota"><?php p($l->t('Colombia / Bogota')); ?></option>
					<option value="Indian/Comoro"><?php p($l->t('Comoros / Comoro')); ?></option>
					<option value="Africa/Brazzaville"><?php p($l->t('Congo / Brazzaville')); ?></option>
					<option value="Africa/Kinshasa"><?php p($l->t('Congo, The Democratic Republic of the / Kinshasa')); ?></option>
					<option value="Africa/Lubumbashi"><?php p($l->t('Congo, The Democratic Republic of the / Lubumbashi')); ?></option>
					<option value="Pacific/Rarotonga"><?php p($l->t('Cook Islands / Rarotonga')); ?></option>
					<option value="America/Costa_Rica"><?php p($l->t('Costa Rica')); ?></option>
					<option value="Europe/Zagreb"><?php p($l->t('Croatia / Zagreb')); ?></option>
					<option value="America/Havana"><?php p($l->t('Cuba / Havana')); ?></option>
					<option value="America/Curacao"><?php p($l->t('Curaçao / Curacao')); ?></option>
					<option value="Asia/Nicosia"><?php p($l->t('Cyprus / Nicosia')); ?></option>
					<option value="Europe/Prague"><?php p($l->t('Czech Republic / Prague')); ?></option>
					<option value="Africa/Abidjan"><?php p($l->t('Côte d\'Ivoire / Abidjan')); ?></option>
					<option value="Europe/Copenhagen"><?php p($l->t('Denmark / Copenhagen')); ?></option>
					<option value="Africa/Djibouti"><?php p($l->t('Djibouti')); ?></option>
					<option value="America/Dominica"><?php p($l->t('Dominica')); ?></option>
					<option value="America/Santo_Domingo"><?php p($l->t('Dominican Republic / Santo Domingo')); ?></option>
					<option value="Pacific/Galapagos"><?php p($l->t('Ecuador / Galapagos')); ?></option>
					<option value="America/Guayaquil"><?php p($l->t('Ecuador / Guayaquil')); ?></option>
					<option value="Africa/Cairo"><?php p($l->t('Egypt / Cairo')); ?></option>
					<option value="America/El_Salvador"><?php p($l->t('El Salvador')); ?></option>
					<option value="Africa/Malabo"><?php p($l->t('Equatorial Guinea / Malabo')); ?></option>
					<option value="Africa/Asmara"><?php p($l->t('Eritrea / Asmara')); ?></option>
					<option value="Europe/Tallinn"><?php p($l->t('Estonia / Tallinn')); ?></option>
					<option value="Africa/Addis_Ababa"><?php p($l->t('Ethiopia / Addis Ababa')); ?></option>
					<option value="Atlantic/Stanley"><?php p($l->t('Falkland Islands (Malvinas) / Stanley')); ?></option>
					<option value="Atlantic/Faroe"><?php p($l->t('Faroe Islands / Faroe')); ?></option>
					<option value="Pacific/Fiji"><?php p($l->t('Fiji')); ?></option>
					<option value="Europe/Helsinki"><?php p($l->t('Finland / Helsinki')); ?></option>
					<option value="Europe/Paris"><?php p($l->t('France / Paris')); ?></option>
					<option value="America/Cayenne"><?php p($l->t('French Guiana / Cayenne')); ?></option>
					<option value="Pacific/Gambier"><?php p($l->t('French Polynesia / Gambier')); ?></option>
					<option value="Pacific/Marquesas"><?php p($l->t('French Polynesia / Marquesas')); ?></option>
					<option value="Pacific/Tahiti"><?php p($l->t('French Polynesia / Tahiti')); ?></option>
					<option value="Indian/Kerguelen"><?php p($l->t('French Southern Territories / Kerguelen')); ?></option>
					<option value="Africa/Libreville"><?php p($l->t('Gabon / Libreville')); ?></option>
					<option value="Africa/Banjul"><?php p($l->t('Gambia / Banjul')); ?></option>
					<option value="Asia/Tbilisi"><?php p($l->t('Georgia / Tbilisi')); ?></option>
					<option value="Europe/Berlin"><?php p($l->t('Germany / Berlin')); ?></option>
					<option value="Europe/Stuttgart"><?php p($l->t('Germany / Stuttgart')); ?></option>
					<option value="Africa/Accra"><?php p($l->t('Ghana / Accra')); ?></option>
					<option value="Europe/Gibraltar"><?php p($l->t('Gibraltar')); ?></option>
					<option value="Europe/Athens"><?php p($l->t('Greece / Athens')); ?></option>
					<option value="America/Danmarkshavn"><?php p($l->t('Greenland / Danmarkshavn')); ?></option>
					<option value="America/Godthab"><?php p($l->t('Greenland / Godthab')); ?></option>
					<option value="America/Scoresbysund"><?php p($l->t('Greenland / Scoresbysund')); ?></option>
					<option value="America/Thule"><?php p($l->t('Greenland / Thule')); ?></option>
					<option value="America/Grenada"><?php p($l->t('Grenada')); ?></option>
					<option value="America/Guadeloupe"><?php p($l->t('Guadeloupe')); ?></option>
					<option value="Pacific/Guam"><?php p($l->t('Guam')); ?></option>
					<option value="America/Guatemala"><?php p($l->t('Guatemala')); ?></option>
					<option value="Europe/Guernsey"><?php p($l->t('Guernsey')); ?></option>
					<option value="Africa/Conakry"><?php p($l->t('Guinea / Conakry')); ?></option>
					<option value="Africa/Bissau"><?php p($l->t('Guinea-Bissau / Bissau')); ?></option>
					<option value="America/Guyana"><?php p($l->t('Guyana')); ?></option>
					<option value="America/Port-au-Prince"><?php p($l->t('Haiti / Port-au-Prince')); ?></option>
					<option value="Europe/Vatican"><?php p($l->t('Holy See (Vatican City State) / Vatican')); ?></option>
					<option value="America/Tegucigalpa"><?php p($l->t('Honduras / Tegucigalpa')); ?></option>
					<option value="Asia/Hong_Kong"><?php p($l->t('Hong Kong')); ?></option>
					<option value="Europe/Budapest"><?php p($l->t('Hungary / Budapest')); ?></option>
					<option value="Atlantic/Reykjavik"><?php p($l->t('Iceland / Reykjavik')); ?></option>
					<option value="Asia/Kolkata"><?php p($l->t('India / Kolkata')); ?></option>
					<option value="Asia/Jakarta"><?php p($l->t('Indonesia / Jakarta')); ?></option>
					<option value="Asia/Jayapura"><?php p($l->t('Indonesia / Jayapura')); ?></option>
					<option value="Asia/Makassar"><?php p($l->t('Indonesia / Makassar')); ?></option>
					<option value="Asia/Pontianak"><?php p($l->t('Indonesia / Pontianak')); ?></option>
					<option value="Asia/Tehran"><?php p($l->t('Iran, Islamic Republic of / Tehran')); ?></option>
					<option value="Asia/Baghdad"><?php p($l->t('Iraq / Baghdad')); ?></option>
					<option value="Europe/Dublin"><?php p($l->t('Ireland / Dublin')); ?></option>
					<option value="Europe/Isle_of_Man"><?php p($l->t('Isle of Man')); ?></option>
					<option value="Asia/Jerusalem"><?php p($l->t('Israel / Jerusalem')); ?></option>
					<option value="Europe/Rome"><?php p($l->t('Italy / Rome')); ?></option>
					<option value="America/Jamaica"><?php p($l->t('Jamaica')); ?></option>
					<option value="Asia/Tokyo"><?php p($l->t('Japan / Tokyo')); ?></option>
					<option value="Europe/Jersey"><?php p($l->t('Jersey')); ?></option>
					<option value="Asia/Amman"><?php p($l->t('Jordan / Amman')); ?></option>
					<option value="Asia/Almaty"><?php p($l->t('Kazakhstan / Almaty')); ?></option>
					<option value="Asia/Aqtau"><?php p($l->t('Kazakhstan / Aqtau')); ?></option>
					<option value="Asia/Aqtobe"><?php p($l->t('Kazakhstan / Aqtobe')); ?></option>
					<option value="Asia/Oral"><?php p($l->t('Kazakhstan / Oral')); ?></option>
					<option value="Asia/Qyzylorda"><?php p($l->t('Kazakhstan / Qyzylorda')); ?></option>
					<option value="Africa/Nairobi"><?php p($l->t('Kenya / Nairobi')); ?></option>
					<option value="Pacific/Enderbury"><?php p($l->t('Kiribati / Enderbury')); ?></option>
					<option value="Pacific/Kiritimati"><?php p($l->t('Kiribati / Kiritimati')); ?></option>
					<option value="Pacific/Tarawa"><?php p($l->t('Kiribati / Tarawa')); ?></option>
					<option value="Asia/Pyongyang"><?php p($l->t('Korea, Democratic People\'s Republic of / Pyongyang')); ?></option>
					<option value="Asia/Seoul"><?php p($l->t('Korea, Republic of / Seoul')); ?></option>
					<option value="Asia/Kuwait"><?php p($l->t('Kuwait')); ?></option>
					<option value="Asia/Bishkek"><?php p($l->t('Kyrgyzstan / Bishkek')); ?></option>
					<option value="Asia/Vientiane"><?php p($l->t('Lao People\'s Democratic Republic / Vientiane')); ?></option>
					<option value="Europe/Riga"><?php p($l->t('Latvia / Riga')); ?></option>
					<option value="Asia/Beirut"><?php p($l->t('Lebanon / Beirut')); ?></option>
					<option value="Africa/Maseru"><?php p($l->t('Lesotho / Maseru')); ?></option>
					<option value="Africa/Monrovia"><?php p($l->t('Liberia / Monrovia')); ?></option>
					<option value="Africa/Tripoli"><?php p($l->t('Libya / Tripoli')); ?></option>
					<option value="Europe/Vaduz"><?php p($l->t('Liechtenstein / Vaduz')); ?></option>
					<option value="Europe/Vilnius"><?php p($l->t('Lithuania / Vilnius')); ?></option>
					<option value="Europe/Luxembourg"><?php p($l->t('Luxembourg')); ?></option>
					<option value="Asia/Macau"><?php p($l->t('Macao / Macau')); ?></option>
					<option value="Europe/Skopje"><?php p($l->t('Macedonia, Republic of / Skopje')); ?></option>
					<option value="Indian/Antananarivo"><?php p($l->t('Madagascar / Antananarivo')); ?></option>
					<option value="Africa/Blantyre"><?php p($l->t('Malawi / Blantyre')); ?></option>
					<option value="Asia/Kuala_Lumpur"><?php p($l->t('Malaysia / Kuala Lumpur')); ?></option>
					<option value="Asia/Kuching"><?php p($l->t('Malaysia / Kuching')); ?></option>
					<option value="Indian/Maldives"><?php p($l->t('Maldives')); ?></option>
					<option value="Africa/Bamako"><?php p($l->t('Mali / Bamako')); ?></option>
					<option value="Europe/Malta"><?php p($l->t('Malta')); ?></option>
					<option value="Pacific/Kwajalein"><?php p($l->t('Marshall Islands / Kwajalein')); ?></option>
					<option value="Pacific/Majuro"><?php p($l->t('Marshall Islands / Majuro')); ?></option>
					<option value="America/Martinique"><?php p($l->t('Martinique')); ?></option>
					<option value="Africa/Nouakchott"><?php p($l->t('Mauritania / Nouakchott')); ?></option>
					<option value="Indian/Mauritius"><?php p($l->t('Mauritius')); ?></option>
					<option value="Indian/Mayotte"><?php p($l->t('Mayotte')); ?></option>
					<option value="America/Cancun"><?php p($l->t('Mexico / Cancun')); ?></option>
					<option value="America/Chihuahua"><?php p($l->t('Mexico / Chihuahua')); ?></option>
					<option value="America/Hermosillo"><?php p($l->t('Mexico / Hermosillo')); ?></option>
					<option value="America/Mazatlan"><?php p($l->t('Mexico / Mazatlan')); ?></option>
					<option value="America/Merida"><?php p($l->t('Mexico / Merida')); ?></option>
					<option value="America/Monterrey"><?php p($l->t('Mexico / Monterrey')); ?></option>
					<option value="America/Tijuana"><?php p($l->t('Mexico / Tijuana')); ?></option>
					<option value="America/Mexico_City"><?php p($l->t('Mexico City')); ?></option>
					<option value="Pacific/Chuuk"><?php p($l->t('Micronesia, Federated States of / Chuuk')); ?></option>
					<option value="Pacific/Kosrae"><?php p($l->t('Micronesia, Federated States of / Kosrae')); ?></option>
					<option value="Pacific/Pohnpei"><?php p($l->t('Micronesia, Federated States of / Pohnpei')); ?></option>
					<option value="Europe/Chisinau"><?php p($l->t('Moldova, Republic of / Chisinau')); ?></option>
					<option value="Europe/Monaco"><?php p($l->t('Monaco')); ?></option>
					<option value="Asia/Choibalsan"><?php p($l->t('Mongolia / Choibalsan')); ?></option>
					<option value="Asia/Hovd"><?php p($l->t('Mongolia / Hovd')); ?></option>
					<option value="Asia/Ulaanbaatar"><?php p($l->t('Mongolia / Ulaanbaatar')); ?></option>
					<option value="Europe/Podgorica"><?php p($l->t('Montenegro / Podgorica')); ?></option>
					<option value="America/Montserrat"><?php p($l->t('Montserrat')); ?></option>
					<option value="Africa/Casablanca"><?php p($l->t('Morocco / Casablanca')); ?></option>
					<option value="Africa/Maputo"><?php p($l->t('Mozambique / Maputo')); ?></option>
					<option value="Asia/Rangoon"><?php p($l->t('Myanmar / Rangoon')); ?></option>
					<option value="Africa/Windhoek"><?php p($l->t('Namibia / Windhoek')); ?></option>
					<option value="Pacific/Nauru"><?php p($l->t('Nauru')); ?></option>
					<option value="Asia/Kathmandu"><?php p($l->t('Nepal / Kathmandu')); ?></option>
					<option value="Europe/Amsterdam"><?php p($l->t('Netherlands / Amsterdam')); ?></option>
					<option value="Pacific/Noumea"><?php p($l->t('New Caledonia / Noumea')); ?></option>
					<option value="Pacific/Auckland"><?php p($l->t('New Zealand / Auckland')); ?></option>
					<option value="Pacific/Chatham"><?php p($l->t('New Zealand / Chatham')); ?></option>
					<option value="America/Managua"><?php p($l->t('Nicaragua / Managua')); ?></option>
					<option value="Africa/Niamey"><?php p($l->t('Niger / Niamey')); ?></option>
					<option value="Africa/Lagos"><?php p($l->t('Nigeria / Lagos')); ?></option>
					<option value="Pacific/Niue"><?php p($l->t('Niue')); ?></option>
					<option value="Pacific/Norfolk"><?php p($l->t('Norfolk Island / Norfolk')); ?></option>
					<option value="Pacific/Saipan"><?php p($l->t('Northern Mariana Islands / Saipan')); ?></option>
					<option value="Europe/Oslo"><?php p($l->t('Norway / Oslo')); ?></option>
					<option value="Asia/Muscat"><?php p($l->t('Oman / Muscat')); ?></option>
					<option value="Asia/Karachi"><?php p($l->t('Pakistan / Karachi')); ?></option>
					<option value="Pacific/Palau"><?php p($l->t('Palau')); ?></option>
					<option value="Asia/Gaza"><?php p($l->t('Palestinian Territory, Occupied / Gaza')); ?></option>
					<option value="America/Panama"><?php p($l->t('Panama')); ?></option>
					<option value="Pacific/Port_Moresby"><?php p($l->t('Papua New Guinea / Port Moresby')); ?></option>
					<option value="America/Asuncion"><?php p($l->t('Paraguay / Asuncion')); ?></option>
					<option value="America/Lima"><?php p($l->t('Peru / Lima')); ?></option>
					<option value="Asia/Manila"><?php p($l->t('Philippines / Manila')); ?></option>
					<option value="Pacific/Pitcairn"><?php p($l->t('Pitcairn')); ?></option>
					<option value="Europe/Warsaw"><?php p($l->t('Poland / Warsaw')); ?></option>
					<option value="Atlantic/Azores"><?php p($l->t('Portugal / Azores')); ?></option>
					<option value="Europe/Lisbon"><?php p($l->t('Portugal / Lisbon')); ?></option>
					<option value="Atlantic/Madeira"><?php p($l->t('Portugal / Madeira')); ?></option>
					<option value="America/Puerto_Rico"><?php p($l->t('Puerto Rico')); ?></option>
					<option value="Asia/Qatar"><?php p($l->t('Qatar')); ?></option>
					<option value="Europe/Bucharest"><?php p($l->t('Romania / Bucharest')); ?></option>
					<option value="Asia/Anadyr"><?php p($l->t('Russian Federation / Anadyr')); ?></option>
					<option value="Asia/Irkutsk"><?php p($l->t('Russian Federation / Irkutsk')); ?></option>
					<option value="Europe/Kaliningrad"><?php p($l->t('Russian Federation / Kaliningrad')); ?></option>
					<option value="Asia/Kamchatka"><?php p($l->t('Russian Federation / Kamchatka')); ?></option>
					<option value="Asia/Krasnoyarsk"><?php p($l->t('Russian Federation / Krasnoyarsk')); ?></option>
					<option value="Asia/Magadan"><?php p($l->t('Russian Federation / Magadan')); ?></option>
					<option value="Europe/Moscow"><?php p($l->t('Russian Federation / Moscow')); ?></option>
					<option value="Asia/Novosibirsk"><?php p($l->t('Russian Federation / Novosibirsk')); ?></option>
					<option value="Asia/Omsk"><?php p($l->t('Russian Federation / Omsk')); ?></option>
					<option value="Asia/Sakhalin"><?php p($l->t('Russian Federation / Sakhalin')); ?></option>
					<option value="Europe/Samara"><?php p($l->t('Russian Federation / Samara')); ?></option>
					<option value="Europe/Simferopol"><?php p($l->t('Russian Federation / Simferopol')); ?></option>
					<option value="Asia/Vladivostok"><?php p($l->t('Russian Federation / Vladivostok')); ?></option>
					<option value="Europe/Volgograd"><?php p($l->t('Russian Federation / Volgograd')); ?></option>
					<option value="Asia/Yakutsk"><?php p($l->t('Russian Federation / Yakutsk')); ?></option>
					<option value="Asia/Yekaterinburg"><?php p($l->t('Russian Federation / Yekaterinburg')); ?></option>
					<option value="Africa/Kigali"><?php p($l->t('Rwanda / Kigali')); ?></option>
					<option value="Indian/Reunion"><?php p($l->t('Réunion / Reunion')); ?></option>
					<option value="America/St_Barthelemy"><?php p($l->t('Saint Barthélemy / St Barthelemy')); ?></option>
					<option value="Atlantic/St_Helena"><?php p($l->t('Saint Helena, Ascension and Tristan da Cunha / St Helena')); ?></option>
					<option value="America/St_Kitts"><?php p($l->t('Saint Kitts and Nevis / St Kitts')); ?></option>
					<option value="America/St_Lucia"><?php p($l->t('Saint Lucia / St Lucia')); ?></option>
					<option value="America/Marigot"><?php p($l->t('Saint Martin (French part) / Marigot')); ?></option>
					<option value="America/Miquelon"><?php p($l->t('Saint Pierre and Miquelon / Miquelon')); ?></option>
					<option value="America/St_Vincent"><?php p($l->t('Saint Vincent and the Grenadines / St Vincent')); ?></option>
					<option value="Pacific/Apia"><?php p($l->t('Samoa / Apia')); ?></option>
					<option value="Europe/San_Marino"><?php p($l->t('San Marino')); ?></option>
					<option value="Africa/Sao_Tome"><?php p($l->t('Sao Tome and Principe / Sao Tome')); ?></option>
					<option value="Asia/Riyadh"><?php p($l->t('Saudi Arabia / Riyadh')); ?></option>
					<option value="Africa/Dakar"><?php p($l->t('Senegal / Dakar')); ?></option>
					<option value="Europe/Belgrade"><?php p($l->t('Serbia / Belgrade')); ?></option>
					<option value="Indian/Mahe"><?php p($l->t('Seychelles / Mahe')); ?></option>
					<option value="Africa/Freetown"><?php p($l->t('Sierra Leone / Freetown')); ?></option>
					<option value="Asia/Singapore"><?php p($l->t('Singapore')); ?></option>
					<option value="Europe/Bratislava"><?php p($l->t('Slovakia / Bratislava')); ?></option>
					<option value="Europe/Ljubljana"><?php p($l->t('Slovenia / Ljubljana')); ?></option>
					<option value="Pacific/Guadalcanal"><?php p($l->t('Solomon Islands / Guadalcanal')); ?></option>
					<option value="Africa/Mogadishu"><?php p($l->t('Somalia / Mogadishu')); ?></option>
					<option value="Africa/Johannesburg"><?php p($l->t('South Africa / Johannesburg')); ?></option>
					<option value="Atlantic/South_Georgia"><?php p($l->t('South Georgia and the South Sandwich Islands / South Georgia')); ?></option>
					<option value="Europe/Barcelona"><?php p($l->t('Spain / Barcelona')); ?></option>
					<option value="Atlantic/Canary"><?php p($l->t('Spain / Canary')); ?></option>
					<option value="Africa/Ceuta"><?php p($l->t('Spain / Ceuta')); ?></option>
					<option value="Europe/Madrid"><?php p($l->t('Spain / Madrid')); ?></option>
					<option value="Asia/Colombo"><?php p($l->t('Sri Lanka / Colombo')); ?></option>
					<option value="Africa/Khartoum"><?php p($l->t('Sudan / Khartoum')); ?></option>
					<option value="America/Paramaribo"><?php p($l->t('Suriname / Paramaribo')); ?></option>
					<option value="Arctic/Longyearbyen"><?php p($l->t('Svalbard and Jan Mayen / Longyearbyen')); ?></option>
					<option value="Africa/Mbabane"><?php p($l->t('Swaziland / Mbabane')); ?></option>
					<option value="Europe/Stockholm"><?php p($l->t('Sweden / Stockholm')); ?></option>
					<option value="Europe/Zurich"><?php p($l->t('Switzerland / Zurich')); ?></option>
					<option value="Asia/Damascus"><?php p($l->t('Syrian Arab Republic / Damascus')); ?></option>
					<option value="Asia/Taipei"><?php p($l->t('Taiwan / Taipei')); ?></option>
					<option value="Asia/Dushanbe"><?php p($l->t('Tajikistan / Dushanbe')); ?></option>
					<option value="Africa/Dar_es_Salaam"><?php p($l->t('Tanzania, United Republic of / Dar es Salaam')); ?></option>
					<option value="Asia/Bangkok"><?php p($l->t('Thailand / Bangkok')); ?></option>
					<option value="Asia/Dili"><?php p($l->t('Timor-Leste / Dili')); ?></option>
					<option value="Africa/Lome"><?php p($l->t('Togo / Lome')); ?></option>
					<option value="Pacific/Fakaofo"><?php p($l->t('Tokelau / Fakaofo')); ?></option>
					<option value="Pacific/Tongatapu"><?php p($l->t('Tongatapu')); ?></option>
					<option value="America/Port_of_Spain"><?php p($l->t('Trinidad and Tobago / Port of Spain')); ?></option>
					<option value="Africa/Tunis"><?php p($l->t('Tunisia / Tunis')); ?></option>
					<option value="Europe/Istanbul"><?php p($l->t('Turkey / Istanbul')); ?></option>
					<option value="Asia/Ashgabat"><?php p($l->t('Turkmenistan / Ashgabat')); ?></option>
					<option value="America/Grand_Turk"><?php p($l->t('Turks and Caicos Islands / Grand Turk')); ?></option>
					<option value="Pacific/Funafuti"><?php p($l->t('Tuvalu / Funafuti')); ?></option>
					<option value="UTC"><?php p($l->t('UTC')); ?></option>
					<option value="Africa/Kampala"><?php p($l->t('Uganda / Kampala')); ?></option>
					<option value="Europe/Kiev"><?php p($l->t('Ukraine / Kiev')); ?></option>
					<option value="Europe/Uzhgorod"><?php p($l->t('Ukraine / Uzhgorod')); ?></option>
					<option value="Europe/Zaporozhye"><?php p($l->t('Ukraine / Zaporozhye')); ?></option>
					<option value="Asia/Dubai"><?php p($l->t('United Arab Emirates / Dubai')); ?></option>
					<option value="Europe/London"><?php p($l->t('United Kingdom / London')); ?></option>
					<option value="America/Adak"><?php p($l->t('United States / Adak')); ?></option>
					<option value="America/Anchorage"><?php p($l->t('United States / Anchorage')); ?></option>
					<option value="America/Boise"><?php p($l->t('United States / Boise')); ?></option>
					<option value="America/Chicago"><?php p($l->t('United States / Chicago')); ?></option>
					<option value="America/Dallas"><?php p($l->t('United States / Dallas')); ?></option>
					<option value="America/Denver"><?php p($l->t('United States / Denver')); ?></option>
					<option value="America/Detroit"><?php p($l->t('United States / Detroit')); ?></option>
					<option value="Pacific/Honolulu"><?php p($l->t('United States / Honolulu')); ?></option>
					<option value="America/Indiana/Indianapolis"><?php p($l->t('United States / Indiana / Indianapolis')); ?></option>
					<option value="America/Indiana/Knox"><?php p($l->t('United States / Indiana / Knox')); ?></option>
					<option value="America/Indiana/Marengo"><?php p($l->t('United States / Indiana / Marengo')); ?></option>
					<option value="America/Indiana/Petersburg"><?php p($l->t('United States / Indiana / Petersburg')); ?></option>
					<option value="America/Indiana/Tell_City"><?php p($l->t('United States / Indiana / Tell City')); ?></option>
					<option value="America/Indiana/Vevay"><?php p($l->t('United States / Indiana / Vevay')); ?></option>
					<option value="America/Indiana/Vincennes"><?php p($l->t('United States / Indiana / Vincennes')); ?></option>
					<option value="America/Indiana/Winamac"><?php p($l->t('United States / Indiana / Winamac')); ?></option>
					<option value="America/Juneau"><?php p($l->t('United States / Juneau')); ?></option>
					<option value="America/Kentucky/Louisville"><?php p($l->t('United States / Kentucky / Louisville')); ?></option>
					<option value="America/Kentucky/Monticello"><?php p($l->t('United States / Kentucky / Monticello')); ?></option>
					<option value="America/Los_Angeles"><?php p($l->t('United States / Los Angeles')); ?></option>
					<option value="America/Menominee"><?php p($l->t('United States / Menominee')); ?></option>
					<option value="America/New_York"><?php p($l->t('United States / New York')); ?></option>
					<option value="America/Nome"><?php p($l->t('United States / Nome')); ?></option>
					<option value="America/North_Dakota/Center"><?php p($l->t('United States / North Dakota / Center')); ?></option>
					<option value="America/North_Dakota/New_Salem"><?php p($l->t('United States / North Dakota / New Salem')); ?></option>
					<option value="America/Phoenix"><?php p($l->t('United States / Phoenix')); ?></option>
					<option value="America/Washington_DC"><?php p($l->t('United States / Washington DC')); ?></option>
					<option value="America/Yakutat"><?php p($l->t('United States / Yakutat')); ?></option>
					<option value="America/Montevideo"><?php p($l->t('Uruguay / Montevideo')); ?></option>
					<option value="Asia/Samarkand"><?php p($l->t('Uzbekistan / Samarkand')); ?></option>
					<option value="Asia/Tashkent"><?php p($l->t('Uzbekistan / Tashkent')); ?></option>
					<option value="Pacific/Efate"><?php p($l->t('Vanuatu / Efate')); ?></option>
					<option value="America/Caracas"><?php p($l->t('Venezuela, Bolivarian Republic of / Caracas')); ?></option>
					<option value="Asia/Ho_Chi_Minh"><?php p($l->t('Viet Nam / Ho Chi Minh')); ?></option>
					<option value="America/Tortola"><?php p($l->t('Virgin Islands, British / Tortola')); ?></option>
					<option value="America/St_Thomas"><?php p($l->t('Virgin Islands, U.S. / St Thomas')); ?></option>
					<option value="Pacific/Wallis"><?php p($l->t('Wallis and Futuna / Wallis')); ?></option>
					<option value="Africa/El_Aaiun"><?php p($l->t('Western Sahara / El Aaiun')); ?></option>
					<option value="Asia/Aden"><?php p($l->t('Yemen / Aden')); ?></option>
					<option value="Africa/Lusaka"><?php p($l->t('Zambia / Lusaka')); ?></option>
					<option value="Africa/Harare"><?php p($l->t('Zimbabwe / Harare')); ?></option>
				</select>
			</p>
			<?php }?>
		<input type="submit" id="submit" value="<?php p($l->t('Create account')); ?>" />
	</fieldset>
</form>
