<? if (count($rights) > 0): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= dgettext('dozentenrechte', 'Gestellte Dozentenanträge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= dgettext('dozentenrechte', 'Gestellt von') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Einrichtung') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Typ') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Von') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Bis') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Antragsdatum') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Status') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($rights->orderBy('mkdate desc') as $right): ?>
                    <tr>
                        <td><?= htmlReady($right->owner->getFullname()) ?></td>
                        <td><?= htmlReady($right->institute->name) ?></td>
                        <td><?= htmlReady($right->rights) ?></td>
                        <td><?= $right->getBeginMessage() ?></td>
                        <td><?= $right->getEndMessage() ?></td>
                        <td><?= $right->getRequestDate() ?></td>
                        <td><?= $right->getStatusMessage() ?></td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </form> 
<? else: ?>
    <?= PageLayout::postInfo(dgettext('dozentenrechte', 'Es wurden keine Daten gefunden.')) ?>
<? endif; ?>
