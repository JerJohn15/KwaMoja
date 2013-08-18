<?php

ini_set('max_execution_time', 0);
session_name('kwamoja_installation');
session_start();

/* If the installer is just starting */
if (isset($_GET['New'])) {
	unset($_SESSION['Installer']);
	$_SESSION['Installer']['Language'] = 'en_GB.utf8';
	$_SESSION['Installer']['DBMS'] = 'mariadb';
	$_SESSION['Installer']['DBExt'] = 1;
}

if (isset($_POST['DBExt'])) {
	$_SESSION['Installer']['DBExt'] = $_POST['DBExt'];
}

if (isset($_POST['DBMS'])) {
	$_SESSION['Installer']['DBMS'] = $_POST['DBMS'];
}

if (isset($_POST['Email'])) {
	$_SESSION['Installer']['Email'] = $_POST['Email'];
}

/*
 * KwaMoja Installer
 * Step 1: Licence acknowledgement and Choose Language
 * Step 2: Check requirements
 * Step 3: Database connection
 * Step 4: Company details
 * Step 5: Administrator account details
 * Step 6: Finalise
 **/

/* Send the HTTP headers */

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>' . _('KwaMoja Installer') . '</title>
			<link rel="stylesheet" type="text/css" href="installer.css" />
		</head>';

echo '<body>
		<div id="CanvasDiv">';

/* Set error reprting level
 * = -1 for development
 * = 1 for production
 */
error_reporting(-1);

if (isset($_POST['SystemValid'])) {
	//If all of them are OK, then users can input the data of database etc
	//Show the database
	if (!empty($MysqlExt)) {
		DbConfig($_SESSION['Installer']['Language'], $_SESSION['Installer']['DBMS']);
	} else {
		DbConfig($_SESSION['Installer']['Language']);
	}
}

/* Get the php-gettext function.
 * When users have not select the language, we guess user's language via
 * the http header information. once the user has select their lanugage,
 * use the language user selected
 */
if (!isset($_POST['Language'])) {
	if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { //get users preferred language
		$ClientLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		switch ($ClientLang) {
			case 'ar':
				$Language = 'ar_EG.utf8';
				break;
			case 'cs':
				$Language = 'cs_CZ.utf8';
				break;
			case 'de':
				$Language = 'de_DE.utf8';
				break;
			case 'el':
				$Language = 'el_GR.utf8';
				break;
			case 'en':
				$Language = 'en_GB.utf8';
				break;
			case 'es':
				$Language = 'es_ES.utf8';
				break;
			case 'et':
				$Language = 'et_EE.utf8';
				break;
			case 'fa':
				$Language = 'fa_IR.utf8';
				break;
			case 'fr':
				$Langauge = 'fr_CA.utf8';
				break;
			case 'hi':
				$Language = 'hi_IN.utf8';
				break;
			case 'hr':
				$Language = 'hr_HR.utf8';
				break;
			case 'hu':
				$Language = 'hu_HU.utf8';
				break;
			case 'id':
				$Language = 'id_ID.utf8';
				break;
			case 'it':
				$Language = 'it_IT.utf8';
				break;
			case 'ja':
				$Language = 'ja_JP.utf8';
				break;
			case 'lv':
				$Language = 'lv_LV.utf8';
				break;
			case 'nl':
				$Language = 'nl_NL.utf8';
				break;
			case 'pl':
				$Language = 'pl_PL.utf8';
				break;
			case 'pt':
				$Language = 'pt-PT.utf8';
				break;
			case 'ro':
				$Language = 'ro_RO.utf8';
				break;
			case 'ru':
				$Language = 'ru_RU.utf8';
				break;
			case 'sq':
				$Language = 'sq_AL.utf8';
				break;
			case 'sv':
				$Language = 'sv_SE.utf8';
				break;
			case 'sw':
				$Language = 'sw_KE.utf8';
				break;
			case 'tr':
				$Language = 'tr_TR.utf8';
				break;
			case 'vi':
				$Language = 'vi_VN.utf8';
				break;
			case 'zh':
				$Language = 'zh_CN.utf8';
				break;
			default:
				$Language = 'en_GB.utf8';

		}
		$_SESSION['Installer']['Language'] = $Language;
		if (isset($_SESSION['Language'])) {
			unset($_SESSION['Language']);
		}

	} else {
		$Language = 'en_GB.utf8';
		$_SESSION['Installer']['Language'] = 'en_US.utf8';
	}
} else {
	$_SESSION['Installer']['Language'] = $_POST['Language'];
}

$PathPrefix = '../'; //To make the LanguageSetup.php script run properly
$DefaultLanguage = $_SESSION['Installer']['Language'];
include('../includes/LanguageSetup.php');
include('../includes/MiscFunctions.php');

//prevent the installation file from running again

if (file_exists('../config.php') or file_exists('../Config.php')) {
	echo '<div class="error">' . _('It seems that the system has been already installed. If you want to install again, please remove the config.php file first') . '</div>';
	exit;
}

