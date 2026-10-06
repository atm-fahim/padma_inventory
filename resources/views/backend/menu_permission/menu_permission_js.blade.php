
<script>
    $('.CheckMaster').on('click', function () {
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {
            $("#main_" + menu_id).prop("checked", false);
        }
    });

    $('.chksuball').on('click', function () {
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var sub_cls_name = "sub_pr_" + menu_id;
        var is_checked = $(this).is(":checked");
        if (is_checked == true) {
            $("." + class_name).prop("checked", true);
            $("." + sub_cls_name).prop("checked", true);
        } else {
            $("." + class_name).prop("checked", false);
            $("." + sub_cls_name).prop("checked", false);
        }
    });

    $('#select-all').click(function (event) {
        if (this.checked) {
            // Iterate each checkbox
            $(':checkbox').each(function () {
                this.checked = true;
            });
        } else {
            $(':checkbox').each(function () {
                this.checked = false;
            });
        }
    });
    $('.allchk').on('change', function () {
        $(this).closest('tr').find(':checkbox').prop('checked', this.checked);
    });
    $('.crchk').on('change', function () {
        var edit_chk = $(this).closest('tr').find('.editchk').is(':checked');
        var delete_chk = $(this).closest('tr').find('.deletechk').is(':checked');
        var req_chk = $(this).closest('tr').find('.reqchk').is(':checked');
        var app_chk = $(this).closest('tr').find('.appchk').is(':checked');
        var viewchk_chk = $(this).closest('tr').find('.viewchk').is(':checked');
        var cancel_chk = $(this).closest('tr').find('.cancelChk').is(':checked');
        if (edit_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (delete_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (req_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (app_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (viewchk_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        }else if (cancel_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else {
            $(this).closest('tr').find('.allchk').prop("checked", this.checked);
        }
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {

            $("#main_" + menu_id).prop("checked", false);
        }
    });
    $('.editchk').on('change', function () {
        var cr_chk = $(this).closest('tr').find('.crchk').is(':checked');
        var delete_chk = $(this).closest('tr').find('.deletechk').is(':checked');
        var viewchk_chk = $(this).closest('tr').find('.viewchk').is(':checked');
        var req_chk = $(this).closest('tr').find('.reqchk').is(':checked');
        var app_chk = $(this).closest('tr').find('.appchk').is(':checked');
        var cancel_chk = $(this).closest('tr').find('.cancelChk').is(':checked');
        if (cr_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (req_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (app_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (delete_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (viewchk_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        }else if (cancel_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else {
            $(this).closest('tr').find('.allchk').prop("checked", this.checked);
        }
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {

            $("#main_" + menu_id).prop("checked", false);
        }
    });
    $('.deletechk').on('change', function () {
        var cr_chk = $(this).closest('tr').find('.crchk').is(':checked');
        var edit_chk = $(this).closest('tr').find('.editchk').is(':checked');
        var viewchk_chk = $(this).closest('tr').find('.viewchk').is(':checked');
        var req_chk = $(this).closest('tr').find('.reqchk').is(':checked');
        var app_chk = $(this).closest('tr').find('.appchk').is(':checked');
        var cancel_chk = $(this).closest('tr').find('.cancelChk').is(':checked');
        if (cr_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (req_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (app_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (edit_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (viewchk_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        }else if (cancel_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else {
            $(this).closest('tr').find('.allchk').prop("checked", this.checked);
        }
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {

            $("#main_" + menu_id).prop("checked", false);
        }
    });
    $('.viewchk').on('change', function () {
        var cr_chk = $(this).closest('tr').find('.crchk').is(':checked');
        var edit_chk = $(this).closest('tr').find('.editchk').is(':checked');
        var delete_chk = $(this).closest('tr').find('.deletechk').is(':checked');
        var req_chk = $(this).closest('tr').find('.reqchk').is(':checked');
        var app_chk = $(this).closest('tr').find('.appchk').is(':checked');
        var cancel_chk = $(this).closest('tr').find('.cancelChk').is(':checked');
        if (cr_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (req_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (app_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (edit_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (delete_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        }else if (cancel_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else {
            $(this).closest('tr').find('.allchk').prop("checked", this.checked);
        }
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {

            $("#main_" + menu_id).prop("checked", false);
        }

    });
    $('.reqchk').on('change', function () {
        var cr_chk = $(this).closest('tr').find('.crchk').is(':checked');
        var edit_chk = $(this).closest('tr').find('.editchk').is(':checked');
        var delete_chk = $(this).closest('tr').find('.deletechk').is(':checked');
        var app_chk = $(this).closest('tr').find('.appchk').is(':checked');
        var viewchk_chk = $(this).closest('tr').find('.viewchk').is(':checked');
        var cancel_chk = $(this).closest('tr').find('.cancelChk').is(':checked');
        if (cr_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (app_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (edit_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (delete_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (viewchk_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        }else if (cancel_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else {
            $(this).closest('tr').find('.allchk').prop("checked", this.checked);
        }
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {

            $("#main_" + menu_id).prop("checked", false);
        }

    });

    $('.appchk').on('change', function () {
        var cr_chk = $(this).closest('tr').find('.crchk').is(':checked');
        var edit_chk = $(this).closest('tr').find('.editchk').is(':checked');
        var delete_chk = $(this).closest('tr').find('.deletechk').is(':checked');
        var req_chk = $(this).closest('tr').find('.reqchk').is(':checked');
        var viewchk_chk = $(this).closest('tr').find('.viewchk').is(':checked');
        var cancel_chk = $(this).closest('tr').find('.cancelChk').is(':checked');
        if (cr_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (edit_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (delete_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (viewchk_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (req_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        }else if (cancel_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else {
            $(this).closest('tr').find('.allchk').prop("checked", this.checked);
        }
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {

            $("#main_" + menu_id).prop("checked", false);
        }

    });
    $('.cancelChk').on('change', function () {
        var cr_chk = $(this).closest('tr').find('.crchk').is(':checked');
        var edit_chk = $(this).closest('tr').find('.editchk').is(':checked');
        var delete_chk = $(this).closest('tr').find('.deletechk').is(':checked');
        var req_chk = $(this).closest('tr').find('.reqchk').is(':checked');
        var viewchk_chk = $(this).closest('tr').find('.viewchk').is(':checked');
        var cancel_chk = $(this).closest('tr').find('.cancelChk').is(':checked');
        if (cr_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (edit_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (delete_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (viewchk_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else if (req_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        }else if (cancel_chk == true) {
            $(this).closest('tr').find('.allchk').prop("checked", true);
        } else {
            $(this).closest('tr').find('.allchk').prop("checked", this.checked);
        }
        var menu_id = $(this).data('id');
        var class_name = "sub_" + menu_id;
        var is_checked = $('.' + class_name).is(":checked");
        if (is_checked == true) {
            $("#main_" + menu_id).prop("checked", true);
        } else {

            $("#main_" + menu_id).prop("checked", false);
        }

    });
</script>
