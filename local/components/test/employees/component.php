<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

\Bitrix\Main\Loader::IncludeModule('iblock');
\Bitrix\Main\Loader::IncludeModule('intranet');

define(FIRED_STATUS_NAME, 'Уволен');
define(WORKING_STATUS_NAME, 'Работает');
define(ABSENT_STATUS_NAME, 'Отсутствует');
define(B24_PROFILE_PATH, '/company/personal/user/');

$navigation = new \Bitrix\Main\UI\PageNavigation('employees-navigation');
$navigation->allowAllRecords(true)
	->setPageSizes([10,30,50,100])
   	->setPageSize(10)
   	->initFromUri();

$arResult = [
	'USERS' => [],
	'DEPARTMENTS' => []
];

// Получить список департаментов
$resDepartmentsIblock = CIBlock::GetList(array(), array("CODE" => "departments"));
$departmentsIblock = $resDepartmentsIblock->Fetch();
$departmentsResult = CIBlockSection::GetList(
	['ID' => 'ASC'],
	['IBLOCK_ID' => $departmentsIblock["ID"]],
	false,
	['ID', 'UF_HEAD'],
	false
);

// заполнить $arResult['DEPARTMENTS'] элементами IdДепартамента => IdРуководителя
while ($department = $departmentsResult->GetNext()) {
	$arResult['DEPARTMENTS'][$department['ID']] = $department['UF_HEAD'];
}

$userQuerySelect = [
	'ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'WORK_POSITION', 'WORK_PHONE', 'UF_DEPARTMENT'
];
$headsQuery = new \Bitrix\Main\Entity\Query(
	\Bitrix\Main\UserTable::getEntity()
);
$headsQuery
	->setSelect($userQuerySelect)
    ->setFilter(['ID' => array_values($arResult['DEPARTMENTS'])])
    ->setOrder(['ID' => 'ASC']);
$headsResult = $headsQuery->exec();

// заполнить $arResult['DEPARTMENTS'] элементами IdДепартамента => [Пользователь-руководитель]
while ($head = $headsResult->fetch()) {
	// Присвоить департаментам в качестве значения сотрудника-руководителя
	foreach ($arResult['DEPARTMENTS'] as $departmentId => $headId) {
		if (!is_array($headId) && $headId == $head['ID']) {
			$arResult['DEPARTMENTS'][$departmentId] = $head;
		}
	}
}

// Массив IdРуководителя => IdДепартамента[]
$headToDepartment = [];

foreach ($arResult['DEPARTMENTS'] as $departmentId => $head) {
	$arResult['DEPARTMENTS'][$departmentId]['EMPLOYEE_COUNT'] = 0;
	// CIntranetUtils::getDepartmentEmployees не вернет ассоциацию при указании массива департаментов, поэтому в цикле
	$resDepartmentEmployees = CIntranetUtils::getDepartmentEmployees([$departmentId], false, false, 'Y', ['ID']);
	$arResult['DEPARTMENTS'][$departmentId]['EMPLOYEE_COUNT'] = $resDepartmentEmployees->result->num_rows;

	$headToDepartment[$head['ID']][] = $departmentId;
}

// Запрос пользователей
$userQuery = new \Bitrix\Main\Entity\Query(
	\Bitrix\Main\UserTable::getEntity()
);
$userQuery
	->countTotal(true)
	->setSelect($userQuerySelect)
    ->setFilter([])
    ->setOrder(['SECOND_NAME' => 'ASC'])
    ->setOffset($navigation->getOffset())
	->setLimit($navigation->getLimit());

$usersResult = $userQuery->exec();
$navigation->setRecordCount($usersResult->getCount());

// Перебор выборки пользователей
while ($user = $usersResult->fetch()) {
	// Количество подчиненных у сотрудника
	$user['SUBORDINATES'] = 0;
	// Сформировать строку-руководителя сотрудника
	$user['HEAD'] = [];

	// Добавить Id руководимых департаментов, на случай если они не заданы
	// для однообразности доступа к данным по департаменту (в частности количество подчиненных)
	// и исключения несоответствеия если руководитель департамента не привязан к департаменту
	$decrement = 0;
	// Если есть ассоциация сотрудника с руководимыми департаментами
	if (array_key_exists($user['ID'], $headToDepartment)) {
		// Если сотрудник не привяза к департаменту которым он руководит,
		// то добавить id этого департамента в UF_DEPARTMENT и прибавить 1 к числу сотрудников в таком департаменте
		foreach ($headToDepartment[$user['ID']] as $headDepartmentId) {
			if (!in_array($headDepartmentId, $user['UF_DEPARTMENT'])) {
				$user['UF_DEPARTMENT'][] = $headDepartmentId;
				$arResult['DEPARTMENTS'][$headDepartmentId]['EMPLOYEE_COUNT']++;
			}
		}
	}

	// перебрать департаменты сотрудника
	foreach ($user['UF_DEPARTMENT'] as $userDepartmentId) {
		// если он не руководитель департамента
		if ($user['ID'] != $arResult['DEPARTMENTS'][$userDepartmentId]['ID']) {
			// добавить фамилию с ссылкой на профиль руководителя для сотрудника
			$user['HEAD'][] = 
				'<a href="' . B24_PROFILE_PATH . $arResult['DEPARTMENTS'][$userDepartmentId]['ID'] . '/">' 
				. $arResult['DEPARTMENTS'][$userDepartmentId]['LAST_NAME'] 
				. '</a>';
		} else {
			$user['SUBORDINATES'] += $arResult['DEPARTMENTS'][$userDepartmentId]['EMPLOYEE_COUNT'] - 1;
		}
	}

	// склеить фамилии руководителей запятыми
	$user['HEAD'] = implode(', ', $user['HEAD']);

	// Не активный пользователь = уволен
	if ($user['ACTIVE'] == 'N') {
		$user['STATUS'] = FIRED_STATUS_NAME;
	} else {
		$user['STATUS'] = WORKING_STATUS_NAME;
	}

	// Ссылка на профиль
	$user['PROFILE_URL'] = B24_PROFILE_PATH . $user['ID'] . '/';

	$arResult['USERS'][$user['ID']] = $user;
}

// Получить элементы-отсутствия
$absences = CIntranetUtils::GetAbsenceData([
	'PER_USER' => true,
	'USERS' => array_keys($arResult['USERS']),
	'DATE_START' => date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), strtotime(date('Y-m-d'))), 
	'DATE_FINISH' => date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), strtotime(date('Y-m-d')))
]);

// перебрать элементы-отсутствия
foreach ($absences as $userId => $absence) {
	// если не уволен
	if ($arResult['USERS'][$userId]['STATUS'] != FIRED_STATUS_NAME) {
		// Добавить сотруднику в статус причины отсутствия
		$absenceReasons = [];

		foreach ($absence as $absenceItem) {
			$absenceReasons[] = $absenceItem['PROPERTY_ABSENCE_TYPE_VALUE'];
		}

		$arResult['USERS'][$userId]['STATUS'] = ABSENT_STATUS_NAME . ' (' . implode(', ', $absenceReasons) . ')';
	}
}

$arResult['NAVIGATION'] = $navigation;

$this->IncludeComponentTemplate();