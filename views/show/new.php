<form class="default" name="rightsform" method="post">
    <header>
        <h1><?= dgettext('dozentenrechte', 'Neuen Dozentenrechteantrag stellen') ?></h1>
    </header>
    <fieldset>
        <legend><?= dgettext('dozentenrechte', 'Personen und Einrichtung') ?></legend>
        <section>
            <select name='rights'>
                <option value='dozent'>
                    <?= dgettext('dozentenrechte', 'Dozentenrechte') ?>
                </option>
                <option value='tutor'<?= $right == 'tutor' ? ' selected="selected"' : '' ?>>
                    <?= dgettext('dozentenrechte', 'Tutorrechte') ?>
                </option>
            </select>
        </section>
        <section>
            <label id="rightsfor">
                <div><?= dgettext('dozentenrechte', 'f�r') ?></div>
                <?php if ($users) : ?>
                    <ul id="rights_added_users">
                    <?php foreach ($users as $u) : ?>
                        <li id="rights_added_user_<?= $u->id ?>">
                            <?= htmlReady($u->getFullname()) ?> (<?= $u->username ?>)
                            <input type="hidden" name="user[]" value="<?= $u->id ?>"/>
                        </li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>
                <?= MultiPersonSearch::get('add_dozentenrecht_' . $GLOBALS['user']->id)
                    ->setTitle(dgettext('dozentenrechte', 'Personen hinzuf�gen'))
                    ->setSearchObject($userSearch)
                    ->setDefaultSelectedUser(array_map(function ($u) { return $u->id; }, $users))
                    ->setExecuteURL($controller->url_for('show/multipersonsearch'))
                    ->setLinkText(dgettext('dozentenrechte', 'Person(en) hinzuf�gen'))
                    ->setJSFunctionOnSubmit('STUDIP.Dozentenrechte.addPersons')
                    ->render() ?>
            </label>
        </section>
        <section>
            <label>
                <?= dgettext('dozentenrechte', 'an der Einrichtung') ?>
                <?php if (count($institutes) > 0) : ?>
                <select name="inst">
                    <option value="">-- <?= dgettext('dozentenrechte', 'bitte ausw�hlen') ?> --</option>
                    <?php foreach ($institutes as $i) : ?>
                        <option value="<?= $i['Institut_id'] ?>"<?= $inst == $i['Institut_id'] ? ' selected="selected"' : '' ?>>
                            <?= ($GLOBALS['perm']->have_perm('admin') ?
                                ($i['Institut_id'] == $i['fakultaets_id'] ? '' : '&nbsp;&nbsp;') : '').
                                htmlReady($i['Name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <?php else : ?>
                <?= QuickSearch::get("inst", new StandardSearch('Institut_id'))->setInputStyle("width: 240px")->defaultValue($inst, $inst_parameter)->render(); ?>
                <?php endif ?>
            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend><?= dgettext('dozentenrechte', 'Von') ?></legend>
        <section>
            <label>
                <input type="radio" name="from_type" value="0"<?= $from ? '' : ' checked="checked"' ?>>
                <?= dgettext('dozentenrechte', 'Ab sofort') ?>
            </label>
            <label>
                <input type="radio" name="from_type" value="1"<?= Request::get('from_type') || $from ? ' checked="checked"' : ''; ?>>
                <?= dgettext('dozentenrechte', 'Ab Datum') ?>
                <input name="from" type="text" placeholder="<?= dgettext('dozentenrechte', 'Datum') ?>" class="datepicker" value="<?= $from ?>"/>
            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend><?= dgettext('dozentenrechte', 'Bis') ?></legend>
        <section>
            <label>
                <input type="radio" name="to_type" value="0"<?= $to ? '' : ' checked="checked"' ?>>
                <?= dgettext('dozentenrechte', 'Unbegrenzt') ?>
            </label>
            <label>
                <input type="radio" name="to_type" value="1"<?= Request::get('to_type') || $to ? ' checked="checked"' : '' ?>>
                <?= dgettext('dozentenrechte', 'Bis Datum') ?>
                <input name="to" type="text" placeholder="<?= dgettext('dozentenrechte', 'Datum') ?>" value="<?= $to ?>" class="datepicker" />
            </label>
        </section>
    </fieldset>
    <footer>
        <?= CSRFProtection::tokenTag() ?>
        <?= \Studip\Button::createAccept(dgettext('dozentenrechte', 'Antrag stellen'), 'save') ?>
    </footer>
</form>
