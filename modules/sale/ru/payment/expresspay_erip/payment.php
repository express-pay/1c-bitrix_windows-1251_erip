<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("sale");

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// �������� POST �������
if (!function_exists('ExpressPay_SendRequestPOST')) {
	function ExpressPay_SendRequestPOST($url, $params) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	
		return $httpcode;
	}
}

if (!function_exists('ExpressPay_AddInvoice')) {
	// ������������ �������� �������
	function computeSignature($requestParams, $secretWord, $method) {
		$normalizedParams = array_change_key_case($requestParams, CASE_LOWER);
		$mapping = array(
			"add-invoice" => array(
									"token",
									"accountno",
									"amount",
									"currency",
									"expiration",
									"info",
									"surname",
									"firstname",
									"patronymic",
									"city",
									"street",
									"house",
									"building",
									"apartment",
									"isnameeditable",
									"isaddresseditable",
									"isamounteditable")
		);
		$apiMethod = $mapping[$method];
		$result = "";
		foreach ($apiMethod as $item){
			$result .= $normalizedParams[$item];
		}
		$hash = strtoupper(hash_hmac('sha1', $result, $secretWord));
		return $hash;
	}
	
	
	//����������� �����
	function ExpressPay_AddInvoice($token, $numberAccount, $amount, $currency, $expiration = "", $info = "", 
		$surname = "", $firstName = "", $patronymic = "", $city = "", $street = "", $house="", $building = "", 
		$apartment = "", $isNameEditable = "", $isAddressEditable = "", $isAmountEditable = "", $emailNotification = "") {
		
		$isTest = CSalePaySystemAction::GetParamValue("IS_TEST_API");
		$baseUrl = "https://api.express-pay.by/v1/";
		
		if($isTest == 'Y')
			$baseUrl = "https://sandbox-api.express-pay.by/v1/";
		
		$url = $baseUrl . "invoices?token=" . $token;
		

		$requestParams = array(
				"AccountNo" => $numberAccount,
				"Amount" => $amount,
				"Currency" => $currency,
				"Expiration" => $expiration,
				"Info" => $info,
				"Surname" => $surname,
				"FirstName" => $firstName,
				"Patronymic" => $patronymic,
				"City" => $city,
				"Street" => $street,
				"House" => $house,
				"Building" => $building,
				"Apartment" => $apartment,
				"IsNameEditable" => $isNameEditable,
				"IsAddressEditable" => $isAddressEditable,
				"IsAmountEditable" => $isAmountEditable,
				"EmailNotification" => $emailNotification
		);
		
		return ExpressPay_SendRequestPOST($url, $requestParams);  
	}
}

log_info('payment','Begin payment process');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

	if(isset($_REQUEST['result']))
	{
		if($_REQUEST['result'] == 'success' && validSignature($_REQUEST['Signature']))
		{
			$inv_id = $_REQUEST['ExpressPayAccountNo'];
			$out_summ = $_REQUEST['ExpressPayAmount'];
			$info_template = CSalePaySystemAction::GetParamValue("INFO_TEMPLATE");
			$paname = CSalePaySystemAction::GetParamValue("PERSONAL_ACCOUNT_NAME");
			$erip_path = CSalePaySystemAction::GetParamValue("ERIP_PATH");

			$invoice_template = '��� ���������� ���������� ������ � ����� �������, ����������� ��������� ������ ����� ���� (������ ����������� ������������, ���������, ��������� ���������, ������� ��������-��������, ������-�������� � �.�.). <br /> 
						<br />
						��� ����� � ������� ����� ���� �������� ������: <br />
						<b>##ERIP_PATH##</b> <br /><br />

						� ���� \'<b>##PERSONAL_ACCOUNT_NAME##</b>\' ������� \'<b>##ORDER_ID##</b>\' � ������� \'����������\'. <br /><br />

						����� ��� ������: <b>##SUM## BYN</b><br />';
															
			$invoice_description = str_replace("##ORDER_ID##", $inv_id, $invoice_template);
			$invoice_description = str_replace("##SUM##", $out_summ, $invoice_description);
			$invoice_description = str_replace("##PERSONAL_ACCOUNT_NAME##", $paname, $invoice_description);
			$invoice_description = str_replace("##ERIP_PATH##", $erip_path, $invoice_description);
				
			$result = $invoice_description;

			echo $result;
		}
		else
		{
			echo '��� ������� ��������� ���� ��������� ������.';
		}
	}
	else
	{

		$isTest = CSalePaySystemAction::GetParamValue("IS_TEST_API");
		$baseUrl = "https://api.express-pay.by/v1/";
		
		if($isTest == 'Y')
			$baseUrl = "https://sandbox-api.express-pay.by/v1/";
		
		$url = $baseUrl . "web_invoices";

		$request_params = getInvoiceParam();

		log_info('payment','REQUEST PARAMS: ' . json_encode($request_params));

		$button         = '<form method="POST" action="'.$url.'">';

        foreach($request_params as $key => $value)
        {
            $button .= "<input type='hidden' name='$key' value='$value'/>";
        }

        $button .= '<input type="submit" class="checkout_button" name="submit_button" value="��������� ���� � ������� ����" />';
		$button .= '</form>';

		log_info('payment','Button: ' . json_encode($button));
		
		echo $button;
	}
	
}

