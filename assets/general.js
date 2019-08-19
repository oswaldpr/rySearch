(function($) {
    $(document).ready(function() {
        const $body = $("body");
        const $priceForm = $('.woocommerce.widget_price_filter form');

        // https://www.malot.fr/bootstrap-datetimepicker/demo.php
        const $datePicker = $("#RYSBD_DatePicker");
        if($datePicker.length > 0){
            (function($){
                $.fn.datepicker.dates['fr'] = {
                    days: ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
                    daysShort: ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
                    daysMin: ["dim", "lun", "mar", "mer", "jeu", "ven", "sam"],
                    months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
                    monthsShort: ["janv.", "févr.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."],
                    today: "Aujourd'hui",
                    monthsTitle: "Mois",
                    clear: "Effacer",
                    weekStart: 1,
                    format: "dd/mm/yyyy"
                };
            }(jQuery));

            $datePicker.datepicker({
                language: 'fr',
                autoclose: false,
                todayHighlight: true
            }).datepicker('update', new Date());
            $datePicker.data('datepicker').hide = function () {};
            $datePicker.datepicker('show');
        }


        $("#RYSBD_Date_Box_Calendar").html($('.datepicker.datepicker-dropdown'));

        window.addEventListener("click", function(e){
            const $RYSBD_Date_Box = $('#RYSBD_Date_Box');
            const isCalendar = $(e.target).hasClass('day') || $(e.target).hasClass('month') || $(e.target).hasClass('year');
            const isDureeEtDate = $(e.target).closest('#RYSBD_Date').length > 0;
            const isInsideDureeBox = $(e.target).closest('#RYSBD_Date_Box').length > 0;
            if (!isDureeEtDate && !isCalendar && !isInsideDureeBox && !$RYSBD_Date_Box.hasClass("hideBox")){
                // Clicked outside the box
                $RYSBD_Date_Box.addClass("hideBox");
            }

            const isInput = $(e.target).is('.rysbd_select_input, .active');
            const $rysbd_select = $('.rysbd_select.active');
            const $parent = $(e.target).closest('.rysbd_select');
            const isInsideSelectSearchBox = $parent.length > 0;
            if (!isInput && !isInsideSelectSearchBox && !$rysbd_select.hasClass("hideBox")){
                // Clicked outside the box
                $rysbd_select.addClass("hideBox");
                $rysbd_select.removeClass("active");
                $parent.find('.rysbd_select_input.active').removeClass("active");
            }

        });

        $datePicker.on("click", function () {
            $("#RYSBD_Date_Box_Calendar").html($('.datepicker.datepicker-dropdown'));
        });

        $("#RYSBD_Date").on("click", function (e) {
            const isClearDate = $(e.target).is('.clearSearch');
            if(!isClearDate){
                $('#RYSBD_Date_Box').toggleClass("hideBox");
            }
        });

        $body.on("mousedown", ".durationGroupSelected", function(){
            const $this = $(this).find('input');
            const isChecked = $this.is(':checked');
            if(isChecked){
                selectedDefaultDuration($this);
            }
        });
        
        $(".durationChoice").on("change", function (){
            const $this = $(this);
            const $parent = $this.closest('.durationSingleChoice');
            const $RYSBD_Date_Box_Duration = $('#RYSBD_Date_Box_Duration');
            const isChecked = $this.is(':checked');
            const label = $parent.find('label').html();

            $RYSBD_Date_Box_Duration.find(".durationGroupSelected").removeClass("durationGroupSelected");
            $RYSBD_Date_Box_Duration.find(".durationSelected").removeClass("durationSelected");

            if(isChecked){
                $('#durationSelectedTxt').html(label);
                $parent.addClass("durationGroupSelected");
                $this.addClass("durationSelected");
            }
        });

        $("#RYSBD_DatePickerValue").on("change", function () {
            const $this = $(this);
            var value = $this.val();
            const isMonth = $('.durationChoice.month:checked').val();
            const withDurationTxt = $('.day.durationSelected').length > 0;
            if(!isMonth){
                if(withDurationTxt){
                    value = ', '+value;
                }
                $('#DateTxt').html(value);
            } else {
                $('#RYSBD_Date_Box_Calendar').find('td.active').removeClass('active');
            }
        });

        $("input.durationChoice.day").on("change", function (){
            const withDurationTxt = $('.day.durationSelected').length > 0;
            var value = $('#RYSBD_DatePickerValue').val();
            if(value === ''){
                value = getCurrentDate();
                $('#RYSBD_Date_Box_Calendar').find('td.active').removeClass('active');
            }
            if(withDurationTxt){
                value = ', '+value;
            }
            $('#DateTxt').html(value);
        });

        $("input.durationChoice.month").on("change", function (){
            const $this = $(this);
            const isChecked = $this.is(':checked');
            if(isChecked){
                $('#RYSBD_Date_Box_Calendar').find('td.active').removeClass('active');
                clearDate();
            }
        });

        $(".rysbd_select_input").on("click", function () {

            const $this = $(this);
            const $parent = $this.closest('.et_pb_widget');
            const $selectBox = $parent.find('.rysbd_select');
            const isClose = $selectBox.hasClass("hideBox");

            $('.et_pb_widget .rysbd_select_input.active').removeClass('active');
            $('.et_pb_widget .rysbd_select.active').removeClass('active');
            $('.et_pb_widget .rysbd_select').addClass('hideBox');

            $selectBox.toggleClass("hideBox", !isClose);
            $selectBox.toggleClass("active", isClose);
            $parent.find('.rysbd_select_input').toggleClass("active", isClose);
        });

        $(".rysbd_select.active li").on("click", function () {
            $(this).find('a').click();
        });

        $("#RYSBD_Btn").on("click", function () {
            $('#RYSBDForm').submit();
        });

        $priceForm.on("submit", function (e) {
            e.preventDefault();
            $('#RYSBDForm').serialize();
            $priceForm.serialize();

            const $this = $(this);
            const $parent = $this.closest('.et_pb_widget');
            const $selectBox = $parent.find('.rysbd_select');
            const isClose = $selectBox.hasClass("hideBox");
            $selectBox.toggleClass("hideBox", !isClose);
            $selectBox.toggleClass("active", isClose);
            $parent.find('.rysbd_select_input').toggleClass("active", isClose);
        });

    });

    function selectedDefaultDuration($this) {
        if(!$this){
            $this = $('#RYSBD_Date_Box_Duration').find('input.durationChoice');
        }

        window.setTimeout(function() {
            $this.prop('checked', false);
            $this.removeClass("durationSelected");
            $this.closest('.durationSingleChoice').removeClass("durationGroupSelected");
            $("#duration_all").prop("checked", true);
            $('#DateTxt').html($("#RYSBD_DatePickerValue").val());
            $('#durationSelectedTxt').html('');
        }, 200);
    }

    function clearDate() {
        const today = getCurrentDate();
        const $RYSBD_DatePickerValue = $("#RYSBD_DatePickerValue");
        const $DateTxt = $('#DateTxt');
        const $inputVal = $RYSBD_DatePickerValue.val();
        const dateTxtVal = $DateTxt.html();
        const isMonth = $('.durationChoice.month:checked').val().length > 0;

        if($inputVal !== today && dateTxtVal !== '' || isMonth){
            $RYSBD_DatePickerValue.val('');
            $DateTxt.html('');
        }
    }

    function getCurrentDate() {
        //Thu Mar 21 2019 17:25:38 GMT+1000 {}
        const fullDate = new Date();
        //convert month to 2 digits
        const twoDigitMonth = ((fullDate.getMonth().length+1) === 1)? (fullDate.getMonth()+1) : '0' + (fullDate.getMonth()+1);

        return fullDate.getFullYear() + "-" + twoDigitMonth + "-" + fullDate.getDate(); //2019-03-21;
    }
}(jQuery));