if (isset($_POST['Install'])) { //confirm the final install data, the last validation step before we submit the data

	echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';
	echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	//first do necessary validation
	//Since user may have changed the DatabaseName so we need check it again
	$InputError = 0;
	if (!empty($_POST['CompanyName'])) {
		//validate the Database name setting
		//The database name cannot contains illegal characters such as "/","\","." etc
		//and it should not contains illegal characters as file name such as "?""%"<"">"" " etc

		if (preg_match(',[/\\\?%:\|<>\.\s"]+,', $_POST['CompanyName'])) {
			$InputError = 1;
			echo '<div class="error">' . _('The database name should not contains illegal characters such as "/\?%:|<>" blank etc') . '</div>';
		}
		$DatabaseName = strtolower($_POST['CompanyName']);
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('The database name should not be empty') . '</div>';
	}
	if (!empty($_POST['TimeZone'])) {
		if (preg_match(',(Etc|Pacific|India|Europe|Australia|Atlantic|Asia|America|Africa)/[A-Z]{1}[a-zA-Z\-_/]+,', $_POST['TimeZone'])) {
			$TimeZone = $_POST['TimeZone'];
		} else {
			$InputError = 1;
			echo '<div class="error">' . _('The timezone must be legal') . '</div>';
		}
	}
	if (!empty($_POST['Demo']) and $_POST['Demo'] == 'on') {
		if (strtolower($DatabaseName) == 'kwamojademo') { //user select to install the kwamojademo
			$OnlyDemo = 1;
		} else {
			$DualCompany = 1; //user choose to install the demo company and production environment
		}
	} else { //user only choose to install the new kwamoja company
		$NewCompany = 1;
	}
	if (!empty($_POST['Email']) and IsEmailAddress($_POST['Email'])) {
		$Email = trim($_POST['Email']);
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('You must enter a valid email address for the Administrator.') . '</div>';
	}
	if (!empty($_POST['KwaMojaPassword']) and !empty($_POST['PasswordConfirm']) and $_POST['KwaMojaPassword'] == $_POST['PasswordConfirm']) {
		$AdminPassword = $_POST['KwaMojaPassword'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('Please correct the password. The password is either blank, or the password check does not match.') . '</div>';

	}
	if (!empty($_POST['HostName'])) {
		// As HTTP_HOST is user input, ensure it only contains characters allowed
		// in hostnames. See RFC 952 (and RFC 2181).
		// $_SERVER['HTTP_HOST'] is lowercased here per specifications.
		$_POST['HostName'] = strtolower($_POST['HostName']);
		$HostValid = preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $_POST['HostName']);
		if ($HostValid) {
			$host = $_POST['HostName'];
		} else {
			echo '<div class="error">' . _('The Host Name is not a valid name.') . '</div>';
			exit;
		}

	} else {
		$InputError = 1;
		echo '<div class="error">' . _('The Host Name must not be empty.') . '</div>';
	}
	if (!empty($_POST['UserName']) and strlen($_POST['UserName']) <= 16) { // dbms user
		$DBUser = $_POST['UserName'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('The user name cannot be empty and length must not be over 16 characters.') . '</div>';
	}
	if (isset($_POST['Password'])) { // dbms password
		$DBPassword = $_POST['Password'];
	}
	if (!empty($_POST['DBMS'])) { //get the dbms connect extension
		$_SESSION['Installer']['DBMS'] = $_POST['DBMS'];
	}
	if (!empty($_POST['UserLanguage'])) {
		if (preg_match(',^[a-z]{2}_[A-Z]{2}.utf8$,', $_POST['UserLanguage'])) {
			$UserLanguage = $_POST['UserLanguage'];
		} else {
			$InputError = 1;
			echo '<div class="error">' . _('The user language defintion is not in the correct format') . '</div>';
		}
	}
	if (!empty($_FILES['LogoFile'])) { //We check the file upload situation
		if ($_FILES['LogoFile']['error'] == UPLOAD_ERR_INI_SIZE || $_FILES['LogoFile']['error'] == UPLOAD_ERR_FORM_SIZE) { //the file is over the php.ini limit or over the from limit
			$InputError = 1;
			if (upload_max_filesize < 0.01) {
				echo '<div class="error">' . ('The company logo file failed to upload due to it\'s size. The file was over the upload_max_filesize set in your php.ini configuration.') . '</div>';

			} else {
				echo '<div class="error">' . ('The logo file failed to upload as it was over 10KB size limit.') . '</div>';
			}

		} elseif ($_FILES['LogoFile']['error'] == UPLOAD_ERR_OK) { //The file has been successfully uploaded
			$File_Temp_Name = $_FILES['LogoFile']['tmp_name'];
		} elseif ($_FILES['LogoFile']['error'] == UPLOAD_ERR_NO_FILE) { //There are no file uploaded by users
			$File_To_Copy = 1;
		}

	}
	if (!empty($_POST['COA'])) {
		$COA = $_POST['COA'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . _('There is no COA file selected. Please select a file.') . '</div>';

	}
	if ($InputError == 1) { //return to the company configuration stage
		CompanySetup($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName);
	} else {
		//start to installation
		$Path_To_Root = '..';
		$Config_File = $Path_To_Root . '/config.php';
		if (!file_exists($Path_To_Root . '/companies/' . $DatabaseName)) {
			$CompanyDir = $Path_To_Root . '/companies/' . $DatabaseName;
			$Result = mkdir($CompanyDir);
			$Result = mkdir($CompanyDir . '/part_pics');
			$Result = mkdir($CompanyDir . '/EDI_Incoming_Orders');
			$Result = mkdir($CompanyDir . '/reports');
			$Result = mkdir($CompanyDir . '/EDI_Sent');
			$Result = mkdir($CompanyDir . '/EDI_Pending');
			$Result = mkdir($CompanyDir . '/reportwriter');
			$Result = mkdir($CompanyDir . '/pdf_append');
			$Result = mkdir($CompanyDir . '/FormDesigns');
			copy($Path_To_Root . '/companies/kwamoja/FormDesigns/GoodsReceived.xml', $CompanyDir . '/FormDesigns/GoodsReceived.xml');
			copy($Path_To_Root . '/companies/kwamoja/FormDesigns/PickingList.xml', $CompanyDir . '/FormDesigns/PickingList.xml');
			copy($Path_To_Root . '/companies/kwamoja/FormDesigns/PurchaseOrder.xml', $CompanyDir . '/FormDesigns/PurchaseOrder.xml');
			copy($Path_To_Root . '/companies/kwamoja/FormDesigns/Journal.xml', $CompanyDir . '/FormDesigns/Journal.xml');
			if (isset($File_Temp_Name)) {
				$Result = move_uploaded_file($File_Temp_Name, $CompanyDir . '/logo.jpg');

			} elseif (isset($File_To_Copy)) {
				$Result = copy($Path_To_Root . '/logo_server.jpg', $CompanyDir . '/logo.jpg');
			}
		} else {
			echo '<div class="error">' . _('This company name already exists') . '</div>';
			exit;
		}
		//$msg holds the text of the new config.php file
		$msg = "<?php\n\n";
		$msg .= "// User configurable variables\n";
		$msg .= "//---------------------------------------------------\n\n";
		$msg .= "//DefaultLanguage to use for the login screen and the setup of new users.\n";
		$msg .= "\$_SESSION['Installer']['Language'] = '" . $UserLanguage . "';\n\n";
		$msg .= "// Whether to display the demo login and password or not on the login screen\n";
		$msg .= "\$AllowDemoMode = FALSE;\n\n";
		$msg .= "// Connection information for the database\n";
		$msg .= "// \$host is the computer ip address or name where the database is located\n";
		$msg .= "// assuming that the webserver is also the sql server\n";
		$msg .= "\$host = '" . $host . "';\n\n";
		$msg .= "// assuming that the web server is also the sql server\n";
		$msg .= "\$DBType = '" . $_SESSION['Installer']['DBMS'] . "';\n";
		$msg .= "//assuming that the web server is also the sql server\n";
		$msg .= "\$DBUser = '" . $DBUser . "';\n";
		$msg .= "\$DBPassword = '" . $DBPassword . "';\n";
		$msg .= "// The timezone of the business - this allows the possibility of having;\n";
		$msg .= "date_default_timezone_set('" . $TimeZone . "');\n";
		$msg .= "putenv('TZ=" . $TimeZone . "');\n";
		$msg .= "\$AllowCompanySelectionBox = 'ShowSelectionBox';\n";
		$msg .= "//The system administrator name use the user input mail;\n";
		if (strtolower($_SESSION['Installer']['Email']) != 'admin@kwamoja.com') {
			$msg .= "\$SysAdminEmail = '" . $_SESSION['Installer']['Email'] . "';\n";
		}
		if (isset($NewCompany)) {
			$msg .= "\$DefaultCompany = '" . $DatabaseName . "';\n";
		} else {
			$msg .= "\$DefaultCompany = '" . $DatabaseName . "';\n";
		}
		$msg .= "\$SessionLifeTime = 3600;\n";
		$msg .= "\$MaximumExecutionTime = 120;\n";
		$msg .= "\$CryptFunction = 'sha1';\n";
		$msg .= "\$DefaultClock = 12;\n";
		$msg .= "\$RootPath = dirname(htmlspecialchars(\$_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));\n";
		$msg .= "if (isset(\$DirectoryLevelsDeep)){\n";
		$msg .= "   for (\$i=0;\$i<\$DirectoryLevelsDeep;\$i++){\n";
		$msg .= "		\$RootPath = mb_substr(\$RootPath,0, strrpos(\$RootPath,'/'));\n";
		$msg .= "	}\n";
		$msg .= "}\n";

		$msg .= "if (\$RootPath == '/' OR \$RootPath == '\\\') {\n";
		$msg .= "	\$RootPath = '';\n";
		$msg .= "}\n";
		$msg .= "error_reporting (E_ALL & ~E_NOTICE);\n";
		$msg .= "/* Make sure there is nothing - not even spaces after this last ?> */\n";
		$msg .= "?>";

		//write the config.php file since we have test the writability of the root path and companies,
		//there is little possibility that it will fail here. So just an warn if it is failed.
		if (!$zp = fopen($Path_To_Root . '/config.php', 'w')) {
			echo '<div class="error">' . _("Cannot open the configuration file") . $Config_File . '</div>';
		} else {
			if (!fwrite($zp, $msg)) {
				fclose($zp);
				echo '<div class="error">' . _("Cannot write to the configuration file") . $Config_File . '</div>';
			}
			//close file
			fclose($zp);
		}

		//Now it is the time to create the mysql data
		//Just get the data from $COA and read data from this file
		//At the mean time, we should check the user need demo database or not
		$sql = 'CREATE DATABASE IF NOT EXISTS `' . $DatabaseName . '`';
		if ($_SESSION['Installer']['DBMS'] == 'mysqli') {
			$db = mysqli_connect($host, $DBUser, $DBPassword);
			$result = mysqli_query($db, $sql);
			if (!$db) {
				echo '<div class="error">' . _('Failed to connect the database, the error is ') . mysqli_connect_error() . '</div>';
			}
		}
		if ($_SESSION['Installer']['DBMS'] == 'mysql') {
			$db = mysql_connect($host, $DBUser, $DBPassword);
			$result = mysql_query($db, $sql);
			if (!$db) {
				echo '<div class="error">' . _('Failed to connect the database, the error is ') . mysql_connect_error() . '</div>';
			}
		}
		if ($_SESSION['Installer']['DBMS'] == 'mariadb') {
			$db = mysqli_connect($host, $DBUser, $DBPassword);
			$result = mysqli_query($db, $sql);
			if (!$db) {
				echo '<div class="error">' . _('Failed to connect the database, the error is ') . mysql_connect_error() . '</div>';
			}
		}
		$NewSQLFile = $Path_To_Root . '/install/coa/' . $COA;
		if (!$result) {
			if ($_SESSION['Installer']['DBMS'] == 'mysqli') {
				echo '<div class="error">' . _('Failed to create database kwamojademo and the error is ' . ' ' . mysqli_error($db)) . '</div>';
			} else {
				echo '<div class="error">' . _('Failed to create database kwamojademo and the error is ' . ' ' . mysql_error($db)) . '</div>';
			}
		}
		if (!isset($DBPort)) {
			$DBPort = 3306;
		}
		$_SESSION['DatabaseName'] = $DatabaseName;
		include('../includes/ConnectDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
		include('../includes/UpgradeDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
		PopulateSQLData($NewSQLFile, false, $db, $_SESSION['Installer']['DBMS'], $DatabaseName);
		DBUpdate($db, $DatabaseName, $_SESSION['Installer']['DBMS'], $AdminPassword, $Email, $UserLanguage, $DatabaseName);

		session_unset();

	} //end of the installation
	echo '<fieldset>
			<input type="hidden" name="SystemValid" value="1" />
			<button type="submit">' . _('Next Step') . '</button>
		</fieldset>';
	echo '</form>';

	exit;
}
//Handle the database configuration data. We'd like to check if the database information has been input correctly
//First try mysqli configuration

if (isset($_POST['DbConfig'])) {

	//validate those data first
	$InputError = 0; //Assume the best first
	if (!empty($_POST['HostName'])) {
		// As HTTP_HOST is user input, ensure it only contains characters allowed
		// in hostnames. See RFC 952 (and RFC 2181).
		// $_SERVER['HTTP_HOST'] is lowercased here per specifications.
		$_POST['HostName'] = strtolower($_POST['HostName']);
		$HostValid = preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $_POST['HostName']);
		if ($HostValid) {
			$host = $_POST['HostName'];
		} else {
			echo '<div class="error">' . _('The Host Name is illegal') . '</div>';
			exit;
		}


	} else {
		$InputError = 1;
		echo '<div class="error">' . _('The Host Name should not be empty') . '</div>';
	}
	if (!empty($_POST['Database'])) {
		//validate the Database name setting
		//The mysql database name cannot contains illegal characters such as "/","\","." etc
		//and it should not contains illegal characters as file name such as "?""%"<"">"" " etc
		//if prefix is set it should be added to database name
		if (!empty($_POST['Prefix'])) {
			$_POST['Database'] = $_POST['Prefix'] . $_POST['Database'];
		}
		if (preg_match(',[/\\\?%:\|<>\.\s"]+,', $_POST['Database'])) {
			$InputError = 1;
			echo '<div class="error">' . _('The database name should not contains illegal characters such as') . ' ' . '"/\?%:|<>"' . ' ' . _('or blank spaces') . '</div>';

		}
		$DatabaseName = $_POST['Database'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . _('The database name should not be empty') . '</div>';
	}

	if (!empty($_POST['Password'])) {
		$DBPassword = $_POST['Password'];
	} else {
		$DBPassword = '';
	}
	if (!empty($_POST['UserLanguage'])) {
		$UserLanguage = $_POST['UserLanguage'];
	}
	if (!empty($_POST['UserName']) and mb_strlen($_POST['UserName']) <= 16) {
		$DBUser = trim($_POST['UserName']);
	}
	if ($InputError == 0) {
		if (!empty($_POST['MysqlExt']) and $_POST['MysqlExt'] == 1) {
			DbCheck($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName, $_POST['MysqlExt']);
		} else {
			DbCheck($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName);
		}
		exit;
	} else {
		echo '<div class="error">' . _('Please correct the displayed error first') . '</div>';
		if (!empty($_POST['MysqlExt'])) {
			DbConfig($_POST['UserLanguage'], $_POST['MysqlExt']);
		} else {
			DbConfig($_POST['UserLanguage']);
		}
		exit;
	}
	//	$db = mysqli_connect
	//if everything is OK, then we try to connect the DB, the database should be connect by two types of method, if there is no mysqli
} //end of users has submit the database configuration data

echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';

if (!isset($_POST['LanguageSet'])) {
	Installation();
} else { //The locale has been set, it's time to check the settings item.
	$ErrMsg = '';
	$InputError = 0;
	$WarnMsg = '';
	$InputWarn = 0;
	//set the default time zone
	if (!empty($_POST['DefaultTimeZone'])) {
		date_default_timezone_set($_POST['DefaultTimeZone']);

	}
	//Check if the browser has been set properly
	if (!isset($_SESSION['CookieAllowed']) or !($_SESSION['CookieAllowed'] == 1)) {
		$InputError = 1;
		echo '<div class="error">' . _('Please set Cookies allowed in your web brower, otherwise KwaMoja cannot run properly') . '</div>';
	} else {
		echo '<div class="success">' . _('Cookies are properly enabled in your browser') . '</div>';
	}
	//Check the situation of php safe mode
	if (!empty($_POST['SafeModeWarning'])) {
		if (!ContainsIllegalCharacters($_POST['SafeModeWarning'])) {
			$InputWarn = 1;
			$WarnMsg .= '<p>' . _($_POST['SafeModeWarning']) . '</p>';
		} else { //Something must be wrong since this messages have been defined.
			echo '<div class="error">' . _('Illegal characters or data has been identified, please see your admistrator for help') . '</div>';
			exit;

		}
	}
	//check the php version
	if (empty($_POST['PHPVersion'])) {
		$InputError = 1;
		echo '<div class="error">' . _('You PHP version should be greater than 5.1') . '</div>';
	} else {
		echo '<div class="success">' . _('Your PHP version is suitable for KwaMoja') . '</div>';
	}
	//check the directory access authority of rootpath and companies
	if (empty($_POST['ConfigFile'])) {
		$InputError = 1;
		//get the directory where kwamoja live
		$KwaMojaHome = dirname(dirname(__FILE__));
		echo '<div class="error">' . _('The directory') . ' ' . $KwaMojaHome . ' ' . _('must be writable by web server') . '</div>';
	} else {
		echo '<div class="success">' . _('The base KwaMoja directory is writable') . '</div>';
	}
	if (empty($_POST['CompaniesCreate'])) {
		$InputError = 1;
		$KwaMojaHome = dirname(dirname(__FILE__));
		echo '<div class="error">' . _('The directory') . ' ' . $KwaMojaHome . '/companies/' . ' ' . ('must be writable by web server') . '</div>';
	} else {
		echo '<div class="success">' . _('The companies/ directory is writable') . '</div>';
	}
	//check the necessary php extensions
	if (empty($_POST['MbstringExt']) or $_POST['MbstringExt'] != 1) {
		$InputError = 1;
		echo '<div class="error">' . _('The mbstring extension is not availble in your PHP') . '</div>';
	} else {
		echo '<div class="success">' . _('The mbstring extension is correctly installed') . '</div>';
	}
	//check if the libxml is exist
	if (empty($_POST['LibxmlExt']) or $_POST['LibxmlExt'] != 1) {
		$InputError = 1;
		echo '<div class="error">' . _('The libxml extension is not available in your PHP') . '</div>';

	} else {
		echo '<div class="success">' . _('The libxml extension is correctly installed') . '</div>';
	}
	//check if the mysqli or mysql is exist
	if (!empty($_POST['DBMSExt']) and $_POST['DBMSExt'] == 1) {
		$InputError = 1;
		echo '<div class="error">' . _('You do not have the correct database extension installed for PHP') . '</div>';
	} else {
		echo '<div class="success">' . _('The database extension is installed') . '</div>';
	}

	if ($_SESSION['Installer']['DBMS'] == 'mysql' and empty($_POST['PHP55'])) {
		$InputWarn = 1;
		echo '<div class="warn">' . _('The PHP MySQLI extension is recommend as MySQL extension has been deprecated since PHP 5.5') . '</div>';
	} elseif ($_SESSION['Installer']['DBMS'] == 'mysql' and !empty($_POST['PHP55'])) {
		$InputError = 1;
		echo '<div class="error">' . _('The MySQL extension has been deprecated since 5.5. You should install the MySQLI extension or downgrade you PHP version to  one prior to 5.5') . '</p>';
	}
	//Check if the GD extension is available
	if (empty($_POST['GdExt']) or $_POST['GdExt'] != 1) {
		$InputError = 1;
		echo '<div class="error">' . _('The GD extension should be installed in your PHP configuration') . '</p>';
	} else {
		echo '<div class="success">' . _('The GD extension is correctly installed') . '</div>';
	}

	if ($InputError != 0) {
		Recheck();
		exit;
	}
	if ($InputWarn != 0) {
		Recheck();
	}

	echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<fieldset>
			<input type="hidden" name="SystemValid" value="1" />
			<button type="submit">' . _('Next Step') . '</button>
		</fieldset>';
	echo '</form>';

}


//This function used to display the first screen for users to select they preferred langauage
//And at the mean time to check if the php configuration has meet requirements.
function Installation() {
	//Check if the cookie is allowed

	$_SESSION['CookieAllowed'] = 1;

	//Check if it's in safe model, safe mode has been deprecated at 5.3.0 and removed at 5.4
	//Please refer to here for more details http://hk2.php.net/manual/en/features.safe-mode.php
	if (ini_get('safe_mode')) {
		$SafeModeWarning = _('You php is running in safe mode, it will force a maximum script execution time of 30 seconds') . ' ' . _('This can sometimes mean that the installation cannot be completed in time.') . ' ' . _('It is better to turn this function off');
	}

	//It's time to check the php version. The version should be run greater than 5.1
	if (version_compare(PHP_VERSION, '5.1.0') >= 0) {
		$PHPVersion = 1;
	}
	if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
		$PHP55 = 1;
	}
	//Check the writability of the root path and companies path
	$RootPath = '..';
	$Companies = $RootPath . '/companies';
	if (is_writable($RootPath)) {
		$ConfigFile = 1;
	} else {
		clearstatcache();
	}
	if (is_writable($Companies)) {
		$CompaniesCreate = 1;
	} else {
		clearstatcache();
	}
	//check the necessary extensions
	$Extensions = get_loaded_extensions();

	//First check the gd module
	if (in_array('gd', $Extensions)) {
		$GDExt = 1;
	}
	//Check the gettext module, it's a selectable
	if (in_array('gettext', $Extensions)) {
		$GettextExt = 1;
	}
	//Check the mbstring module, it must be exist
	if (in_array('mbstring', $Extensions)) {
		$MbstringExt = 1;
	}
	//Check the libxml module
	if (in_array('libxml', $Extensions)) {
		$LibxmlExt = 1;
	}
	//Check if mysqli is exist
	//usually when it's not exist, there is some warning and cannot contiue in before version
	//We should adjust show a warning to the users if the users still use the mysql, then we should modify the config.php
	//to make use can still continue the installation. It's just performance lost
	if (isset($_POST['DBMS'])) {
		$DBExt = '0';
		if ($_POST['DBMS'] == 'mysql' and in_array('mysql', $Extensions)) {
			$DBExt = '1';
		}
		if ($_POST['DBMS'] == 'mysqli' and in_array('mysqli', $Extensions)) {
			$DBExt = '1';
		}
		if ($_POST['DBMS'] == 'mariadb' and in_array('mysqlnd', $Extensions)) {
			$DBExt = '1';
		}
	}

	echo '<form id="installation" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<fieldset>
			<legend>' . _('Welcome to the KwaMoja Installation Wizard') . '</legend>
			<div class="page_help_text">
				<ul>
					<li>' . _('During installation you may see different status messages.') . '</li>
					<li>' . _('When there is an error message you must correct the error to continue.') . '</li>
					<li>' . _('If you see a warning message you should take notice before you proceed.') . '</li>
					<li>' . _('If you are unsure of an option value, you should keep the default setting.') . '</li>
				</ul>
			</div>
		</fieldset>';

	/* Select the language for the install. This language will also
	 * be the default language for the admin user
	 */
	include('../includes/LanguagesArray.php');
	echo '<fieldset>
			<legend>' . _('Select your language') . '</legend>
				<div class="page_help_text">
					<p>' . _('The installer will try and guess your language from your browser, but may get it wrong. Please select your preferred language below.') . '</p>
				</div>
				<ul>
					<li>
						<label for="Language">' . _('Language:') . '&#160;</label>
							<select id="Language" name="Language">';

	if (substr($_SESSION['Installer']['Language'], 0, 2) != 'en') { //ensure that the bilingual only display when the language is not english
		foreach ($LanguagesArray as $Key => $Language1) { //since we only use the first 2 characters to separate the language, there are some
			//chance that different locale but use same first 2 letters.
			if (!isset($SelectedKey) and substr($DefaultLanugage, 0, 2) == substr($Key, 0, 2)) {
				$SelectedKey = $Key;
				echo '<option value="' . $Key . '" selected="selected">' . $Language1['LanguageName'] . $Language1['WindowsLocale'] . '</option>';
			}
			if (!isset($SelectedKey) or (isset($SelectedKey) and $Key != $SelectedKey)) {
				echo '<option value="' . $Key . '" >' . $Language1['LanguageName'] . $Language1['WindowsLocale'] . '</option>';
			}
		}
	} else {
		foreach ($LanguagesArray as $Key => $Language1) {
			if (!isset($SelectedKey) and substr($Key, 0, 2) == 'en') {
				$SelectedKey = $Key;
				echo '<option value="' . $Key . '" selected="selected">' . $Language1['LanguageName'] . '</option>';
			}
			if (!isset($SelectedKey) or (isset($SelectedKey) and $SelectedKey != $Key)) {

				echo '<option value="' . $Key . '" >' . $Language1['LanguageName'] . '</option>';
			}
		}
	}

	echo '</select>
		</li>
		</ul>
	</fieldset>';

	/* Select the DBMS to be used for this implementation */
	echo '<fieldset>
			<legend>' . _('Select your Database Management System') . '</legend>
			<div class="page_help_text" >
				<p>' . _('KwaMoja can use several different Database systems. Please select below the system you will be using.') . '</p>
			</div>
			<ul>
				<li>
					<label for="DBMS">' . _('DBMS:') . '&#160;</label>
					<select id="DBMS" name="DBMS">';
	if ($_SESSION['Installer']['DBMS'] == 'mysql') {
		echo '<option selected="selected" value="mysql">MySQL</option>';
	} else {
		echo '<option value="mysql">MySQL</option>';
	}
	if ($_SESSION['Installer']['DBMS'] == 'mysqli') {
		echo '<option selected="selected" value="mysqli">MySQLi</option>';
	} else {
		echo '<option value="mysqli">MySQLi</option>';
	}
	if ($_SESSION['Installer']['DBMS'] == 'mariadb') {
		echo '<option selected="selected" value="mariadb">MariaDB</option>';
	} else {
		echo '<option value="mariadb">MariaDB</option>';
	}
	echo '</select>
			</li>
		</ul>
	</fieldset>';


	/* Now we acquire default information about the system setup */

	/* JavaScript function to guess the default time zone */
	echo '<script>
			function tz(){
				document.getElementById(\'DefaultTimeZone\').value = jstz.determine().name();
			}
		</script>';
	echo '<input type="hidden" name="DefaultTimeZone" id="DefaultTimeZone" />';

	if (!empty($SafeModeWarning)) {
		echo '<input type="hidden" name="SafeModeWarning" value="' . $SafeModeWarning . '" />';
	}
	if (!empty($PHPVersion)) { //
		echo '<input type="hidden" name="PHPVersion" value="1" />';
	}
	if (!empty($ConfigFile)) {
		echo '<input type="hidden" name="ConfigFile" value="1" />';
	}
	if (!empty($CompaniesCreate)) {
		echo '<input type="hidden" name="CompaniesCreate" value="1" />';
	}
	if (!empty($GDExt)) {
		echo '<input type="hidden" name="GdExt" value="1" />';
	}
	if (!empty($GettextExt)) {
		echo '<input type="hidden" name="GettextExt" value="1" />';
	}
	if (!empty($MbstringExt)) {
		echo '<input type="hidden" name="MbstringExt" value="1" />';
	}
	if (!empty($LibxmlExt)) {
		echo '<input type="hidden" name="LibxmlExt" value="1" />';
	}
	if (!empty($_SESSION['Installer']['DBExt'])) {
		echo '<input type="hidden" name="DBExt" value="1" />';
	}
	if (!empty($PHP55)) {
		echo '<input type="hidden" name="PHP55" value="1" />';
	}

	echo '<fieldset>
			<input type="hidden" name="LanguageSet" value="1" />
			<button type="submit">' . _('Next Step') . '</button>
		</fieldset>';

	echo '<fieldset>
			<p>' . _('KwaMoja is an open source application licenced under GPL V2 and absolutely free to download.') . '<br />' . _('By installing KwaMoja you acknowledge you have read') . ' ' . '<a href="http://www.gnu.org/licenses/gpl-2.0.html#SEC1" target="_blank">the licence</a>. <br />' . _('Please visit the official KwaMoja website for more information.') . '</p>
		</fieldset>
		<div class="centre">
			<a href="http://www.kwamoja.com"><img src="../css/logo.png" title="KwaMoja" alt="KwaMoja" /></a>
		</div>';

	echo '</form>';
}

//@para Language used to determine user's preferred language
//@para MysqlExt use to mark if mysql extension has been used by users
//The function used to provide a screen for users to input mysql server parameters data
function DbConfig($Language, $MysqlExt = FALSE) { //The screen for users to input mysql database information

	echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';
	echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<fieldset>
			<legend>' . _('Database settings') . '</legend>
			<div class="page_help_text">
				<p>' . _('Please enter your Database information below. The database name is also used at log in time to choose the company for use.') . '<br />
					<span>' . _('* Denotes required field') . '</span>
				</p>
			</div>
			<ul>
				<li>
					<label for="HostName">' . _('Host Name') . ': </label>
					<input type="text" name="HostName" id="HostName" required="required" value="localhost" placeholder="' . _('Enter database host name') . '" />
					<span>' . _('Commonly: localhost or 127.0.0.1') . '</span>
				</li>
				<li>
					<label for="Database">' . _('Database Name') . ': </label>
					<input type="text" name="Database" id="Database" required="required" value="kwamoja" maxlength="16" placeholder="' . _('The database name') . '" />
					<span>' . _('If your user name below does not have permissions to create a database then this database must be created and empty.') . '</span>
				</li>
				<li>
					<label for="Prefix">' . _('Database Prefix') . ': </label>
					<input type="text" name="Prefix" size="25" placeholder="' . _('Useful with shared hosting') . '" pattern="^[A-Za-z0-9$]+_$" />&#160;
					<span>' . _('Optional: in the form of prefix_') . '</span>
				</li>
				<li>
					<label for="UserName">' . _('Database User Name') . ':</label>
					<input type="text" name="UserName" id="UserName" value="root" placeholder="' . _('A valid database user name') . '" maxlength="16" required="required" />&#160;
					<span>' . _('If this user does not have permission to create databases, then the database entered above must exist and be empty.') . '</span>
				</li>
				<li>
					<label for="Password">' . _('Password') . ': </label>
					<input type="password" name="Password" placeholder="' . _('DB user password') . '"  />
					<span>' . _('Enter the user password if one exists') . '</span>
				</li>
			</ul>
		</fieldset>';

	echo '<input type="hidden" name="UserLanguage" value="' . $Language . '" />';
	echo '<input type="hidden" name="Language" value="' . $Language . '" />';
	echo '<input type="hidden" name="DBExt" value="' . $_SESSION['Installer']['DBExt'] . '" />';

	echo '<input type="hidden" name="required" value="' . $_SESSION['Installer']['DBMS'] . '" />';

	echo '<fieldset>
			<button type="submit" name="DbConfig">' . _('Next Step') . '</button>
		</fieldset>';
	exit;
}

//The function is used by users to return to start page
function Recheck() {
	echo '<form id="refresh" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<fieldset>
			<button type="submit">' . _('Check Again') . '</button>
		</fieldset>';
	echo '</form';
}

//@para $UserLanguage is the language select by users and will be used as a default language
//@para $host is the Host of mysql server
//@para $DBUser is the name of the mysql user
//@para $DBPassword is the user's password which is stored in plain text in config.php
//@DatabaseName is the database used by kwamoja
//@$MysqlExt to check if it's use mysql extension in php instead of mysqli
//The function used to check if mysql parameters have been set correctly and can connect correctly

function DbCheck($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName, $MysqlExt = FALSE) { //Check if the users have input the correct password
	if ($MysqlExt) { //use the mysqli
		$Con = mysql_connect($host, $DBUser, $DBPassword);

	} else {
		$Con = mysqli_connect($host, $DBUser, $DBPassword);
	}
	if (!$Con) {
		echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';
		echo '<div class="error">' . _('Failed to connect to the database. Please correct the following error:') . '<br/>' . mysqli_connect_error() . '<br/> ' . ('This error is usually caused by entry of an incorrect database password or user name.') . '</div>';
		if ($MysqlExt) {
			DbConfig($UserLanguage, $MysqlExt);
		} else {
			DbConfig($UserLanguage);
		}

	} else {
		CompanySetup($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName);
	}

}
//@para $UsersLanguage the language select by the user it will be used as the default langauge in config.php
//@para $host is the host for mysql server
//@para $DBUser is the name of mysql user
//@para $DBPassword is the password for mysql server
//@para $DatabaseName is the name of the database of KwaMoja and also the same name of company
//@para $MysqlEx is refer to the php mysql extention if it's false, it means the php configuration only support mysql instead of mysqli
//The purpose of this function is to display the final screen for users to input company, admin user accounts etc informatioin
function CompanySetup($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName) { //display the company setup for users

	echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';
	echo '<form id="companyset" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';

	echo '<fieldset>
			<legend>' . _('Company Settings') . '</legend>
			<div class="page_help_text">
				<p>
					<span>' . _('* Denotes required field') . '</span>
				</p>
			</div>
			<ul>
				<li>
					<label for="CompanyName">' . _("Company Name") . ': </label>
					<input type="text" name="CompanyName" required="required" value="' . $DatabaseName . '" maxlength="50" />
					<span>' . _('Currently, must be the same as the database name') . '</span>
				</li>
				<li>
				<label for="COA">' . _("Chart of Accounts") . ': </label>
				<select name="COA">';

	$COAs = scandir('coa');
	$COAs = array_diff($COAs, array(
		'.',
		'..'
	));
	foreach ($COAs as $Value) {
		if ($Value == 'kwamoja-new.sql') {
			echo '<option value="' . $Value . '" selected="true">' . $Value . '</option>';
		} else {
			echo '<option value="' . $Value . '">' . $Value . '</option>';
		}
	}
	echo '</select>
			<span>' . _('Will be installed as starter Chart of Accounts') . '</span>
		</li>';

	echo '<li>
			<label for="TimeZone">' . _("Time Zone") . ': </label>
			<select name="TimeZone">';
	include('timezone.php');
	echo '</select>
		</li>';

	echo '<li>
			<label for="Logo">' . _('Company logo file') . ': </label>
			<input type="file" accept="image/jpg" name="LogoFile" title="' . _('A jpg file up to 10kb, and not greater than 170px x 80px') . '" />
			<span>' . _("jpg file to 10kb, and not greater than 170px x 80px") . '</span>
		</li>
	</ul>
</fieldset>';

	echo '<fieldset>
			<legend>' . _('Installation option') . '</legend>
				<ul>
					<li>
						<label for="InstallDemo">' . _('Install the demo data?') . '</label><input type="checkbox" name="Demo" checked="checked"  />
						<span>' . _("KwaMojaDemo site and data will be installed") . '</span>
					</li>
				</ul>
		</fieldset>';
	echo '<fieldset>
			<legend>' . _('Administrator account settings') . '</legend>
			<div class="page_help_text">
				<ul>
					<li>' . _('The default user name is \'admin\' and it cannot be changed.') . '</li>
					<li>' . _('The default password is \'kwamoja\' which you can change below.') . '</li>
				</ul>
			</div>
			<ul>
				<li>
					<label for="adminaccount">' . _('KwaMoja Admin Account') . ': </label>
					<input type="text" name="adminaccount" value="admin" disabled="disabled" />
				</li>
				<li>
					<label for="Email">' . _('Email address') . ': </label>
					<input type="email" name="Email" required="required" placeholder="admin@yoursite.com" />
					<span>' . _('For example: admin@yourcompany.com') . '</span>
				</li>
				<li>
					<label for="KwaMojaPassword">' . _('KwaMoja Password') . ': </label>
					<input type="password" name="KwaMojaPassword" required="required" value="kwamoja" />
				</li>
				<li>
					<label for="PasswordConfirm">' . _('Re-enter Password') . ': </label>
					<input type="password" name="PasswordConfirm" required="required" value="kwamoja" />
				</li>
			</ul>
		</fieldset>';

	echo '<input type="hidden" name="HostName" value="' . $host . '" />
		<input type="hidden" name="UserName" value="' . $DBUser . '" />
		<input type="hidden" name="Password" value="' . $DBPassword . '" />
		<input type="hidden" name="DBExt" value="' . $_SESSION['Installer']['DBExt'] . '" />
		<input type="hidden" name="UserLanguage" value="' . $UserLanguage . '" />
		<input type="hidden" name="MAX_FILE_SIZE" value="10240" />';

	echo '<fieldset>
			<button type="submit" name="Install">' . _('Install') . '</button>
		</fieldset>';

	echo '</form>
		</div>';
}
//@para $NewSQL is the kwamoja new sql file which contains the COA file
//@para $Demo is the kwamoja demo sql file
//@para $db refer to the database connection reference
//@para $DBType refer to the database connection type mysqli or mysql
//@para $NewDB is the new database name
//The purpose of this function is populate database with data from the sql file by mysqli
function PopulateSQLData($NewSQL = false, $Demo = false, $db, $DBType, $NewDB = false) {
	if ($NewSQL) {
		$PathPrefix = '../';
		$StartingUpdate = 0;
		$EndingUpdate = HighestFileName('../sql/updates/');
		unset($_SESSION['Updates']);
		$_SESSION['Updates']['Errors'] = 0;
		$_SESSION['Updates']['Successes'] = 0;
		$_SESSION['Updates']['Warnings'] = 0;
		for ($UpdateNumber = $StartingUpdate; $UpdateNumber <= $EndingUpdate; $UpdateNumber++) {
			if (file_exists('../sql/updates/' . $UpdateNumber . '.php')) {
				$sql = "SET FOREIGN_KEY_CHECKS=0";
				$result = DB_query($sql, $db);
				include('../sql/updates/' . $UpdateNumber . '.php');
				$sql = "SET FOREIGN_KEY_CHECKS=1";
				$result = DB_query($sql, $db);
			}
		}
		echo 'Number of Errors ' . $_SESSION['Updates']['Errors'];
		if ($_SESSION['Updates']['Errors'] > 0) {
			echo '<div class="error">' . _('There were errors creating the database') . '</div>';
			foreach ($_SESSION['Updates']['Messages'] as $Message) {
				echo '<div class="error">' . $Message . '</div>';
			}
			exit;
		}
	}
	if ($Demo) {

		if ($DBType == 'mysqli') {
			mysqli_select_db($db, $NewDB);
		} else {
			mysql_select_db($NewDB, $db);
		}
		PopulateSQLDataBySQL($Demo, $db, $DBType, false, $NewDB);
		//we can let users wait instead of changing the my.cnf file
		//It is a non affordable challenge for them since wamp set the max_allowed_packet 1M
		//and kwamojademo.sql is 1.4M so at least it cannot install in wamp
		//so we not use the multi query here


		/*	$SQLFile = fopen($Demo);

		$sql = file_get_contents($Demo);
		if(!$sql){
		die(_('Failed to open the demo sql file'));
		}

		$result = mysqli_multi_query($db,$sql);

		if(!$result){
		echo '<div class="error">' . _('Failed to populate the database'.' '.$NewDB.' and the error is').' '.mysqli_error($db) . '</div>';
		}
		//clear the bufferred result
		do {
		if($result = mysqli_store_result($db)){
		mysqli_free_result($result);
		}
		} while (mysqli_more_results($db)?mysqli_next_result($db):false); */


		/*	}else{
		mysqli_select_db($db,$NewDB);
		PopulateSQLDataBySQL($Demo,$db,$DBType,false,$NewDB);
		}*/
	}





}
//@para $File is the sql file name
//@para $db is the DB connect reference
//@para $DBType refer to mysqli or mysql connection
//@para $NewDB is the new database name
//@para $DemoDB is the demo database name
//The purpose of this function is populate the database with mysql extention
function PopulateSQLDataBySQL($File, $db, $DBType, $NewDB = false, $DemoDB = 'kwamojademo') {
	$dbName = ($NewDB) ? $NewDB : $DemoDB;
	($DBType == 'mysqli') ? mysqli_select_db($db, $dbName) : mysql_select_db($dbName, $db);
	$SQLScriptFile = file($File);
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$SQL = '';
	$InAFunction = false;
	for ($i = 0; $i < $ScriptFileEntries; $i++) {

		$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
		//ignore lines that start with -- or USE or /*
		if (mb_substr($SQLScriptFile[$i], 0, 2) != '--' AND mb_strstr($SQLScriptFile[$i], '/*') == FALSE AND mb_strlen($SQLScriptFile[$i]) > 1) {

			$SQL .= ' ' . $SQLScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i], 0, 15) == 'CREATE FUNCTION') {
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i], 0, 8) == 'LANGUAGE') {
				$InAFunction = false;
			}
			if (mb_strpos($SQLScriptFile[$i], ';') > 0 AND !$InAFunction) {
				// Database created above with correct name.
				if (strncasecmp($SQL, ' CREATE DATABASE ', 17) AND strncasecmp($SQL, ' USE ', 5)) {
					$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1);

					$result = ($DBType == 'mysqli') ? mysqli_query($db, $SQL) : mysql_query($SQL, $db);
				}
				$SQL = '';
			}

		} //end if its a valid sql line not a comment
	} //end of for loop around the lines of the sql script
}

