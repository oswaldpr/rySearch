<?php


use rySearch\core\rySearchController;

$keyword = rySearchController::urlGetParameter(RY_SEARCH_PARAM_KEY);
$actionFormUrl = rySearchController::getActionFormUrl();
$dateRangeValue = rySearchController::getDateRangeDefaultValue();
?>

<form id="RYSBDForm" name="RYSBDForm" accept-charset="utf-8" action="<?php echo $actionFormUrl; ?>">
    <div id="RYFilterForm">
        <div id="RY_Filter_Inputs">
            <div  class="RY_Filter_Row RY_Filter_Row_Left">
                <input type="text" id="RY_Filter_Text" name="<?php echo RY_SEARCH_PARAM_KEY; ?>" value="<?php echo $keyword; ?>"
                       placeholder='Essayez "Bali" ou "Vinyasa"'
                >
                <div id="RYSBD_clearSearch">
                    <a class="clearSearch" href="<?php echo rySearchController::getDefaultSearchUrl(RY_SEARCH_PARAM_KEY); ?>">x</a>
                </div>
            </div>
            <div  class="RY_Filter_Row RY_Filter_Row_Right">
                <input type="text" id="RY_Filter_Date" value="<?php echo $dateRangeValue; ?>" />
                <div id="RYSBD_clearSearch">
                    <a class="clearSearch" href="<?php echo rySearchController::getDefaultSearchUrl(RY_SEARCH_PARAM_DATES); ?>">x</a>
                </div>
                <input type="hidden" id="RY_Filter_Date_Values" class="" name="<?php echo RY_SEARCH_PARAM_DATES; ?>">
            </div>

            <div class="clear"></div>
        </div>

        <div id="RY_Filter_Selects">
            <div class="RY_Filter_Selects_Row">
                <div id="DestinationHtml" class="RY_UL_Select_html RY_Filter_Row RY_Filter_Row_Left">
                    <?php echo rySearchController::buildDestinationHtml(); ?>
                </div>
                <div id="MonthHTML" class="RY_UL_Select_html RY_Filter_Row RY_Filter_Row_Right">
                    <?php echo rySearchController::buildMonthHTML(); ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="RY_Filter_Selects_Row">
                <div id="ProfHtml" class="RY_UL_Select_html RY_Filter_Row RY_Filter_Row_Left">
                    <?php echo rySearchController::buildProfHtml(); ?>
                </div>
                <div id="TypeHtml" class="RY_UL_Select_html RY_Filter_Row RY_Filter_Row_Right">
                    <?php echo rySearchController::buildTypeHtml(); ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div id="RY_Filter_Btn">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </div>
    </div>

    <div class="waiting-result-Display">
        <div id="waiting-result-content">
            <img src="<?php echo plugin_dir_url('rySearch') . 'rySearch/assets/ajax-loader-grey.gif'?>" alt="<?php echo __('Please wait ...') ?>">
        </div>
    </div>
</form>
