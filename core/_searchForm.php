<?php


use rySearch\core\rySearchController;

$keyword = rySearchController::urlGetParameter(RY_SEARCH_PARAM_KEY);
$duration = rySearchController::urlGetParameter(RY_SEARCH_PARAM_DURATION);
$isMonth = strlen($duration) > 4;
$durationTxt = rySearchController::getSelectedDuration($duration);
$dateTxt = $isMonth ? '' : rySearchController::urlGetParameter(RY_SEARCH_PARAM_DATE);
$beforeDayTxt = $durationTxt !== '' && $dateTxt !== '' ? ', ' : '';
$isChecked = $duration && $duration !== 'all' ? '' : 'checked="checked"';
$actionFormUrl = rySearchController::getActionFormUrl();
$dayDurationHTML = rySearchController::getDayDurationHTML($duration);
$monthDurationHTML = rySearchController::getMonthDurationHTML($duration);
?>

<form id="RYSBDForm"
      name="RYSBDForm"
      accept-charset="utf-8"
      action="<?php echo $actionFormUrl; ?>">

    <div id="RYSBDMainForm">
        <div id="RYSBD_Btn" class="RYSBDBtn RYSBD_search_row"></div>
        <div id="RYSBD_Input" class="RYSBD_search_row">
            <input type="text" class="inputfield" name="<?php echo RY_SEARCH_PARAM_KEY; ?>" value="<?php echo $keyword; ?>"
                   placeholder="<?php _e('Essayez...', RY_SEARCH_TXT_DOMAIN); ?>">
        </div>
        <div id="RYSBD_Date" for="RYSBD_DatePicker" class="RYSBD_search_row">
            <div id="RYSBD_DatePicker" class="input-group date dureeEtDateElem" data-date-format="yyyy-mm-dd">
                <input  id="RYSBD_DatePickerValue" class="form-control" name="<?php echo RY_SEARCH_PARAM_DATE; ?>" type="text" value="<?php echo $dateTxt; ?>" readonly />
                <span id="calendarIcon"  class="input-group-addon dureeEtDateBox"><i class="glyphicon glyphicon-calendar"></i></span>
            </div>

            <div id="RYSBD_DureeEtDate" class="dureeEtDateBox">
                <div class="dureeEtDateTxt">Durée et date d'arrivée</div>
                <span id="durationSelectedTxt" class="dureeEtDateElem"><?php echo $durationTxt; ?></span>
                <span id="DateTxt" class="dureeEtDateElem"><?php echo $beforeDayTxt . $dateTxt; ?></span>
            </div>

            <div class="clear"></div>
        </div>
    </div>

    <div id="RYSBD_Date_Box" class="hideBox">
        <div id="RYSBD_Date_Box_Calendar">
            <!--  The calendar will be injected here-->
        </div>
        <div id="RYSBD_Date_Box_Duration">
            <div class="daySection">
                <p class="RYSBD_Date_Box_title"><?php echo __('Durée')?></p>
                <?php echo $dayDurationHTML; ?>
            </div>
            <hr>
            <div class="monthSection">
                <p class="RYSBD_Date_Box_title"><?php echo __('Mois du séjour')?></p>
                <?php echo $monthDurationHTML; ?>
            </div>
            <input type="radio" class="multiChoiceGroupInput" style="display: none"
                   id="duration_all" name="<?php echo RY_SEARCH_PARAM_DURATION; ?>" value="all" <?php echo $isChecked; ?>>
        </div>
        <div class="clear"></div>
    </div>
</form>
