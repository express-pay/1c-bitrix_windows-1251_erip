<?
	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

	include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

	$psTitle = GetMessage("SPCP_DTITLE");
	$psDescription = GetMessage("SPCP_DTITLE");

	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

	$arPSCorrespondence = array(
		"IS_TEST_API" => array(
			"SORT" => 10,
			"NAME" => "������ � �������� ������",
			"DESCR"	=> "�������������� ����������� � �������� �������",
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"TOKEN"	=> array(
			"SORT" => 20,
			"NAME"	=> "API-���� (�����) ������� � API",
			"DESCR"	=> "�������� � ������ ��������",
			"VALUE"	=> "",
			"TYPE"	=> ""
		),
		"SERVICE_ID"	=> array(
			"SORT" => 30,
			"NAME"	=> "����� ������ � ������� \"�������� �������\"",
			"DESCR"	=> "����� ������ � ������ �������� � ���������� ������",
			"VALUE"	=> "",
			"TYPE"	=> ""
		),
		"SECRET_WORD"	=> array(
			"SORT" => 40,
			"NAME"	=> "��������� ����� ��� �������� �������",
			"DESCR"	=> "�������� � ������ ��������",
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"NOTIFICATION_URL"	=> array(
			"SORT" => 45,
			"NAME"	=> "����� ��� ��������� �����������",
			"DESCR"	=> "��������� ��� ���������� ������� ����� ��� ������",
			"DEFAULT" => array(
				"PROVIDER_VALUE" => $url. "/bitrix/tools/expresspay_notify.php",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"IS_USE_SIGNATURE_FROM_NOTIFICATION" => array(
			"SORT" => 50,
			"NAME" => "������������ �������� ������� ��� ��������� �����������",
			"DESCR"	=> "�������� ������ �������������� ��������, �������������� � ������ ��������",
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"SECRET_WORD_FROM_NOTIFICATION"	=> array(
			"SORT" => 55,
			"NAME"	=> "��������� ����� ��� �������� ������� �����������",
			"DESCR"	=> "�������� � ������ ��������",
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"INFO_TEMPLATE"	=> array(
			"SORT" => 60,
			"NAME"	=> "������ ���������� �������",
			"DESCR"	=> "�� ������ ��������� 1024 �������",
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "������ ������ ##ORDER_ID##",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"ERIP_PATH"	=> array(
			"SORT" => 70,
			"NAME"	=> "���� ����� ���� ������",
			"DESCR"	=> "�������� ��� �����������. ������� � ������ ��������",
			"DEFAULT"	=> array(
				"PROVIDER_VALUE" => "��������-��������/�������->[������ ����� �����]->[��� �����]",
				"PROVIDER_KEY" => "VALUE"
				),
		),
		"PERSONAL_ACCOUNT_NAME"	=> array(
			"SORT" => 80,
			"NAME"	=> "������������ �������� �����",
			"DESCR"	=> "�������� ��� �����������. ������� � ������ ��������",
			"DEFAULT"	=> array(
				"PROVIDER_VALUE" => "����� ������",
				"PROVIDER_KEY" => "VALUE"
				),
		),
		"IS_NAME_EDITABLE" => array(
			"SORT" => 90,
			"NAME" => "��������� �������� ��� �����������",
			"DESCR"	=> "��������� �������� ��� ����������� ��� ������ � ������� ����",
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"IS_ADDRESS_EDITABLE" => array(
			"SORT" => 95,
			"NAME" => "��������� �������� ����� �����������",
			"DESCR"	=> "��������� �������� ����� ����������� ��� ������ � ������� ����",
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
				"PROVIDER_KEY" => "INPUT"
			)
		),
		"IS_AMOUNT_EDITABLE" => array(
			"SORT" => 100,
			"NAME" => "��������� �������� ����� ������",
			"DESCR"	=> "��������� �������� ����� ������ ��� ������ � ������� ����",
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "N",
				"PROVIDER_KEY" => "INPUT"
			)
		),
	);
?>