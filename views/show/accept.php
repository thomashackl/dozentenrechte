<? if (count($rights)): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= dgettext('dozentenrechte', 'Gestellte Dozentenantr�ge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= dgettext('dozentenrechte', 'Gestellt von') ?></th>
                    <th><?= dgettext('dozentenrechte', 'F�r') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Einrichtung') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Typ') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Von') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Bis') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Antragsdatum') ?></th>
                    <th><?= dgettext('dozentenrechte', 'Best�tigen') ?></th>
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
                        <td><input type="checkbox" name="verify[<?= $right->id ?>]" checked></td>
                    </tr>
                <? endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="0">
                        <?= \Studip\Button::create(dgettext('dozentenrechte', 'Markierte best�tigen'), 'accept') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form> 
<? else: ?>
    <?= dgettext('dozentenrechte', 'Es liegen keine Antr�ge vor') ?>
<? endif; ?>
