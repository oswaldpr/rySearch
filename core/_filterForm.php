<?php


use rySearch\core\rySearchController;

$keyword = rySearchController::urlGetParameter(RY_SEARCH_PARAM_KEY);
$actionFormUrl = rySearchController::getActionFormUrl();
?>

<form id="RYSBDForm" name="RYSBDForm" accept-charset="utf-8" action="<?php echo $actionFormUrl; ?>">
    <div id="RYFilterForm">
        <div id="RY_Filter_Inputs">
            <input type="text" id="RY_Filter_Text" class="RY_Filter_Row RY_Filter_Row_Left"
                   name="<?php echo RY_SEARCH_PARAM_KEY; ?>" value="<?php echo $keyword; ?>"
                   placeholder='Essayez "Bali" ou "Vinyasa"'
            >
            <input type="text" id="RY_Filter_Date" class="RY_Filter_Row RY_Filter_Row_Right"
                   placeholder='Date de début -> Date de fin'
            />
            <input type="hidden" id="RY_Filter_Date_Values" class="" name="<?php echo RY_SEARCH_PARAM_CALENDAR; ?>">
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
</form>
