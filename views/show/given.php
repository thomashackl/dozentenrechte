<? if ($rights): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= dgettext('dozentenrechte', 'Gestellte Dozentenanträge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= dgettext('dozentenrechte', 'Antrag für') ?></th>
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
                    <?php if ($right->user) : ?>
                    <tr>
                        <td><?= htmlReady($right->user->getFullname()) ?></td>
                        <td><?= htmlReady($right->institute->name) ?></td>
                        <td><?= htmlReady($right->rights) ?></td>
                        <td><?= $right->getBeginMessage() ?></td>
                        <td><?= $right->getEndMessage() ?></td>
                        <td><?= $right->getRequestDate() ?></td>
                        <td><?= $right->getStatusMessage() ?></td>
                        <td>
                            <?= $right->verify ? "" : \Studip\Button::create(dgettext('dozentenrechte', 'Antrag zurückziehen'), 'reject', array('value' => $right->id)) ?>
                            <?= $GLOBALS['perm']->have_perm('root') ? \Studip\Button::create(dgettext('dozentenrechte', 'Antrag löschen'), 'reject', array('value' => $right->id)) : "" ?>
                        </td>
                    </tr>
                    <?php endif ?>
                <? endforeach; ?>
            </tbody>
        </table>
    </form> 
<? else: ?>
    <?= dgettext('dozentenrechte', 'Von ihnen liegen keine Anträge vor') ?>
<? endif; ?>
