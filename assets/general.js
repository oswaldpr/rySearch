(function($) {
    $(document).ready(function() {
        const $body = $("body");
        const $priceForm = $('.woocommerce.widget_price_filter form');

        //http://www.daterangepicker.com/
        //https://openclassrooms.com/forum/sujet/traduction-du-plugin-date-range-picker
        const $rangeDatePicker = $("#RY_Filter_Date");
            if($rangeDatePicker.length > 0) {
            $rangeDatePicker.daterangepicker({
                opens: 'left',
                autoApply: true,
                minDate:new Date(),
                locale: {
                    separator: " --> ",
                    format: 'DD/MM/YYYY',
                    daysOfWeek: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
                    monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août",
                        "Septembre", "Octobre", "Novembre", "Décembre"],
                }
            }, function(start, end) {
                const startDate = start.format('YYYY-MM-DD');
                const endDate = end.format('YYYY-MM-DD');
                const dateRange = startDate + ' to ' + endDate;
                $('#RY_Filter_Date_Values').val(dateRange);
                updateFormAction(dateRange);
                updateULSelect($('#DestinationHtml'), dateRange);
                updateULSelect($('#ProfHtml'), dateRange);
                updateULSelect($('#TypeHtml'), dateRange);
            });
        }

        $("#RYSBD_Date_Box_Calendar").html($('.datepicker.datepicker-dropdown'));

        // window.addEventListener("click", function(e){
        //     const $RYSBD_Date_Box = $('#RYSBD_Date_Box');
        //     const isCalendar = $(e.target).hasClass('day') || $(e.target).hasClass('month') || $(e.target).hasClass('year');
        //     const isDureeEtDate = $(e.target).closest('#RYSBD_Date').length > 0;
        //     const isInsideDureeBox = $(e.target).closest('#RYSBD_Date_Box').length > 0;
        //     if (!isDureeEtDate && !isCalendar && !isInsideDureeBox && !$RYSBD_Date_Box.hasClass("hideBox")){
        //         // Clicked outside the box
        //         $RYSBD_Date_Box.addClass("hideBox");
        //     }
        //
        //     const isInput = $(e.target).is('.rysbd_select_input, .active');
        //     const $rysbd_select = $('.ry_ul_select.active');
        //     const $parent = $(e.target).closest('.ry_ul_select');
        //     const isInsideSelectSearchBox = $parent.length > 0;
        //     if (!isInput && !isInsideSelectSearchBox && !$rysbd_select.hasClass("hideBox")){
        //         // Clicked outside the box
        //         $rysbd_select.addClass("hideBox");
        //         $rysbd_select.removeClass("active");
        //         $parent.find('.rysbd_select_input.active').removeClass("active");
        //     }
        // });

        $body.on("click", ".ry_li_select_value", function (e) {
            $('.waiting-result-Display').show();
        });

        $("#RYSBD_Date").on("click", function (e) {
            const isClearDate = $(e.target).is('.clearSearch');
            if(!isClearDate){
                $('#RYSBD_Date_Box').toggleClass("hideBox");
            }
        });

        // $body.on("mousedown", ".durationGroupSelected", function(){
        //     const $this = $(this).find('input');
        //     const isChecked = $this.is(':checked');
        //     if(isChecked){
        //         selectedDefaultDuration($this);
        //     }
        // });
        //
        // $(".durationChoice").on("change", function (){
        //     const $this = $(this);
        //     const $parent = $this.closest('.durationSingleChoice');
        //     const $RYSBD_Date_Box_Duration = $('#RYSBD_Date_Box_Duration');
        //     const isChecked = $this.is(':checked');
        //     const label = $parent.find('label').html();
        //
        //     $RYSBD_Date_Box_Duration.find(".durationGroupSelected").removeClass("durationGroupSelected");
        //     $RYSBD_Date_Box_Duration.find(".durationSelected").removeClass("durationSelected");
        //
        //     if(isChecked){
        //         $('#durationSelectedTxt').html(label);
        //         $parent.addClass("durationGroupSelected");
        //         $this.addClass("durationSelected");
        //     }
        // });
        //
        // $("#RYSBD_DatePickerValue").on("change", function () {
        //     const $this = $(this);
        //     var value = $this.val();
        //     const isMonth = $('.durationChoice.month:checked').val();
        //     const withDurationTxt = $('.day.durationSelected').length > 0;
        //     if(!isMonth){
        //         if(withDurationTxt){
        //             value = ', '+value;
        //         }
        //         $('#DateTxt').html(value);
        //     } else {
        //         $('#RYSBD_Date_Box_Calendar').find('td.active').removeClass('active');
        //     }
        // });
        //
        // $("input.durationChoice.day").on("change", function (){
        //     const withDurationTxt = $('.day.durationSelected').length > 0;
        //     var value = $('#RYSBD_DatePickerValue').val();
        //     if(value === ''){
        //         value = getCurrentDate();
        //         $('#RYSBD_Date_Box_Calendar').find('td.active').removeClass('active');
        //     }
        //     if(withDurationTxt){
        //         value = ', '+value;
        //     }
        //     $('#DateTxt').html(value);
        // });
        //
        // $("input.durationChoice.month").on("change", function (){
        //     const $this = $(this);
        //     const isChecked = $this.is(':checked');
        //     if(isChecked){
        //         $('#RYSBD_Date_Box_Calendar').find('td.active').removeClass('active');
        //         clearDate();
        //     }
        // });

        $(".rysbd_select_input").on("click", function () {
            const $this = $(this);
            const $parent = $this.closest('.RY_UL_Select_html');
            const $selectBox = $parent.find('.ry_ul_select');
            const isClose = $selectBox.hasClass("hideBox");

            $('.et_pb_widget .rysbd_select_input.active').removeClass('active');
            $('.et_pb_widget .ry_ul_select.active').removeClass('active');
            $('.et_pb_widget .ry_ul_select').addClass('hideBox');

            $selectBox.toggleClass("hideBox", !isClose);
            $selectBox.toggleClass("active", isClose);
            $parent.find('.ry_ul_select').toggleClass("active", isClose);
        });

        $(".ry_ul_select.active li").on("click", function () {
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
            const $selectBox = $parent.find('.ry_ul_select');
            const isClose = $selectBox.hasClass("hideBox");
            $selectBox.toggleClass("hideBox", !isClose);
            $selectBox.toggleClass("active", isClose);
            $parent.find('.rysbd_select_input').toggleClass("active", isClose);
        });

    });

    function updateFormAction(dateRange) {
        const $form = $('#RYSBDForm');
        const formAction = $form.attr('action');
        const urlBase = getBaseUrl(formAction, dateRange);
        $form.prop('action', urlBase);

        const $monthHtml = $('#MonthHTML');
        const liSelected = $monthHtml.find('li.ulSelected');
        const divName = $monthHtml.find('.rysbd_select_input');
        liSelected.find('.spanSelected').detach();
        liSelected.removeClass('ulSelected');
        divName.html('Mois');

        let mainUrlArray = urlBase.split('?');
        let mainUrlParamArray = mainUrlArray[1].split('$');
        if(mainUrlParamArray.length === 1 && mainUrlParamArray[0].indexOf('dates=') === 0){
            $('.waiting-result-Display').show();
            window.location.href = urlBase;
        }
    }

    function updateULSelect(ul, dateRange) {
        const liList = ul.find('.ry_li_select_value');
        liList.each(function(){
            const $this = $(this);
            const href = $this.attr("href");
            const urlBase = getBaseUrl(href, dateRange);
            $this.prop("href", urlBase);
        });
    }

    function getBaseUrl(href, dateRange) {
        let mainUrlArray = href.split('?');
        let urlArray = mainUrlArray[1].split('&');
        let urlBase = mainUrlArray[0];
        const doNotContainDate = urlBase.indexOf('dates=') === -1;
        const doNotContainMonth = urlBase.indexOf('mois=') === -1;

        urlArray.forEach(function($thisLi, $index){
            let char = '';
            const isNotDate = $thisLi.indexOf('dates=') === -1;
            const isNotMonth = $thisLi.indexOf('mois=') === -1;
            if(isNotMonth && doNotContainDate && doNotContainMonth){
                if(urlBase.indexOf('?') === -1){
                    char = '?';
                } else {
                    char = '&';
                }

                if(!isNotDate){
                    urlBase = urlBase + char + 'dates=' + dateRange;
                } else {
                    urlBase = urlBase + char + $thisLi;
                }
            }
        });

        return urlBase;
    }

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
