<? if ($rights): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= _('Gestellte Dozentenanträge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= _('Antrag für') ?></th>
                    <th><?= _('Einrichtung') ?></th>
                    <th><?= _('Typ') ?></th>
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
                        <td><?= htmlReady($right->user->getFullname()) ?></td>
                        <td><?= htmlReady($right->institute->name) ?></td>
                        <td><?= htmlReady($right->rights) ?></td>
                        <td><?= $right->getBeginMessage() ?></td>
                        <td><?= $right->getEndMessage() ?></td>
                        <td><?= $right->getRequestDate() ?></td>
                        <td><?= $right->getStatusMessage() ?></td>
                        <td>
                            <?= $right->verify ? "" : \Studip\Button::create(_('Antrag zurückziehen'), 'reject', array('value' => $right->id)) ?>
                            <?= $GLOBALS['perm']->have_perm('root') ? \Studip\Button::create(_('Antrag löschen'), 'reject', array('value' => $right->id)) : "" ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </form> 
<? else: ?>
    <?= _('Von ihnen liegen keine Anträge vor') ?>
<? endif; ?>
