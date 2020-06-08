<?php
/*
Задача 
* Создать компонент для вывода списка пользователей в табличном виде.
Обязательные поля для отображения - Фамилия, Имя, Отчество, Должность, Рабочий телефон, Фамилия начальника, Количество подчиненных, Статус пользователя(работает/не работает/в отпуске/отсутствует(причина)).
* Предусмотреть постраничную навигацию и возможность выбирать количество пользователей на странице: 10,30,50,100,все
* Предусмотреть возможность перейти в профиль любого из пользователей
*/
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
?>
<div class="employees">
	<table class="bx-users-table data-table">
		<thead>
			<th>Фамилия</th>
			<th>Имя</th>
			<th>Отчество</th>
			<th>Должность</th>
			<th>Рабочий телефон</th>
			<th>Фамилия начальника</th>
			<th>Количество подчиненных</th>
			<th>Статус пользователя</th>
		</thead>
		<tbody>
			<? foreach ($arResult['USERS'] as $key => $user): ?>
				<tr>
					<td><a href="<?=$user['PROFILE_URL'];?>"><?=$user['LAST_NAME'];?></a></td>
					<td><?=$user['NAME'];?></td>
					<td><?=$user['SECOND_NAME'];?></td>
					<td><?=$user['WORK_POSITION'];?></td>
					<td><?=$user['WORK_PHONE'];?></td>
					<td><?=$user['HEAD'];?></td>
					<td><?=$user['SUBORDINATES'];?></td>
					<td><?=$user['STATUS'];?></td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
	<div>
		<?
		$APPLICATION->IncludeComponent(
		   "bitrix:main.pagenavigation",
		   "test",
		   array(
		      "NAV_OBJECT" => $arResult['NAVIGATION'],
		      "SEF_MODE" => "N",
		   ),
		   false
		);
		?>
	</div>
</div>