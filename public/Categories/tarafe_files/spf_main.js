"use strict";

var $j = jQuery.noConflict();

$j(document).bind("gform_post_render", function (event, form_id) {
    var spfMainData = window["spfMainData_" + form_id];

    if (!spfMainData) {
        return;
    }

    var arr_el = spfMainData.elements;

    console.log(arr_el);

    $j.each(arr_el, function (index, name) {
        var obj = name;

        var options = {
            nationalMode: false,
            utilsScript: spfMainData.utilsScript,
            separateDialCode: true,
        };

        for (var i = 0; i < obj.length; i++) {
            var inputTel = obj[0],
                autoIp = obj[1],
                intCountry = obj[2],
                preCountry = obj[3],
                hiddenInput = obj[4],
                multi = obj[5];

            if (!multi) {
                options.hiddenInput = hiddenInput;
            }

            if (
                (intCountry != "none" && autoIp === false) ||
                (intCountry != "none" && autoIp === "")
            ) {
                options.initialCountry = intCountry;
            }

            if (preCountry != "none") {
                options.preferredCountries = preCountry;
            }

            if (autoIp === true) {
                options.initialCountry = "auto";
                options.geoIpLookup = function (success, failure) {
                    $j.get("https://ipinfo.io", function () {}, "jsonp").always(
                        function (resp) {
                            var countryCode =
                                resp && resp.country ? resp.country : "";
                            success(countryCode);
                        }
                    );
                };
            }
        }

        $j(inputTel).intlTelInput(options);
    });

    /*
     *   Phone number validation
     */

    $j.each(arr_el, function (index) {
        var inputId = "#" + index;
        var teleInput = $j(inputId);

        teleInput
            .parent()
            .parent()
            .after(
                '<span class="spf-phone valid-msg hide">✓ Valid number</span>'
            );
        teleInput
            .parent()
            .parent()
            .after(
                '<span class="spf-phone error-msg hide">&#x2715 Invalid number</span>'
            );

        teleInput.blur(function () {
            isInputValid($j(this));
        });

        teleInput.keydown(function () {
            hideInputValidation($j(this));
        });

        function hideInputValidation(phoneID) {
            phoneID.removeClass("error");
            phoneID
                .parent()
                .parent()
                .parent()
                .find(".valid-msg")
                .addClass("hide");
            phoneID
                .parent()
                .parent()
                .parent()
                .find(".error-msg")
                .addClass("hide");
        }

        function isInputValid(phoneID) {
            var errorMsg = phoneID
                    .parent()
                    .parent()
                    .parent()
                    .find(".error-msg"),
                validMsg = phoneID
                    .parent()
                    .parent()
                    .parent()
                    .find(".valid-msg");

            console.log(phoneID.parent());
            if ($j.trim(phoneID.val())) {
                if (phoneID.intlTelInput("isValidNumber")) {
                    validMsg.removeClass("hide");
                    errorMsg.addClass("hide");
                } else {
                    phoneID.addClass("error");
                    errorMsg.removeClass("hide");
                    validMsg.addClass("hide");
                }
            }
        }
    });
});
