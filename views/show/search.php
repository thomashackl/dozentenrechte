<form method="post">
    <input name="search" value="<?= htmlReady(Request::get('search')) ?>">
    <?= \Studip\Button::create(_('Suchen')) ?>
</form>
<? if ($rights): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= _('Gestellte Dozentenanträge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= _('Von') ?></th>
                    <th><?= _('Für') ?></th>
                    <th><?= _('Einrichtung') ?></th>
                    <th><?= _('Von') ?></th>
                    <th><?= _('Bis') ?></th>
                    <th><?= _('Antragsdatum') ?></th>
                    <th><?= _('Status') ?></th>
                    <th><?= _('Aktion') ?></th>
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
                        <td><?= $right->begin ? date('d.m.Y', $right->begin) : _('Unbegrenzt'); ?></td>
                        <td><?= $right->end == PHP_INT_MAX ? date('d.m.Y', $right->end) : _('Unbegrenzt'); ?></td>
                        <td><?= date('d.m.Y', $right->mkdate) ?></td>
                        <td><?= $right->verify ? _('Bestätigt') : _('Wartend') ?></td>
                        <td>
                            <?= $GLOBALS['perm']->have_perm('root') ? \Studip\Button::create(_('Antrag löschen'), 'reject', array('value' => $right->id)) : "" ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </form> 
<? else: ?>
    <?= _('Es liegen keine Anträge vor') ?>
<? endif; ?>
