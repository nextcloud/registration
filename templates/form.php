<?php
\OCP\Util::addStyle('registration', 'style');
\OCP\Util::addScript('registration', 'moment-timezone-with-data');
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
				<img id="fullname-icon" class="svg" src="<?php print_unescaped(image_path('', 'places/contacts-dark.svg')); ?>" alt=""/>
				<label for="fullname" class="infield"><?php p($l->t('Full name'));?></label>
			</p>
			<?php }?>

			<?php if ($_['showcompany'] === "yes") {?>
			<p class="groupmiddle">
				<input type="text" id="company" name="company" value="<?php echo !empty($_['entered_data']['company']) ? $_['entered_data']['company'] : ''; ?>" placeholder="<?php p($l->t('Company')); ?>" />
				<label class="infield"><?php p($l->t('Company'));?></label>
				<img id="company-icon" class="svg" src="<?php print_unescaped(image_path('', 'places/contacts-dark.svg')); ?>" alt=""/>
			</p>
			<?php }?>
			<?php if ($_['showphoneno'] === "yes") {?>
			<p class="groupmiddle">
				<input type="text" id="phoneno" name="phoneno" value="<?php echo !empty($_['entered_data']['phoneno']) ? $_['entered_data']['phoneno'] : ''; ?>" placeholder="<?php p($l->t('Phone Number')); ?>" />
				<label class="infield"><?php p($l->t('Phone Number'));?></label>
				<img id="phoneno-icon" class="svg" src="<?php print_unescaped(image_path('', 'places/contacts-dark.svg')); ?>" alt=""/>
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
				<img id="timezone-icon" class="svg" src="<?php print_unescaped(image_path('', 'places/calendar-dark.svg')); ?>" alt=""/>
				<label for="timezone" class="msg"><?php p($l->t('Timezone'));?></label>
				<select id="timezone" name="timezone" class="selfield" data-value="<?php echo !empty($_['entered_data']['timezone']) ? $_['entered_data']['timezone'] : ''; ?>" placeholder="<?php p($l->t('Timezone')); ?>" ></select>
			</p>
			<?php }?>
		<input type="submit" id="submit" value="<?php p($l->t('Create account')); ?>" />
	</fieldset>
</form>
