STUDIP.Dozentenrechte = {

    addPersons: function (form) {
        if ($(form).find($('select[name$="_selectbox[]"] option:selected')).length > 0) {
            if ($('#rights_added_users').length > 0) {
                var ul = $('#rights_added_users');
            } else {
                var ul = $('<ul>').attr('id', 'rights_added_users');
            }
            $(form).find($('select[name$="_selectbox[]"] option:selected')).each(function (index) {
                if ($('li#rights_added_user_' + $(this).val()).length == 0) {
                    var li = $('<li>').
                        attr('id', 'rights_added_user_' + $(this).val());
                    li.append($(this).text().replace(/(.+, .+)\r?\n(.*) (\(.+\))/, '\$1 \$3'));
                    var userid = $('<input>').
                        attr('type', 'hidden').
                        attr('name', 'user[]').
                        attr('value', $(this).val());
                    li.append(userid);
                    ul.append(li);
                }
            });
            $('#rightsfor').append(ul);
        }
        jQuery(form).closest('.ui-dialog-content').dialog('close');
        return false;
    }

}
