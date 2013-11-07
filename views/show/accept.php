<? if (count($rights)): ?>
    <form method="post">   
        <table class="default">
            <caption>
                <?= _('Gestellte Dozentenantr�ge') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= _('Von') ?></th>
                    <th><?= _('F�r') ?></th>
                    <th><?= _('Einrichtung') ?></th>
                    <th><?= _('Von') ?></th>
                    <th><?= _('Bis') ?></th>
                    <th><?= _('Antragsdatum') ?></th>
                    <th><?= _('Best�tigen') ?></th>
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
                        <td><input type="checkbox" name="verify[<?= $right->id ?>]" checked></td>
                    </tr>
                <? endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="0">
                        <?= \Studip\Button::create(_('Markierte best�tigen'), 'accept') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form> 
<? else: ?>
    <?= _('Es liegen keine Antr�ge vor') ?>
<? endif; ?>