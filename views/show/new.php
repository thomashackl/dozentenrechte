<form class="studip_form" method="post" action="<?= $controller->url_for('show/given') ?>">
    <fieldset><legend><?= _('Neuen Dozentenrechte Antrag stellen') ?></legend>
        <label>
            <?= _('Dozentenrechte für') ?>
            <?= QuickSearch::get("username", new StandardSearch('username'))->setInputStyle("width: 240px")->render(); ?>
        </label>
        <label>
            <?= _('an der Einrichtung') ?>
            <?= QuickSearch::get("username", new StandardSearch('username'))->setInputStyle("width: 240px")->render(); ?>
        </label>
        <fieldset><legend><?= _('Von') ?></legend>
            <label>
                <input type="radio" name="from_type" value="1" CHECKED>
                <?= _('Ab sofort') ?>
            </label>
            <label>
                <input type="radio" name="from_type" value="2">
                <?= _('Ab Datum') ?>
                <input type="text" placeholder="<?= _('Datum') ?>" class="datepicker" />
            </label>
        </fieldset>
        <fieldset><legend><?= _('Bis') ?></legend>
            <label>
                <input type="radio" name="to_type" value="1" CHECKED>
                <?= _('Unbegrenzt') ?>
            </label>
            <label>
                <input type="radio" name="to_type" value="2">
                <?= _('Bis Datum') ?>
                <input type="text" placeholder="<?= _('Datum') ?>" class="datepicker" />
            </label>
        </fieldset>
    </fieldset>
    <?= \Studip\Button::create(_('Antrag stellen'), 'put') ?>
</form>

<script>
    $(function() {
        $(".datepicker").datepicker();
    });
</script>