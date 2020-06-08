<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

/** @var PageNavigationComponent $component */
$component = $this->getComponent();

$this->setFrameMode(true);
?>

<div class="bx-pagination">
	<div class="bx-pagination-container">
		<ul>
			<?if ($arResult["CURRENT_PAGE"] > 1):?>
				<?if ($arResult["CURRENT_PAGE"] > 2):?>
					<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1, $arResult["PAGE_SIZE"]))?>"><span><?echo GetMessage("round_nav_back")?></span></a></li>
				<?else:?>
					<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1, $arResult["PAGE_SIZE"]))?>"><span><?echo GetMessage("round_nav_back")?></span></a></li>
				<?endif?>
					<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate(1, $arResult["PAGE_SIZE"]))?>"><span>1</span></a></li>
			<?else:?>
					<li class="bx-pag-prev"><span><?echo GetMessage("round_nav_back")?></span></li>
					<li class="bx-active"><span>1</span></li>
			<?endif?>

			<?
			$page = $arResult["START_PAGE"] + 1;
			while($page <= $arResult["END_PAGE"]-1):
			?>
				<?if ($page == $arResult["CURRENT_PAGE"]):?>
					<li class="bx-active"><span><?=$page?></span></li>
				<?else:?>
					<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($page, $arResult["PAGE_SIZE"]))?>"><span><?=$page?></span></a></li>
				<?endif?>
				<?$page++?>
			<?endwhile?>

			<?if($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
				<?if($arResult["PAGE_COUNT"] > 1):?>
					<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["PAGE_COUNT"], $arResult["PAGE_SIZE"]))?>"><span><?=$arResult["PAGE_COUNT"]?></span></a></li>
				<?endif?>
					<li class="bx-pag-next"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]+1, $arResult["PAGE_SIZE"]))?>"><span><?echo GetMessage("round_nav_forward")?></span></a></li>
			<?else:?>
				<?if($arResult["PAGE_COUNT"] > 1):?>
					<li class="bx-active"><span><?=$arResult["PAGE_COUNT"]?></span></li>
				<?endif?>
					<li class="bx-pag-next"><span><?echo GetMessage("round_nav_forward")?></span></li>
			<?endif?>
		</ul>
		<div style="clear:both"></div>
		<div>
			<select name="" class="adm-select" onchange="changePageSize();">
				<?foreach($arResult["PAGE_SIZES"] as $size):?>
					<option value="<?echo $size?>"<?if($arResult["PAGE_SIZE"] == $size) echo ' selected="selected"'?>><?echo $size?></option>
				<?endforeach;?>
				<?if($arResult["SHOW_ALL"]):?>
					<option value="0"<?if($arResult["ALL_RECORDS"]) echo ' selected="selected"'?>>Все</option>
				<?endif;?>
			</select>
		</div>
	</div>
</div>
