(function($) {
    $(document).ready(function() {
        const $body = $("body");
        const $priceForm = $('.woocommerce.widget_price_filter form');

        // https://www.malot.fr/bootstrap-datetimepicker/demo.php
        const $datePicker = $("#RYSBD_DatePicker");
        $datePicker.datepicker({
            autoclose: false,
            todayHighlight: true
        }).datepicker('update', new Date());
        $datePicker.data('datepicker').hide = function () {};
        $datePicker.datepicker('show');

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

        $("#RYSBD_Date").on("click", function () {
            $('#RYSBD_Date_Box').toggleClass("hideBox");
        });

        // $("#RYSBD_clearDateTime").on("click", function () {
        //     const $this = $(this);
        //     if($this){
        //         clearSearch(true)
        //     }
        // });

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

        $("#RYSBD_Btn").on("click", function () {
            $('#RYSBDForm').submit();
        });

        $(".rysbd_select_input").on("click", function () {
            const $this = $(this);
            const $parent = $this.closest('.et_pb_widget');
            const $selectBox = $parent.find('.rysbd_select');
            const isClose = $selectBox.hasClass("hideBox");
            $selectBox.toggleClass("hideBox", !isClose);
            $selectBox.toggleClass("active", isClose);
            $parent.find('.rysbd_select_input').toggleClass("active", isClose);
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
