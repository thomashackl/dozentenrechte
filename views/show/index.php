<? if ($rights): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= _('Gestellte Dozentenanträge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= _('Gestellt von') ?></th>
                    <th><?= _('Einrichtung') ?></th>
                    <th><?= _('Von') ?></th>
                    <th><?= _('Bis') ?></th>
                    <th><?= _('Antragsdatum') ?></th>
                    <th><?= _('Status') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($rights->orderBy('mkdate desc') as $right): ?>
                    <tr>
                        <td><?= htmlReady($right->owner->getFullname()) ?></td>
                        <td><?= htmlReady($right->institute->name) ?></td>
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
    <?= _('Es liegen keine Anträge für Sie vor') ?>
<? endif; ?>