function getInvoiceParam()
{
	$inv_id = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];//����� ������
	$shouldPay = (strlen(CSalePaySystemAction::GetParamValue("SHOULD_PAY", '')) > 0) ? CSalePaySystemAction::GetParamValue("SHOULD_PAY", 0) : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
	$out_summ = number_format(floatval($shouldPay), 2, ',', '');//������������ ����� � 2 ������� ����� ","

	$token = CSalePaySystemAction::GetParamValue("TOKEN");
	$secret_word = CSalePaySystemAction::GetParamValue("SECRET_WORD");
	$serviceId = CSalePaySystemAction::GetParamValue("SERVICE_ID");
	$info = str_replace("##ORDER_ID##", $inv_id, $info_template);
	$name_edit = CSalePaySystemAction::GetParamValue("IS_NAME_EDITABLE");
	$name_edit = CSalePaySystemAction::GetParamValue("IS_ADDRESS_EDITABLE");
	$amount_edit = CSalePaySystemAction::GetParamValue("IS_AMOUNT_EDITABLE");

	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$request_params = array(
		'ServiceId'         => $serviceId,
		'AccountNo'         => $inv_id,
		'Amount'            => $out_summ,
		'Currency'          => 933,
		'ReturnType'        => 'redirect',
		'ReturnUrl'         => $url."&result=success&ExpressPayAmount={$out_summ}" ,
		'FailUrl'           => $url."&result=fail",
		'Expiration'        => '',
		'Info'              => $info,
		'Surname'           => '',
		'FirstName'         => '',
		'Patronymic'        => '',
		'Street'            => '',
		'House'             => '',
		'Apartment'         => '',
		'IsNameEditable'    => $name_edit == 'Y' ? 1 : 0,
		'IsAddressEditable' => $address_edit == 'Y' ? 1 : 0,
		'IsAmountEditable'  => $amount_edit == 'Y' ? 1 : 0,
		'EmailNotification' => '',
		'SmsPhone'          => ''
	);

	$request_params['Signature'] = compute_signature($request_params, $token, $secret_word);

	return $request_params;

}

function log_error_exception($name, $message, $e) {
	expresspay_log($name, "ERROR" , $message . '; EXCEPTION MESSAGE - ' . $e->getMessage() . '; EXCEPTION TRACE - ' . $e->getTraceAsString());
}


function log_error($name, $message) {
	expresspay_log($name, "ERROR" , $message);
}



function log_info($name, $message) {
	expresspay_log($name, "INFO" , $message);
}

function expresspay_log($name, $type, $message) {
	$log_url = dirname(__FILE__) . '/log';

	if(!file_exists($log_url)) {
		$is_created = mkdir($log_url, 0777);

		if(!$is_created)
			return;
	}

	$log_url .= '/express-pay-' . date('Y.m.d') . '.log';

	file_put_contents($log_url, $type . " - IP - " . $_SERVER['REMOTE_ADDR'] . "; USER AGENT - " . $_SERVER['HTTP_USER_AGENT'] . "; FUNCTION - " . $name . "; MESSAGE - " . $message . ';' . PHP_EOL, FILE_APPEND);

}

function compute_signature($request_params, $token, $secret_word, $method = 'add_invoice') {
	$secret_word = trim($secret_word);
	$normalized_params = array_change_key_case($request_params, CASE_LOWER);
	$api_method = array( 
		'add_invoice' => array(
							"serviceid",
							"accountno",
							"amount",
							"currency",
							"expiration",
							"info",
							"surname",
							"firstname",
							"patronymic",
							"city",
							"street",
							"house",
							"building",
							"apartment",
							"isnameeditable",
							"isaddresseditable",
							"isamounteditable",
							"emailnotification",
							"smsphone",
							"returntype",
							"returnurl",
							"failurl"),
		'get_qr_code' => array(
							"invoiceid",
							"viewtype",
							"imagewidth",
							"imageheight"),
		'add_invoice_return' => array(
							"accountno",
							"invoiceno"
		)
	);

	$result = $token;

	foreach ($api_method[$method] as $item)
		$result .= ( isset($normalized_params[$item]) ) ? $normalized_params[$item] : '';

	$hash = strtoupper(hash_hmac('sha1', $result, $secret_word));

	return $hash;
}

function validSignature($signature)
{
	$token = CSalePaySystemAction::GetParamValue("TOKEN");
	$secret_word = CSalePaySystemAction::GetParamValue("SECRET_WORD");

	$signature_param = array(
		"AccountNo" => $_REQUEST['ExpressPayAccountNumber'],
		"InvoiceNo" => $_REQUEST['ExpressPayInvoiceNo'],
		);

	$validSignature = compute_signature($signature_param, $token, $secret_word, 'add_invoice_return');

	return $validSignature == $signature;
}

?>

