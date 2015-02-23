<?= $msg ?>
<form class="studip_form" method="post">
    <fieldset><legend><?= dgettext('dozentenrechte', 'Neuen Dozentenrechte Antrag stellen') ?></legend>
        <select name='rights'>
            <option value='dozent'><?= dgettext('dozentenrechte', 'Dozentenrechte') ?></option>
            <option value='tutor'><?= dgettext('dozentenrechte', 'Tutorrechte') ?></option>
        </select>
        <label>
            <?= dgettext('dozentenrechte', 'für') ?>
            <?= QuickSearch::get("user", new FullUserSearch('user_id'))->setInputStyle("width: 240px")->defaultValue(Request::get('user'), Request::get('user_parameter'))->render(); ?>
        </label>
        <label>
            <?= dgettext('dozentenrechte', 'an der Einrichtung') ?>
            <?= QuickSearch::get("inst", new StandardSearch('Institut_id'))->setInputStyle("width: 240px")->defaultValue(Request::get('inst'), Request::get('inst_parameter'))->render(); ?>
        </label>
        <fieldset><legend><?= dgettext('dozentenrechte', 'Von') ?></legend>
            <label>
                <input type="radio" name="from_type" value="0" CHECKED>
                <?= dgettext('dozentenrechte', 'Ab sofort') ?>
            </label>
            <label>
                <input type="radio" name="from_type" value="1" <?= Request::get('from_type') ? "CHECKED" : "";?>>
                <?= dgettext('dozentenrechte', 'Ab Datum') ?>
                <input name="from" type="text" placeholder="<?= dgettext('dozentenrechte', 'Datum') ?>" class="datepicker" value="<?= Request::get('from') ?>"/>
            </label>
        </fieldset>
        <fieldset><legend><?= dgettext('dozentenrechte', 'Bis') ?></legend>
            <label>
                <input type="radio" name="to_type" value="0" CHECKED>
                <?= dgettext('dozentenrechte', 'Unbegrenzt') ?>
            </label>
            <label>
                <input type="radio" name="to_type" value="1" <?= Request::get('to_type') ? "CHECKED" : "";?>>
                <?= dgettext('dozentenrechte', 'Bis Datum') ?>
                <input name="to" type="text" placeholder="<?= dgettext('dozentenrechte', 'Datum') ?>" value="<?= Request::get('to') ?>" class="datepicker" />
            </label>
        </fieldset>
    </fieldset>
    <?= \Studip\Button::create(dgettext('dozentenrechte', 'Antrag stellen'), 'save') ?>
</form>

<script>
    $(function() {
        $(".datepicker").datepicker();
    });
</script>