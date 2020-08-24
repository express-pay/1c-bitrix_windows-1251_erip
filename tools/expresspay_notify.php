<?
use \Bitrix\Sale\Order;

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define("DisableEventsCheck", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
CModule::IncludeModule("sale");


// ��������� POST �������
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$json = $_POST['Data'];
	$signature = $_POST['Signature'];
	
	// ����������� �� JSON � Object
	$data = json_decode($json);
	
	if($arOrder = CSaleOrder::GetByID(IntVal($data->AccountNo)))
	{
		// ������������� ���������� ��������� �������
		CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
	
		// ������������� �������� ������� ����������� � ���������� ������� ��������
		$isUseSignature = CSalePaySystemAction::GetParamValue("IS_SIGNATURE");
		
		// ��������� ������������� �������� �������
		if($isUseSignature == 'Y') {
		
		// ��������� ����� ����������� � ���������� ������� ��������
		$secretWord = CSalePaySystemAction::GetParamValue("SECRET_WORD");
		
			// ��������� �������� �������
			if($signature == computeSignature($json, $secretWord)) {
			
				updateOrder($data);
				
				$status = 'OK | payment received';
				header("HTTP/1.0 200 OK");
			} else {
				
				$status = 'FAILED | wrong notify signature'; 
				header("HTTP/1.0 400 Bad Request");
			}
		} else {
			updateOrder($data);

			$status = 'OK | payment received';
			header("HTTP/1.0 200 OK");
		}
	} else {
		$status = 'FAILED | ID ������ ����������'; 
		header("HTTP/1.0 200 Bad Request");
	}
}

function computeSignature($json, $secretWord) {
    $hash = NULL;
    
	$secretWord = trim($secretWord);
	
    if (empty($secretWord))
		$hash = strtoupper(hash_hmac('sha1', $json, ""));
    else
        $hash = strtoupper(hash_hmac('sha1', $json, $secretWord));
    return $hash;
}

// ���������� ������� ������
function updateOrder($data) {
	// ��������� ������ �����
	if($data->CmdType == '3' || $data->CmdType == '6') {	
		// ���� �������
		if($data->Status == '3') {		
			// ��������� ������ �� ������ �������� �����
			$order = CSaleOrder::GetByID($data->AccountNo);

			// ����� ����������
			if(isset($order)) {
				CSalePaySystemAction::InitParamArrays($order, $order["ID"]);
				
				// �������� ����� ��� ����������
				$arFields = array(
					"PAYED" => "Y",
					"STATUS_ID" => "F",
				);
		 
				CSaleOrder::Update($order["ID"], $arFields);
			}		
		}
	}
}

?>