//@para $db the database connection
//@para $DatabaseName the database to update
//@para $_SESSION['Installer']['DBMS'] if it is mysql extention or not
//@para $AdminPasswd the kwamoja administrator's password
//@para $AdminEmail the kwamoja administrators' email
//@para $AdminLangauge the administrator's language for login
//@para $CompanyName the company
//The purpose of this function is to update the admin accounts and company name information

function DBUpdate($db, $DatabaseName, $DBConnectType, $AdminPasswd, $AdminEmail, $AdminLanguage, $CompanyName) {
	$MysqlExt = ($DBConnectType == 'mysql') ? true : false;
	//select the database to connect
	$Result = (!$MysqlExt) ? mysqli_select_db($db, $DatabaseName) : mysql_select_db($DatabaseName, $db);

	$sql = "UPDATE www_users
				SET password = '" . sha1($AdminPasswd) . "',
					email = '" . $AdminEmail . "',
					language = '" . $AdminLanguage . "'
				WHERE userid = 'admin'";
	$Result = (!$MysqlExt) ? mysqli_query($db, $sql) : mysql_query($sql, $db);
	if (!$Result) {

		echo '<div class="error">' . _('Failed to update the email address and password of the administrator and the error is') . ((!$MysqlExt) ? mysqli_error($db) : mysql_error($db)) . '</div>';
	}

	$sql = "UPDATE companies
			SET coyname = '" . ((!$MysqlExt) ? mysqli_real_escape_string($db, $CompanyName) : mysql_real_escape_string($CompanyName, $db)) . "'
			WHERE coycode = 1";
	$Result = (!$MysqlExt) ? mysqli_query($db, $sql) : mysql_query($sql, $db);
	if (!$Result) {
		echo '<div class="error">' . _('Failed to update the company name and the erroris') . ((!$MysqlExt) ? mysqli_error($db) : mysql_error($db)) . '</div>';
	}


}

