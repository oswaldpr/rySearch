(function($) {
    $(document).ready(function() {
        const $body = $("body");

        //http://www.daterangepicker.com/
        //https://openclassrooms.com/forum/sujet/traduction-du-plugin-date-range-picker
        const $rangeDatePicker = $("#RY_Filter_Date");
            if($rangeDatePicker.length > 0) {
                $rangeDatePicker.daterangepicker({
                    opens: 'left',
                    autoApply: true,
                    //minDate: new Date(),
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
                    const dateRange = startDate + ' --> ' + endDate;
                    $('#RY_Filter_Date_Values').val(dateRange);
                    updateDatesAction(dateRange);
                    // updateULSelect($('#DestinationHtml'), dateRange);
                    // updateULSelect($('#ProfHtml'), dateRange);
                    // updateULSelect($('#TypeHtml'), dateRange);
                });
            }

        window.addEventListener("click", function(e){
            const isInput = $(e.target).is('.rysbd_select_input, .active');
            const $rysbd_select = $('.ry_ul_select.active');
            const $parent = $(e.target).closest('.ry_ul_select');
            const isInsideSelectSearchBox = $parent.length > 0;
            if (!isInput && !isInsideSelectSearchBox && !$rysbd_select.hasClass("hideBox")){
                // Clicked outside the box
                $rysbd_select.addClass("hideBox");
                $rysbd_select.removeClass("active");
                $parent.find('.rysbd_select_input.active').removeClass("active");
            }
        });

        $body.on("keydown", "#RY_Filter_Date_Values", function (e) {
            e.preventDefault();
        });

        $body.on("click", ".ry_li_select_value", function (e) {
            $('.waiting-result-Display').show();
        });

        $body.on("click", ".clearSearch", function (e) {
            $('.waiting-result-Display').show();
        });

        $body.on("click", "#RY_Filter_Btn button", function (e) {
            $('.waiting-result-Display').show();
        });

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
            $('.waiting-result-Display').show();
        });

        $("#RY_Filter_Date_Values").on("click", function () {
            const $this = $(this);
            const isOpen = $this.attr('isOpen');
            if(isOpen === 'true'){
                $('.daterangepicker').hide();
                $this.attr('isOpen', false);
            } else {
                $this.attr('isOpen', true);
                $('#RY_Filter_Date').click();
            }
        });
    });


    function getSplitUrl(urlBase, char) {
        let mainUrl = '';
        try {
            mainUrl = urlBase.split(char);
        } catch(e) {
            if(urlBase){
                mainUrl = [urlBase];
            }
        }

        return mainUrl
    }

    function updateDatesAction(dateRange) {
        const $form = $('#RYSBDForm');
        const formAction = $form.attr('action');
        const urlBase = getBaseUrl(formAction, dateRange);

        $('.waiting-result-Display').show();
        window.location.href = urlBase;

        // $form.prop('action', urlBase);
        //
        // const $monthHtml = $('#MonthHTML');
        // const liSelected = $monthHtml.find('li.ulSelected');
        // const divName = $monthHtml.find('.rysbd_select_input');
        // liSelected.find('.spanSelected').detach();
        // liSelected.removeClass('ulSelected');
        // divName.html('Mois');
        //
        // let mainUrlArray = getSplitUrl(urlBase, '?');
        // if(mainUrlArray[1]){
        //     let mainUrlParamArray = getSplitUrl(mainUrlArray[1], '&');
        //     if(mainUrlParamArray.length === 1 && mainUrlParamArray[0].indexOf('dates=') === 0){
        //         $('.waiting-result-Display').show();
        //         window.location.href = urlBase;
        //     }
        // }
    }

    function getBaseUrl(href, dateRange) {
        let mainUrlArray = getSplitUrl(href, '?');
        let urlBase = mainUrlArray[0];

        if(mainUrlArray[1]){
            let char = '';
            let urlArray = getSplitUrl(mainUrlArray[1], '&');
            const doNotContainDate = urlBase.indexOf('dates=') === -1;
            const doNotContainMonth = urlBase.indexOf('mois=') === -1;

            urlArray.forEach(function($thisLi){
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

            if(urlBase.indexOf('?') === -1){
                char = '?';
            } else {
                char = '&';
            }

            if(urlBase.indexOf('dates=') === -1){
                urlBase = urlBase + char + 'dates=' + dateRange;
            }

        } else {
            urlBase = urlBase + '?dates=' + dateRange;
        }

        return urlBase;
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

}(jQuery));

