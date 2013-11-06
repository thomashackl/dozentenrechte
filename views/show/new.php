<?= $msg ?>
<form class="studip_form" method="post">
    <fieldset><legend><?= _('Neuen Dozentenrechte Antrag stellen') ?></legend>
        <label>
            <?= _('Dozentenrechte für') ?>
            <?= QuickSearch::get("user", new StandardSearch('user_id'))->setInputStyle("width: 240px")->defaultValue(Request::get('user'), Request::get('user_parameter'))->render(); ?>
        </label>
        <label>
            <?= _('an der Einrichtung') ?>
            <?= QuickSearch::get("inst", new StandardSearch('Institut_id'))->setInputStyle("width: 240px")->defaultValue(Request::get('inst'), Request::get('inst_parameter'))->render(); ?>
        </label>
        <fieldset><legend><?= _('Von') ?></legend>
            <label>
                <input type="radio" name="from_type" value="0" CHECKED>
                <?= _('Ab sofort') ?>
            </label>
            <label>
                <input type="radio" name="from_type" value="1" <?= Request::get('from_type') ? "CHECKED" : "";?>>
                <?= _('Ab Datum') ?>
                <input type="text" placeholder="<?= _('Datum') ?>" class="datepicker" />
            </label>
        </fieldset>
        <fieldset><legend><?= _('Bis') ?></legend>
            <label>
                <input type="radio" name="to_type" value="0" CHECKED>
                <?= _('Unbegrenzt') ?>
            </label>
            <label>
                <input type="radio" name="to_type" value="1" <?= Request::get('to_type') ? "CHECKED" : "";?>>
                <?= _('Bis Datum') ?>
                <input type="text" placeholder="<?= _('Datum') ?>" class="datepicker" />
            </label>
        </fieldset>
    </fieldset>
    <?= \Studip\Button::create(_('Antrag stellen'), 'save') ?>
</form>

<script>
    $(function() {
        $(".datepicker").datepicker();
    });
</script>