function HighestFileName($PathPrefix) {
	if ($handle = opendir('../sql/updates')) {
		$i = 0;
		// Go through directory:
		while (false !== ($file = readdir($handle))) {
			// filter unnecessary file/dir paths:
			if (substr($file, -3) == 'php') {
				$FileNameLength = strlen($file) - 4;
				$UpdateNames[$i] = substr($file, 0, $FileNameLength);
				$i++;
			}
		}
		closedir($handle);
	}
	return max($UpdateNames);
}

function executeSQL($sql, $db, $TrapErrors = False) {
	global $SQLFile;
	/* Run an sql statement and return an error code */
	if (!isset($SQLFile)) {
		DB_IgnoreForeignKeys($db);
		$result = DB_query($sql, $db, '', '', false, $TrapErrors);
		$ErrorNumber = DB_error_no($db);
		DB_ReinstateForeignKeys($db);
		return $ErrorNumber;
	} else {
		fwrite($SQLFile, $sql . ";\n");
	}
}

function updateDBNo($NewNumber, $db) {
	global $SQLFile;
	if (!isset($SQLFile)) {
		$sql = "UPDATE config SET confvalue='" . $NewNumber . "' WHERE confname='DBUpdateNumber'";
		executeSQL($sql, $db);
		$_SESSION['DBUpdateNumber'] = $NewNumber;
	}
}

echo '<script src="installer.js"></script>';

echo '</body>
	</html>';

?>