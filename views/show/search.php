<form method="post">
    <input name="search" value="<?= htmlReady(Request::get('search')) ?>">
    <?= \Studip\Button::create(dgettext('dozentenrechte', 'Suchen')) ?>
</form>
<? if ($rights): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= dgettext('dozentenrechte', 'Gestellte Dozentenanträge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= dgettext('dozentenrechte', 'Von') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Für') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Einrichtung') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Typ') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Von') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Bis') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Antragsdatum') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Status') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Aktion') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($rights->orderBy('mkdate desc') as $right): ?>
                    <tr>
                        <td>
                            <?= htmlReady($right->owner->getFullname()) ?> (<?= htmlReady($right->owner->username) ?>)
                        </td>
                        <td>
                            <?= htmlReady($right->user->getFullname()) ?> (<?= htmlReady($right->user->username) ?>)
                        </td>
                        <td><?= htmlReady($right->institute->name) ?></td>
                        <td><?= htmlReady($right->rights) ?></td>
                        <td><?= $right->getBeginMessage() ?></td>
                        <td><?= $right->getEndMessage() ?></td>
                        <td><?= $right->getRequestDate() ?></td>
                        <td><?= $right->getStatusMessage() ?></td>
                        <td>
                            <?= !$right->verify 
                                ? \Studip\Button::create(dgettext('dozentenrechte', 'Antrag löschen'), 'reject', ['value' => $right->id])
                                : $right->status < Dozentenrecht::FINISHED 
                                    ? \Studip\Button::create(dgettext('dozentenrechte', 'Beenden'), 'end', ['value' => $right->id])
                                    : "" ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </form> 
<? else: ?>
    <?= PageLayout::postInfo(dgettext('dozentenrechte', 'Es liegen keine Anträge vor')) ?>
<? endif; ?>
